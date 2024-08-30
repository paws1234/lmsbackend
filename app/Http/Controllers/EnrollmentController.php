<?php
namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index()
    {
        $enrollments = Enrollment::with(['student', 'subject'])->get();
        return response()->json($enrollments);
    }

    public function store(Request $request)
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

        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
        ]);

        return response()->json($enrollment, 201);
    }

    public function show($id)
    {
        $enrollment = Enrollment::with(['student', 'subject'])->findOrFail($id);
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