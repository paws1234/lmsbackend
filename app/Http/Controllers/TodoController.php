<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TodoController extends Controller
{
    public function index()
    {
        $todos = Todo::all();
        return response()->json($todos);
    }

    public function show($id)
    {
        $todo = Todo::findOrFail($id);
        return response()->json($todo);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $todoData = $request->only(['type', 'title', 'description']);

        if ($request->hasFile('file')) {
            $file = $request->file('file')->store('files', 'public');
            $todoData['file'] = $file;
        }

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
            'file' => 'nullable|string', 
        ]);
    
        $todo = Todo::findOrFail($id);
        \Log::info('Full Request Data:', $request->all());
        $todoData = $request->only(['type', 'title', 'description']);
        if ($request->has('file')) {
            if ($todo->file) {
                Storage::disk('public')->delete($todo->file);
            }
            $file = $request->input('file'); 
            $todoData['file'] = $file;
        } else {
            $todoData['file'] = $todo->file;
        }
    
        \Log::info('Todo Data to be Updated:', $todoData);
    
        $todo->update($todoData);
    
        \Log::info('Updated Todo:', $todo->toArray());
        return response()->json($todo);
    }
    

    
    






    public function destroy($id)
    {
        $todo = Todo::findOrFail($id);

        // Delete the file if it exists
        if ($todo->file) {
            Storage::disk('public')->delete($todo->file);
        }

        $todo->delete();
        return response()->json(null, 204);
    }
}