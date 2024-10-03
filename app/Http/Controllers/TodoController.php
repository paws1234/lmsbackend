<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\Teacher; // Import the Teacher model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Auth for getting the current user
use Illuminate\Support\Facades\Storage;

class TodoController extends Controller
{
    public function index()
    {
        // Get the current user's teacher_id
        $teacherId = Teacher::where('user_id', Auth::id())->firstOrFail()->user_id;
        $todos = Todo::where('teacher_id', $teacherId)->get();
        return response()->json($todos);
    }

    public function show($id)
    {
        // Get the current user's teacher_id
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
        ]);

        $todoData = $request->only(['type', 'title', 'description', 'fileUrl']);

        if (isset($todoData['fileUrl'])) {
            $todoData['file'] = $todoData['fileUrl']; 
            unset($todoData['fileUrl']);
        }

        // Get the current user's teacher_id
        $teacherId = Teacher::where('user_id', Auth::id())->firstOrFail()->user_id;
        $todoData['teacher_id'] = $teacherId; // Set the teacher_id

        $todo = Todo::create($todoData);

        return response()->json($todo, 201);
    }

    public function update(Request $request, $id)
    {
        \Log::info('Incoming Request Data:', $request->all());

        $request->validate([
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'fileUrl' => 'nullable|url', 
        ]);

        // Get the current user's teacher_id
        $teacherId = Teacher::where('user_id', Auth::id())->firstOrFail()->user_id;
        $todo = Todo::where('id', $id)->where('teacher_id', $teacherId)->firstOrFail();
        
        $todoData = $request->only(['type', 'title', 'description']);

        if ($request->filled('fileUrl')) {
            $todoData['file'] = $request->input('fileUrl');
        }

        $todo->update($todoData);

        \Log::info('Updated Todo:', $todo->toArray());
        return response()->json($todo);
    }

    public function destroy($id)
    {
        // Get the current user's teacher_id
        $teacherId = Teacher::where('user_id', Auth::id())->firstOrFail()->user_id;
        $todo = Todo::where('id', $id)->where('teacher_id', $teacherId)->firstOrFail();

        if ($todo->file) {
            Storage::disk('public')->delete($todo->file);
        }

        $todo->delete();
        return response()->json(null, 204);
    }
}
