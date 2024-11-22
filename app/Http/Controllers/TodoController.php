<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TodoController extends Controller
{
    public function index()
    {
        $teacherId = Teacher::where('user_id', Auth::id())->firstOrFail()->id;

        $todos = Todo::select('todos.*', 'subjects.title as subject_name')
            ->leftJoin('subjects', 'todos.subject_id', '=', 'subjects.id')
            ->where('todos.teacher_id', $teacherId)
            ->get();

        return response()->json($todos);
    }


    public function show($id)
    {

        $teacherId = Teacher::where('user_id', Auth::id())->firstOrFail()->user_id;
        $todo = Todo::where('id', $id)->where('teacher_id', $teacherId)->firstOrFail();
        return response()->json($todo);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fileUrl' => 'nullable|url',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $todoData = $request->only(['type', 'title', 'description', 'fileUrl', 'subject_id']);

        if (isset($todoData['fileUrl'])) {
            $todoData['file'] = $todoData['fileUrl'];
            unset($todoData['fileUrl']);
        }


        $teacherId = Teacher::where('user_id', Auth::id())->firstOrFail()->id;
        $todoData['teacher_id'] = $teacherId;


        $todo = Todo::create($todoData);

        return response()->json($todo, 201);
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fileUrl' => 'nullable|url',
            'subject_id' => 'required|exists:subjects,id',
        ]);
    
        // Fetch teacher ID and retrieve Todo record linked to this teacher
        $teacherId = Teacher::where('user_id', Auth::id())->firstOrFail()->user_id;
        $todo = Todo::where('id', $id)->where('teacher_id', $teacherId)->firstOrFail();
    
        // Gather only fields provided in the request for a dynamic update
        $todoData = $request->only(['type', 'title', 'description', 'subject_id']);
    
        // Check if fileUrl is provided and update 'file' only if present
        if ($request->filled('fileUrl')) {
            $todoData['file'] = $request->input('fileUrl');
        }
    
        // Update the Todo with validated and filtered data
        $todo->update($todoData);
    
        return response()->json($todo, 200);
    }
    


    public function destroy($id)
    {

        $teacherId = Teacher::where('user_id', Auth::id())->firstOrFail()->user_id;
        $todo = Todo::where('id', $id)->where('teacher_id', $teacherId)->firstOrFail();

        if ($todo->file) {
            Storage::disk('public')->delete($todo->file);
        }

        $todo->delete();
        return response()->json(null, 204);
    }
}
