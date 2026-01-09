<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    
    public function index()
    {
        $students = Student::with('class')->get();
        return response()->json(['status' => true, 'data' => $students], 200);
    }

    
    public function show($id)
    {
        $student = Student::with('class')->find($id);

        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Student not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $student], 200);
    }

    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:students,email',
            'phone'      => 'nullable|string|max:20',
            'class_id'   => 'required|exists:class_rooms,id',
            'gender'     => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => 'nullable|date',
            'address'    => 'nullable|string',
        ]);

        $student = Student::create($validated);

        return response()->json(['status' => true, 'data' => $student], 201);
    }

    // PUT /api/students/{id}
    public function update(Request $request, $id)
    {
        $student = Student::find($id);
        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Student not found'], 404);
        }

        $validated = $request->validate([
            'name'       => 'sometimes|required|string|max:255',
            'email'      => ['sometimes','required','email', Rule::unique('students')->ignore($student->id)],
            'phone'      => 'nullable|string|max:20',
            'class_id'   => 'sometimes|required|exists:class_rooms,id',
            'gender'     => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => 'nullable|date',
            'address'    => 'nullable|string',
        ]);

        $student->update($validated);

        return response()->json(['status' => true, 'data' => $student], 200);
    }

    // DELETE /api/students/{id}
    public function destroy($id)
    {
        $student = Student::find($id);
        if (!$student) {
            return response()->json(['status' => false, 'message' => 'Student not found'], 404);
        }

        $student->delete();

        return response()->json(['status' => true, 'message' => 'Student deleted'], 200);
    }
}
