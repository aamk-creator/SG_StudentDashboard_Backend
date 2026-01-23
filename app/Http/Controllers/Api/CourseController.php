<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CourseController extends Controller
{
    // GET /api/courses
    public function index()
    {
        $courses = Course::with(['branch', 'students'])->get();

        // Format course dates for UI
        $courses = $courses->map(function ($course) {
            $course->course_start_at = $course->course_start_at
                ? Carbon::parse($course->course_start_at)->format('Y-m-d')
                : null;
            $course->course_end_at = $course->course_end_at
                ? Carbon::parse($course->course_end_at)->format('Y-m-d')
                : null;
            return $course;
        });

        return response()->json([
            'status' => true,
            'data' => $courses
        ], 200);
    }

    // GET /api/courses/{id}
    public function show($id)
    {
        $course = Course::with(['branch', 'students'])->find($id);

        if (!$course) {
            return response()->json([
                'status' => false,
                'message' => 'Course not found'
            ], 404);
        }

        // Format course dates
        $course->course_start_at = $course->course_start_at
            ? Carbon::parse($course->course_start_at)->format('Y-m-d')
            : null;
        $course->course_end_at = $course->course_end_at
            ? Carbon::parse($course->course_end_at)->format('Y-m-d')
            : null;

        return response()->json([
            'status' => true,
            'data' => $course
        ], 200);
    }

    // POST /api/courses
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id',
            'course_start_at' => 'nullable|date',
            'course_end_at' => 'nullable|date',
        ]);

        $validated['user_id'] = Auth::id() ?? 1;

        if (empty($validated['branch_id'])) {
            $defaultBranch = Branch::first();
            if ($defaultBranch) {
                $validated['branch_id'] = $defaultBranch->id;
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No branch available. Please add a branch first.'
                ], 400);
            }
        }

        $course = Course::create($validated);

        // Format dates for UI
        $course->course_start_at = $course->course_start_at
            ? Carbon::parse($course->course_start_at)->format('Y-m-d')
            : null;
        $course->course_end_at = $course->course_end_at
            ? Carbon::parse($course->course_end_at)->format('Y-m-d')
            : null;

        return response()->json([
            'status' => true,
            'data' => $course
        ], 201);
    }

    // PUT /api/courses/{id}
    public function update(Request $request, $id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json([
                'status' => false,
                'message' => 'Course not found'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'title' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'branch_id' => 'sometimes|nullable|exists:branches,id',
            'course_start_at' => 'sometimes|nullable|date',
            'course_end_at' => 'sometimes|nullable|date',
        ]);

        $course->update($validated);

        // Format dates
        $course->course_start_at = $course->course_start_at
            ? Carbon::parse($course->course_start_at)->format('Y-m-d')
            : null;
        $course->course_end_at = $course->course_end_at
            ? Carbon::parse($course->course_end_at)->format('Y-m-d')
            : null;

        return response()->json([
            'status' => true,
            'data' => $course
        ], 200);
    }

    // DELETE /api/courses/{id}
    public function destroy($id)
    {
        $course = Course::find($id);
        if (!$course) {
            return response()->json([
                'status' => false,
                'message' => 'Course not found'
            ], 404);
        }

        $course->delete();

        return response()->json([
            'status' => true,
            'message' => 'Course deleted successfully'
        ], 200);
    }

    // GET /api/courses/{id}/students
    public function students($id)
    {
        $course = Course::with('students.user')->find($id);

        if (!$course) {
            return response()->json([
                'status' => false,
                'message' => 'Course not found'
            ], 404);
        }

        $students = $course->students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->user->email ?? null,
                'course_start_at' => $student->course_start_at
                    ? Carbon::parse($student->course_start_at)->format('Y-m-d')
                    : null,
                'course_end_at' => $student->course_end_at
                    ? Carbon::parse($student->course_end_at)->format('Y-m-d')
                    : null,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $students
        ], 200);
    }
}
