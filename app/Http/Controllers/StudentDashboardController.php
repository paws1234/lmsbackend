<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Todo;
use App\Models\FormMap;
use App\Models\Question;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Schedule;
use App\Models\EventHandler;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $studentId = Student::where('user_id', $userId)->value('id');
        if (!$studentId) {
            return response()->json(['error' => 'Student not found'], 404);
        }
        $subjectCount = Enrollment::where('student_id', $studentId)
            ->distinct('subject_id')
            ->count();
        $teacherIds = Enrollment::where('student_id', $studentId)
            ->pluck('teacher_id');
        $taskGivenCount = FormMap::whereIn('question_id', Question::whereIn('teacher_id', $teacherIds)->pluck('id'))
            ->distinct('question_id')
            ->count();
        $todoGivenCount = Todo::whereIn('teacher_id', $teacherIds)
            ->count();
        $totalTaskCount = $taskGivenCount + $todoGivenCount;
        $scheduleCount = Subject::whereIn('subjects.id', Enrollment::where('student_id', $studentId)->pluck('subject_id'))
            ->whereNotNull('subjects.schedule')
            ->where('subjects.schedule', '!=', '')
            ->join('enrollments', 'subjects.id', '=', 'enrollments.subject_id')
            ->join('teachers', 'enrollments.teacher_id', '=', 'teachers.id')
            ->select('subjects.*', 'teachers.*')
            ->get();
        $events = EventHandler::all();
        return response()->json([
            'subjectCount' => $subjectCount,
            'taskGivenCount' => $totalTaskCount,
            'scheduleCount' => $scheduleCount,
            'events' => $events,
        ]);
    }
}
