<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// -------------------- PUBLIC ROUTES --------------------
Route::post('/login', [AuthController::class, 'login'])->name('login');


// -------------------- PROTECTED ROUTES --------------------
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
 

    // Students
    Route::prefix('students')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('students.index');
        Route::get('/export', [StudentController::class, 'export'])->name('students.export');
        Route::get('/{id}', [StudentController::class, 'show'])->name('students.show');
        Route::post('/', [StudentController::class, 'store'])->name('students.store');
        Route::put('/{id}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::post('/{id}/complete-course', [StudentController::class, 'completeCourse']);
    });

    // Branches
    Route::prefix('branches')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('branches.index');
        Route::get('/{id}', [BranchController::class, 'show'])->name('branches.show');
        Route::post('/', [BranchController::class, 'store'])->name('branches.store');
        Route::put('/{id}', [BranchController::class, 'update'])->name('branches.update');
        Route::delete('/{id}', [BranchController::class, 'destroy'])->name('branches.destroy');
    });

    // Courses
    Route::prefix('courses')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('courses.index');
        Route::get('/{id}', [CourseController::class, 'show'])->name('courses.show');
        Route::post('/', [CourseController::class, 'store'])->name('courses.store');
        Route::put('/{id}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::get('{id}/students', [CourseController::class, 'students']);
    });

    // Certificates
    // Route::prefix('certificates')->group(function () {
    //     Route::post('/issue', [CertificateController::class, 'issue']); // move logic from StudentController here
    //     // Route::get('/{certificate}/pdf', [CertificateController::class, 'pdf']);
    // });
});
