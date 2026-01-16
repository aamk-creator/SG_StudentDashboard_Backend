<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\StudentResource;

class StudentController extends Controller
{
    /**
     * List all students
     */
    public function index()
    {
        $students = Student::with(['course', 'branch', 'user'])->get();

        return response()->json([
            'status' => true,
            'data' => StudentResource::collection($students),
        ], 200);
    }

    /**
     * Show single student
     */
    public function show($id)
    {
        $student = Student::with(['course', 'branch', 'user'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => new StudentResource($student),
        ], 200);
    }

    /**
     * Add new student
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'      => 'required|string|max:50|unique:students,code',
            'name'      => 'required|string|max:255',
            'status'    => 'nullable|string|in:active,inactive',
            'user_id'   => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        $student = Student::create($validated);
        $student->load(['course', 'branch', 'user']);

        return response()->json([
            'status' => true,
            'data' => new StudentResource($student),
        ], 201);
    }

    /**
     * Update student
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'code')->ignore($student->id),
            ],
            'name'      => 'sometimes|required|string|max:255',
            'status'    => 'nullable|string|in:active,inactive',
            'user_id'   => 'sometimes|required|exists:users,id',
            'course_id' => 'sometimes|required|exists:courses,id',
            'branch_id' => 'sometimes|required|exists:branches,id',
        ]);

        $student->update($validated);
        $student->load(['course', 'branch', 'user']);

        return response()->json([
            'status' => true,
            'data' => new StudentResource($student),
        ], 200);
    }

    /**
     * Delete student
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        return response()->json([
            'status' => true,
            'message' => 'Student deleted successfully',
        ], 200);
    }
}
