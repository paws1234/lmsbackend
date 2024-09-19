<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\EventHandlerController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\QuestionController;


Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('user', [AuthController::class, 'user']);
Route::middleware(['auth:sanctum', 'role:admin'])->get('/admin/dashboard', function () {
    return response()->json(['message' => 'Welcome to the admin dashboard']);
});
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::apiResource('students', StudentController::class);
    Route::apiResource('teachers', TeacherController::class);
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('schedules', ScheduleController::class);
    Route::apiResource('event-handlers', EventHandlerController::class);

});
Route::middleware(['auth:sanctum', 'role:teacher'])->prefix('teacher')->group(function () {
    Route::apiResource('subjects', SubjectController::class);
    Route::get('getStudents', [EnrollmentController::class, 'getStudents']);
    Route::get('getSubjects', [EnrollmentController::class, 'getSubjects']);
    Route::apiResource('enrollments', EnrollmentController::class);
    Route::apiResource('todos', TodoController::class);
    Route::apiResource('questions', QuestionController::class);

});
Route::middleware(['auth:sanctum', 'role:teacher'])->get('/teacher/dashboard', function () {
    return response()->json(['message' => 'Welcome to the teacher dashboard']);
});

Route::middleware(['auth:sanctum', 'role:student'])->get('/student/dashboard', function () {

});