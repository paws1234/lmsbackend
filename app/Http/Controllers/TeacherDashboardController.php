<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Subject;
use App\Models\EventHandler;
use App\Models\Teacher;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherDashboardController extends Controller
{
   
    public function index()
    {
        try {
            
            $user = Auth::user();
        
            
            $teacher = Teacher::where('user_id', $user->id)->first();
        
            
            if (!$teacher) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Teacher not found for the current user',
                ], 404);
            }
        
            
            $teacherId = $teacher->id;
        
            
            $enrolledStudentsCount = Enrollment::where('teacher_id', $teacherId)
                ->distinct('teacher_id')
                ->count('teacher_id');
        
            
            $subjectCount = Subject::count();
        
            
            $eventCount = EventHandler::all();
        
            
            
            $distinctQuestionCount = DB::table('questions')
            ->join('form_map', 'form_map.question_id', '=', 'questions.id') 
            ->where('questions.teacher_id', $teacherId) 
            ->select(DB::raw('DISTINCT DATE(questions.created_at) as created_date')) 
            ->count(DB::raw('DISTINCT DATE(questions.created_at)')); 
        
            
            $todoCount = Todo::where('teacher_id', $teacherId)->count(); 
        
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'enrolled_students_count' => $enrolledStudentsCount,
                    'subject_count' => $subjectCount,
                    'event_count' => $eventCount,  
                    'distinct_question_count' => $distinctQuestionCount,  
                    'todo_count' => $todoCount,  
                ]
            ], 200);
        } catch (\Exception $e) {
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    
    


}
