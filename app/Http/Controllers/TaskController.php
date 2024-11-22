<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Todo;
use App\Models\Question;
use App\Models\FormMap;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    /**
     * Display a listing of the todos and questions for the current logged-in user.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get the current logged-in user
        $user = Auth::user();
        
        // Log the current logged-in user
        Log::info("Logged-in User:", ['user' => $user]);
    
        // Fetch the student record using the logged-in user's ID as user_id in the students table
        $student = Student::where('user_id', $user->id)->first();
    
        if (!$student) {
            Log::warning("No student record found for user:", ['user_id' => $user->id]);
            return response()->json(['message' => 'Student record not found'], 404);
        }
    
        // Log the retrieved student record
        Log::info("Student Record:", ['student' => $student]);
    
        // Use the student_id (from the student table) for all subsequent queries
        $studentId = $student->id;
    
        // Fetch the student's enrollments using the student_id
        $enrollments = Enrollment::where('student_id', $studentId)->get();
    
        // Log the student's enrollments
        Log::info("Student Enrollments:", ['enrollments' => $enrollments]);
    
        $todosBySubject = [];
        $questionsBySubject = [];
    
        // Iterate through each enrollment
        foreach ($enrollments as $enrollment) {
            $teacherId = $enrollment->teacher_id;
            $subjectId = $enrollment->subject_id;
    
            // Fetch the subject title using the subject_id
            $subject = Subject::find($subjectId);
            $subjectTitle = $subject ? $subject->title : 'Unknown Subject'; // Fallback if subject is not found
    
            // Log each enrollment details
            Log::info("Processing Enrollment:", [
                'enrollment' => $enrollment,
                'teacher_id' => $teacherId,
                'subject_id' => $subjectId,
                'subject_title' => $subjectTitle
            ]);
    
            // Fetch questions associated with the teacher and subject first
            $questionsData = Question::where('teacher_id', $teacherId)
                                     ->where('subject_id', $subjectId)
                                     ->get();
    
            // Fetch todos associated with the teacher and subject from the Todo table
            $todosData = Todo::where('teacher_id', $teacherId)
                             ->where('subject_id', $subjectId)
                             ->get();
    
            // Fetch form_map_id for the current questions and subject
            $formMapIds = FormMap::whereIn('question_id', $questionsData->pluck('id'))->pluck('id', 'question_id');
    
            // Group todos and questions by subject
            $todosBySubject[$subjectId][] = [
                'subject_id' => $subjectId,
                'subject_title' => $subjectTitle,
                'todos' => $todosData->map(function ($todo) {
                    return [
                        'id' => $todo->id,
                        'teacher_id' => $todo->teacher_id,
                        'subject_id' => $todo->subject_id,
                        'title' => $todo->title,
                        'description' => $todo->description,
                        'file' => $todo->file,
                        'created_at' => $todo->created_at,
                        'updated_at' => $todo->updated_at,
                    ];
                }),
            ];
    
            $questionsBySubject[$subjectId][] = [
                'subject_id' => $subjectId,
                'subject_title' => $subjectTitle,
                'questions' => $questionsData->map(function ($question) use ($formMapIds) {
                    return [
                        'question_id' => $question->id,
                        'question_text' => $question->question_text,
                        'points' => $question->points,
                        'answers' => $question->answers,
                        'form_map_id' => $formMapIds[$question->id] ?? null, // Include form_map_id
                    ];
                }),
            ];
        }
    
        // Log the final tasks data
        Log::info("Final Tasks Data:", ['todos_by_subject' => $todosBySubject, 'questions_by_subject' => $questionsBySubject]);
    
        // Return the todos and questions separately
        return response()->json([
            'todos_by_subject' => $todosBySubject,
            'questions_by_subject' => $questionsBySubject,
        ]);
    }
    
    
    

    
    
    /**
     * Store a newly created todo or question in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validation rules for the data being passed
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,user_id',
            'subject_id' => 'required|exists:subjects,id',
            'type' => 'required|string', // 'todo' or 'question'
            'title' => 'required|string',
            'description' => 'nullable|string',
            'file' => 'nullable|file',
            'form_map_id' => 'nullable|exists:form_map,id', // For questions
        ]);

        if ($validated['type'] == 'todo') {
            // Create a new todo entry
            $todo = Todo::create($validated);
            return response()->json($todo, 201);
        } elseif ($validated['type'] == 'question') {
            // Create a new question entry
            $question = Question::create($validated);
            return response()->json($question, 201);
        }

        return response()->json(['error' => 'Invalid type'], 400);
    }
}
