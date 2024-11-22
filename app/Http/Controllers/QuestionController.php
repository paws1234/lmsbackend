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
        $userId = auth()->user()->id;
        $teacher = \App\Models\Teacher::where('user_id', $userId)->first();
    
        if (!$teacher) {
            Log::error('Teacher not found.', ['user_id' => $userId]);
            return response()->json(['error' => 'Teacher not found.'], 404);
        }
    
        $teacherId = $teacher->id;
    
        $validator = Validator::make($request->all(), [
            'form_topic' => 'required|string',
            'subject_id' => 'required|integer|exists:subjects,id', // Add validation for subject_id
            'questions' => 'required|array',
            'questions.*.question_text' => 'required|string',
            'questions.*.points' => 'required|integer|min:1',
            'questions.*.answers' => 'required|array',
            'questions.*.answers.*.answer_text' => 'required|string',
            'questions.*.answers.*.is_correct' => 'required|boolean',
        ]);
    
        if ($validator->fails()) {
            Log::warning('Validation failed', ['errors' => $validator->errors()]);
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        DB::beginTransaction();
    
        try {
            foreach ($request->questions as $questionData) {
                $question = Question::create([
                    'question_text' => $questionData['question_text'],
                    'points' => $questionData['points'],
                    'teacher_id' => $teacherId,
                    'subject_id' => $request->subject_id // Ensure the subject_id is set on the question
                ]);
    
                foreach ($questionData['answers'] as $answerData) {
                    $answer = Answer::create([
                        'question_id' => $question->id,
                        'answer_text' => $answerData['answer_text'],
                        'is_correct' => $answerData['is_correct']
                    ]);
    
                    FormMap::create([
                        'topic_name' => $request->form_topic,
                        'question_id' => $question->id,
                        'answer_id' => $answer->id,
                    ]);
                }
            }
    
            DB::commit();
            return response()->json(['message' => 'Questions, Answers, and Form Map saved successfully!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to save questions, answers, and form map.'], 500);
        }
    }
    
    
    
    
    public function index()
    {
        $userId = auth()->user()->id;
        $teacher = \App\Models\Teacher::where('user_id', $userId)->first();
    
        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found.'], 404);
        }
    
        $teacherId = $teacher->id;
    
        $questions = Question::with(['answers', 'formMaps'])
                    ->where('teacher_id', $teacherId)
                    ->get();
    
        return response()->json($questions);
    }
    

    public function show($id)
    {
        $userId = auth()->user()->id;
        $teacher = \App\Models\Teacher::where('user_id', $userId)->first();
    
        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found.'], 404);
        }
    
        $teacherId = $teacher->id;
    
        try {
            $question = Question::with(['answers', 'formMaps'])
                        ->where('id', $id)
                        ->where('teacher_id', $teacherId)
                        ->firstOrFail();
    
            return response()->json($question);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Question not found or not authorized.'], 404);
        }
    }
    
    public function update(Request $request, $id)
    {
        $userId = auth()->user()->id;
        $teacher = \App\Models\Teacher::where('user_id', $userId)->first();
    
        if (!$teacher) {
            return response()->json(['error' => 'Teacher not found.'], 404);
        }
    
        $teacherId = $teacher->id;
    
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
            
            $question = Question::where('id', $id)
                ->where('teacher_id', $teacherId)
                ->firstOrFail();
    
            
            $question->update([
                'question_text' => $request->question_text,
                'points' => $request->points,
            ]);
    
            
            $question->answers()->delete();
    
            
            foreach ($request->answers as $answerData) {
                Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => $answerData['answer_text'],
                    'is_correct' => $answerData['is_correct'],
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
            return response()->json(['error' => 'Question not found or not authorized.'], 404);
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
