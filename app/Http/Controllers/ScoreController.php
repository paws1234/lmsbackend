<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Score;
use App\Models\FormMap;
use App\Models\Submission;
use App\Models\Student;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;


class ScoreController extends Controller
{

    public function store(Request $request)
    {
        $score = Score::create([
            'student_id' => $request->student_id,
            'task_id' => $request->task_id,
            'score' => Score::calculateScore($request->student_id, $request->task_id),
        ]);

        return response()->json($score, 201);
    }



    public function index()
    {
        $userId = Auth::id();
    
        // Retrieve the student associated with the current user.
        $student = Student::where('user_id', $userId)->first();
        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }
    
        // Retrieve all submissions for the student.
        $submissions = Submission::where('user_id', $userId)->get();
    
        if ($submissions->isEmpty()) {
            return response()->json(['message' => 'No submissions found for this student'], 404);
        }
    
        // Prepare an array to hold the score data.
        $scoreData = [];
    
        foreach ($submissions as $submission) {
            $formMapId = $submission->form_map_id;
    
            // If this form_map_id doesn't exist in the scoreData array, initialize it.
            if (!isset($scoreData[$formMapId])) {
                $formMap = FormMap::find($formMapId);
                if ($formMap) {
                    $scoreData[$formMapId] = [
                        'form_id' => $formMap->form_id,
                        'submissions' => [],
                        'correct_answers_count' => 0,
                        'total_submissions' => 0,
                    ];
                }
            }
    
            // Process each submission and accumulate data for scoreData.
            if (isset($scoreData[$formMapId])) {
                // Add this submission to the submissions array of the respective form_map_id.
                $scoreData[$formMapId]['submissions'][] = $submission;
    
                // Retrieve the question and the answer related to this submission.
                $question = Question::find($submission->question_id);
                $answer = Answer::find($submission->answer_id);
    
                // If question and answer exist, associate them with the submission.
                if ($question && $answer) {
                    $scoreData[$formMapId]['submissions'][] = [
                        'question' => [
                            'id' => $question->id,
                            'text' => $question->question_text,
                        ],
                        'answer' => [
                            'id' => $answer->id,
                            'value' => $answer->answer_text,
                        ],
                        'is_correct' => $submission->is_correct,
                        'created_at' => $submission->created_at,
                        'updated_at' => $submission->updated_at,
                    ];
                }
    
                // Track the correct answers and total submissions.
                if ($submission->is_correct) {
                    $scoreData[$formMapId]['correct_answers_count']++;
                }
                $scoreData[$formMapId]['total_submissions']++;
            }
        }
    
        // Return the score data in the required format.
        return response()->json([
            'score_data' => $scoreData,
        ]);
    }
    
}
