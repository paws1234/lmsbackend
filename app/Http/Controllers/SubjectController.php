<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $teacherId = Auth::id();
        $subjects = Subject::where('teacher_id', $teacherId)->get();
        
        return response()->json($subjects);
    }
    
    public function show($id)
    {
        $teacherId = Auth::id();
        $subject = Subject::where('id', $id)->where('teacher_id', $teacherId)->firstOrFail();
        
        return response()->json($subject);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        
        $subject = Subject::create(array_merge($request->all(), ['teacher_id' => Auth::id()]));
        
        return response()->json($subject, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $teacherId = Auth::id();
        $subject = Subject::where('id', $id)->where('teacher_id', $teacherId)->firstOrFail();
        
        $subject->update($request->all());
        return response()->json($subject);
    }

    public function destroy($id)
    {
        $teacherId = Auth::id();
        $subject = Subject::where('id', $id)->where('teacher_id', $teacherId)->firstOrFail();
        
        $subject->delete();
        return response()->json(null, 204);
    }
}
