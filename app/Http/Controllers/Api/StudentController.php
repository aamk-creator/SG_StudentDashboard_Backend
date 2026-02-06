<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Course;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\StudentResource;
use Illuminate\Support\Facades\Auth;


class StudentController extends Controller
{
    /**
     * List all students
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        /** @var \Illuminate\Database\Eloquent\Collection $students */
        $students = Student::with(['course', 'branch', 'user'])->get();

        return response()->json([
            'status' => true,
            'data' => StudentResource::collection($students),
        ]);
    }

    /**
     * Add new student
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        /** @var array $validated */
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

        /** @var string $plainPassword */
        $plainPassword = $validated['password'];
        $validated['password'] = Hash::make($plainPassword);

        /** @var \App\Models\Course $course */
        $course = Course::findOrFail($validated['course_id']);
        $validated['course_start_at'] = $course->course_start_at;
        $validated['course_end_at']   = $course->course_end_at;

        /** @var \App\Models\Student $student */
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
     * Update student
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        /** @var \App\Models\Student $student */
        $student = Student::findOrFail($id);

        /** @var array $validated */
        $validated = $request->validate([
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('students', 'code')->ignore($student->id),
            ],
            'name'  => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:191',
                Rule::unique('students', 'email')->ignore($student->id),
            ],
            'phone' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'phone')->ignore($student->id),
            ],
            'password'  => 'nullable|min:6',
            'status'    => 'nullable|in:active,inactive',
            'user_id'   => 'sometimes|required|exists:users,id',
            'course_id' => 'sometimes|required|exists:courses,id',
            'branch_id' => 'sometimes|required|exists:branches,id',
        ]);

        if (!empty($validated['password'])) {
            /** @var string $plainPassword */
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
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        Student::findOrFail($id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Student deleted successfully',
        ]);
    }

    /**
     * IMPORT STUDENTS (CSV)
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $rows = array_map('str_getcsv', file($file));
        $header = array_shift($rows);

        DB::beginTransaction();

        try {
            $imported = 0;
            $skipped = [];

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because header is row 1

                // if (count($row) < 8) {
                //     $skipped[] = "Row {$rowNumber}: Missing fields";
                //     continue;
                // }

                [
                    $code,
                    $phone,
                    $email,
                    $name,
                    $course_name,
                    $branch_name,
                    $status,
                ] = $row;

                //  var_dump($row);
                // if (!$code || !$name || !$email || !$course_name || !$branch_name) {
                //     $skipped[] = "Row {$rowNumber}: Required fields missing";
                //     // continue;
                // }

                // if (Student::where('email', $email)->exists()) {
                //     $skipped[] = "Row {$rowNumber}: Email already exists";
                //     // continue;
                // }

                // /** @var \App\Models\Course|null $course */
                // var_dump($course_name);

                $course = Course::where('name', $course_name)->first();
                // if (!$course) {
                //     $skipped[] = "Row {$rowNumber}: Course '{$course_name}' not found";
                //     // continue;
                // }
                // var_dump($course);

                // /** @var \App\Models\Branch|null $branch */
                $branch = Branch::where('name', $branch_name)->first();

                // var_dump($branch);
            if (!$branch) {
                $skipped[] = "Row {$rowNumber}: Branch '{$branch_name}' not found";
                continue;
            }

                /** @var \App\Models\Student $student */
                $student = Student::create([
                    'code'      => $code,
                    'name'      => $name,
                    'email'     => $email,
                    'phone'     => $phone,
                    'status'    => $status,
                    'user_id'   => Auth::user()->id,
                    'course_id' => $course->id,
                    'branch_id' => $branch->id,
                ]);

                $imported++;
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => "Import completed: {$imported} imported, " . count($skipped) . " skipped.",
                'skipped_rows' => $skipped,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Import failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
