<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Subject;
use Illuminate\Http\Request;

class StudentEnrollmentController extends Controller
{
    
    public function showEnrolledSubjects($studentId)
    {
        $enrollments = Enrollment::where('student_id', $studentId)
            ->with('subject')  
            ->get();

        
        if ($enrollments->isEmpty()) {
            return response()->json(['message' => 'No enrollments found for this student.'], 404);
        }

        
        $subjects = $enrollments->map(function ($enrollment) {
            return [
                'subject_title' => $enrollment->subject->title,
                'subject_description' => $enrollment->subject->description,
                'subject_schedule' => $enrollment->subject->schedule, 
            ];
        });
        

        return response()->json($subjects);
    }

    
    public function countEnrollments($studentId)
    {
        $count = Enrollment::where('student_id', $studentId)->count();

        return response()->json(['enrollment_count' => $count]);
    }
}
