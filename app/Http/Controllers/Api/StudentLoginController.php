<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentLoginController extends Controller
{
    /**
     * Student login
     */
    public function login(Request $request)
    {
        // Validate inputs
        $request->validate([
            'code'     => 'required|string',
            'password' => 'required|string',
        ]);

        // Find student by code
        $student = Student::where('code', $request->code)->first();

        // Check if student exists and password matches
        if (!$student || !Hash::check($request->password, $student->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid student code or password',
            ], 401);
        }

        // Return student data (without token for now)
        return response()->json([
            'status'  => true,
            'message' => 'Login successful',
            'data'    => [
                'id'      => $student->id,
                'code'    => $student->code,
                'name'    => $student->name,
                'email'   => $student->email,
                'phone'   => $student->phone,
                'status'  => $student->status,
                'course'  => $student->course?->name,
                'branch'  => $student->branch?->name,
            ],
        ]);
    }
}
