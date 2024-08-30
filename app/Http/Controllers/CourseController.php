<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courseCount = Course::count(); 
        $courses = Course::all();     

        return response()->json([
            'count' => $courseCount,     
            'courses' => $courses         
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $course = Course::create($request->all());

        return response()->json($course, 201);
    }

    public function show(Course $course)
    {
        return response()->json($course);
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $course->update($request->all());

        return response()->json($course);
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json(null, 204);
    }
}