<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    // GET /api/courses
    public function index()
    {
        $courses = Course::with(['branch', 'students'])->get();

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
        ]);

        $course->update($validated);

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
        $course = Course::with('students')->find($id);

        if (!$course) {
            return response()->json([
                'status' => false,
                'message' => 'Course not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $course->students
        ], 200);
    }
}
