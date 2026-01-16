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
|
| Here is where you can register API routes for your application.
| Routes are loaded by the RouteServiceProvider within a group 
| which is assigned the "api" middleware group. 
|
*/

// -------------------- AUTH --------------------


Route::post('/login', [AuthController::class, 'login'])->name('login');

// Route::post('/register', [AuthController::class, 'createUser'])->name('register');




Route::middleware('auth:sanctum')->prefix()->group(function () {
Route::get('/students', [StudentController::class, 'index'])->name('students.index');

    
    Route::prefix('students')->group(function () {
        Route::get('/{id}', [StudentController::class, 'show'])->name('students.show');
        Route::post('/', [StudentController::class, 'store'])->name('students.store');
        Route::put('/{id}', [StudentController::class, 'update'])->name('students.update');
        Route::delete('/{id}', [StudentController::class, 'destroy'])->name('students.destroy');
    });

 });




    
