<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\StudentResource;

class StudentController extends Controller
{
    /**
     * List all students
     */
    public function index()
    {
        $students = Student::with(['course', 'branch', 'user'])->get();

        // Return with plain_password included
        return response()->json([
            'status' => true,
            'data' => StudentResource::collection($students),
        ]);
    }

    /**
     * Add new student (ADMIN)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'      => 'required|string|max:50|unique:students,code',
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:191|unique:students,email',
            'phone'     => 'required|string|max:20|unique:students,phone',
            'password'  => 'required|min:6',
            'status'    => 'nullable|in:active,inactive',
            'user_id'   => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $plainPassword = $validated['password'];
        $validated['password'] = Hash::make($plainPassword);

        // Auto set course dates
        $course = Course::findOrFail($validated['course_id']);
        $validated['course_start_at'] = $course->course_start_at;
        $validated['course_end_at']   = $course->course_end_at;

        // Save student including plain_password
        $student = Student::create([
            ...$validated,
            'plain_password' => $plainPassword,
        ]);

        $student->load(['course', 'branch', 'user']);

        return response()->json([
            'status' => true,
            'data' => new StudentResource($student),
        ], 201);
    }

    /**
     * Update student (ADMIN)
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'code' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('students', 'code')->ignore($student->id),
            ],
            'name'  => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes', 'required', 'email', 'max:191',
                Rule::unique('students', 'email')->ignore($student->id),
            ],
            'phone' => [
                'sometimes', 'required', 'string', 'max:20',
                Rule::unique('students', 'phone')->ignore($student->id),
            ],
            'password'  => 'nullable|min:6',
            'status'    => 'nullable|in:active,inactive',
            'user_id'   => 'sometimes|required|exists:users,id',
            'course_id' => 'sometimes|required|exists:courses,id',
            'branch_id' => 'sometimes|required|exists:branches,id',
        ]);

        // Hash password and save plain password if changed
        if (!empty($validated['password'])) {
            $plainPassword = $validated['password'];
            $validated['password'] = Hash::make($plainPassword);
            $validated['plain_password'] = $plainPassword;
        }

        $student->update($validated);
        $student->load(['course', 'branch', 'user']);

        return response()->json([
            'status' => true,
            'data' => new StudentResource($student),
        ]);
    }

    /**
     * Delete student
     */
    public function destroy($id)
    {
        Student::findOrFail($id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Student deleted successfully',
        ]);
    }
}
