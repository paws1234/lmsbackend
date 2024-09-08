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
        $request->validate([
            'type' => 'nullable|string',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $todo = Todo::findOrFail($id);

        $todoData = $request->only(['type', 'title', 'description']);

        // Debugging output
        \Log::info('Request Data:', $request->all());

        if ($request->hasFile('file')) {
            if ($todo->file) {
                Storage::disk('public')->delete($todo->file);
            }
            $file = $request->file('file')->store('files', 'public');
            $todoData['file'] = $file;
        } else {
            $todoData['file'] = $todo->file; // Keep the old file if no new file is provided
        }

        // Debugging output
        \Log::info('Todo Data to be Updated:', $todoData);

        $todo->update($todoData);

        // Debugging output
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