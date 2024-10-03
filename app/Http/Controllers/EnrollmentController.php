<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    public function index()
{
    // Get the current logged-in user's user_id
    $currentUserId = Auth::id();

    // Fetch the teacher's ID associated with the current user
    $teacher = Teacher::where('user_id', $currentUserId)->first();

    if (!$teacher) {
        return response()->json(['error' => 'Teacher not found for the current user.'], 404);
    }

    // Get enrollments associated with the logged-in teacher
    $enrollments = Enrollment::with(['student', 'subject'])
        ->where('teacher_id', $teacher->user_id) // Use teacher's user_id as teacher_id in enrollments
        ->get();

        return response()->json($enrollments);
}


public function store(Request $request)
{
    $request->validate([
        'student_name' => 'required|string|max:255',
        'subject_name' => 'required|string|max:255',
    ]);

    // Find the student and subject based on provided names
    $student = Student::where('name', $request->student_name)->first();
    $subject = Subject::where('title', $request->subject_name)->first();

    if (!$student || !$subject) {
        return response()->json(['error' => 'Student or Subject not found'], 404);
    }

    // Get the current teacher's user_id
    $teacherId = Auth::id(); // Assuming this is the correct user ID for the teacher

    // Ensure the teacher exists in the teachers table
    $teacher = Teacher::where('user_id', $teacherId)->first();
    
    if (!$teacher) {
        return response()->json(['error' => 'Teacher not found for the current user.'], 404);
    }

    // Create the enrollment
    $enrollment = Enrollment::create([
        'student_id' => $student->id,
        'subject_id' => $subject->id,
        'teacher_id' => $teacher->user_id, // Use the user_id from the teachers table
    ]);

    return response()->json($enrollment, 201);
}



    public function show($id)
    {
        $enrollment = Enrollment::with(['student', 'subject', 'teacher'])->findOrFail($id);
        return response()->json($enrollment);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'student_name' => 'required|string|max:255',
            'subject_name' => 'required|string|max:255',
        ]);

        $student = Student::where('name', $request->student_name)->first();
        $subject = Subject::where('title', $request->subject_name)->first();

        if (!$student || !$subject) {
            return response()->json(['error' => 'Student or Subject not found'], 404);
        }

        $enrollment = Enrollment::findOrFail($id);
        $enrollment->update([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
        ]);

        return response()->json($enrollment);
    }

    public function destroy($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        $enrollment->delete();

        return response()->json(null, 204);
    }

    public function getStudents()
    {
        $students = Student::all(['id', 'name']);
        return response()->json($students);
    }

    public function getSubjects()
    {
        $subjects = Subject::all(['id', 'title']);
        return response()->json($subjects);
    }
}
