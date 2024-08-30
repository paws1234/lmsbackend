<?php
namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::all();
        return response()->json($subjects);
    }

    public function show($id)
    {
        $subject = Subject::findOrFail($id);
        return response()->json($subject);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subject = Subject::create($request->all());
        return response()->json($subject, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $subject = Subject::findOrFail($id);
        $subject->update($request->all());
        return response()->json($subject);
    }

    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();
        return response()->json(null, 204);
    }
}