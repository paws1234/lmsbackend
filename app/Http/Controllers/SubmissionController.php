<?php
namespace App\Http\Controllers;

use App\Models\FormMap;
use App\Models\Submission;
use App\Models\Answer;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function submit(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'form_map_id' => 'required|exists:form_map,id',
            'submissions' => 'required|array',
            'submissions.*.question_id' => 'required|exists:questions,id',
            'submissions.*.answer_id' => 'required|exists:answers,id',
        ]);

        // Loop through submissions and save them
        $submissionData = [];
        foreach ($request->submissions as $submission) {
            $isCorrect = Submission::checkAnswer($submission['question_id'], $submission['answer_id']);

            // Store the submission
            $submissionData[] = [
                'form_map_id' => $request->form_map_id,
                'question_id' => $submission['question_id'],
                'answer_id' => $submission['answer_id'],
                'user_id' => auth()->id(),
                'is_correct' => $isCorrect,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all submissions at once
        Submission::insert($submissionData);

        return response()->json(['success' => true, 'message' => 'Answers submitted successfully']);
    }
}