<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Answer;
use App\Models\FormMap;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class QuestionController extends Controller
{
   

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'questions' => 'required|array',
            'questions.*.question_text' => 'required|string',
            'questions.*.points' => 'required|integer|min:1',
            'questions.*.answers' => 'required|array',
            'questions.*.answers.*.answer_text' => 'required|string',
            'questions.*.answers.*.is_correct' => 'required|boolean',
            'form_topic' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            Log::error('Validation failed for store request', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        DB::beginTransaction();
    
        try {
            // Create a list of question IDs and answer IDs
            $questionIds = [];
            $answerIds = [];
    
            foreach ($request->questions as $questionData) {
                // Create a new question
                $question = Question::create([
                    'question_text' => $questionData['question_text'],
                    'points' => $questionData['points']
                ]);
    
                $questionIds[] = $question->id;
    
                Log::info('Created question', ['question_id' => $question->id, 'question_data' => $questionData]);
    
                foreach ($questionData['answers'] as $answerData) {
                    // Create associated answers
                    $answer = Answer::create([
                        'question_id' => $question->id,
                        'answer_text' => $answerData['answer_text'],
                        'is_correct' => $answerData['is_correct']
                    ]);
    
                    $answerIds[] = $answer->id;
    
                    Log::info('Created answer', ['answer_id' => $answer->id, 'answer_data' => $answerData]);
                }
            }
    
            // Create form map entries for each question and answer ID
            foreach ($questionIds as $questionId) {
                foreach ($answerIds as $answerId) {
                    FormMap::create([
                        'topic_name' => $request->form_topic,
                        'question_id' => $questionId,
                        'answer_id' => $answerId
                    ]);
                }
            }
    
            Log::info('Form map entries created', ['form_topic' => $request->form_topic, 'question_ids' => $questionIds, 'answer_ids' => $answerIds]);
    
            DB::commit();
    
            return response()->json(['message' => 'Questions and Form Map saved successfully!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save questions and form map', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to save questions and form map.'], 500);
        }
    }
    
    
    public function index()
    {
        $questions = Question::with(['answers', 'formMaps'])->get();
        return response()->json($questions);
    }

    public function show($id)
    {
        try {
            $question = Question::with(['answers', 'formMaps'])->findOrFail($id);
            return response()->json($question);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Question not found.'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'question_text' => 'required|string',
            'points' => 'required|integer|min:1',
            'answers' => 'required|array',
            'answers.*.answer_text' => 'required|string',
            'answers.*.is_correct' => 'required|boolean',
            'form_topic' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        try {
            $question = Question::findOrFail($id);
            $question->update([
                'question_text' => $request->question_text,
                'points' => $request->points
            ]);
            $question->answers()->delete();
            foreach ($request->answers as $answerData) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => $answerData['answer_text'],
                    'is_correct' => $answerData['is_correct']
                ]);
            }

            FormMap::updateOrCreate(
                ['question_id' => $id],
                ['topic_name' => $request->form_topic]
            );

            DB::commit();

            return response()->json(['message' => 'Question and Form Map updated successfully!'], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Question not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update question.'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $question = Question::findOrFail($id);
            $question->answers()->delete();
            FormMap::where('question_id', $id)->delete();
            $question->delete();

            return response()->json(['message' => 'Question and associated data deleted successfully!'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Question not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete question.'], 500);
        }
    }
}
