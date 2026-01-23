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
        $validated['course_start_at'] = now(); // 🔥 start tracking here

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
     * Mark course as completed (ADMIN)
     */
    public function completeCourse($id)
    {
        $student = Student::findOrFail($id);

        if ($student->course_end_at) {
            return response()->json([
                'status' => false,
                'message' => 'Course already completed',
            ], 400);
        }

        $student->course_end_at = now();
        $student->save();

        return response()->json([
            'status' => true,
            'message' => 'Course marked as completed',
            'completed_at' => $student->course_end_at,
        ]);
    }

    /**
     * Issue certificate (ADMIN)
     */
    // public function issueCertificate(Request $request)
    // {
    //     $request->validate([
    //         'student_id' => 'required|exists:students,id',
    //     ]);

    //     $student = Student::with('course')->findOrFail($request->student_id);

    //     if (!$student->course_end_at) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Student has not completed the course',
    //         ], 400);
    //     }

    //     if ($student->certificate_issued_at) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Certificate already issued',
    //         ], 400);
    //     }

    //     $student->certificate_issued_at = now();
    //     $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.template', [
    //         'student' => $student,
    //         'course' => $student->course,
    //         'issued_at' => $student->certificate_issued_at,
    //     ]);

    //     $fileName = 'student_' . $student->id . '.pdf';
    //     $pdfPath = storage_path('app/public/certificates/' . $fileName);

    //     if (!file_exists(storage_path('app/public/certificates'))) {
    //         mkdir(storage_path('app/public/certificates'), 0777, true);
    //     }
    //     $pdf->save($pdfPath);

    //     $student->certificate_path = 'certificates/' . $fileName;
    //     $student->save();

       
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Certificate issued successfully',
    //         'issued_at' => $student->certificate_issued_at,
    //         'certificate_path' => asset('storage/' . $student->certificate_path),
    //     ]);
    // }

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
