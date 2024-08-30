<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index()
    {
        $studentCount = Student::count();
        $students = Student::all();

        return response()->json([
            'count' => $studentCount,
            'students' => $students
        ]);
    }


    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students,email',
            'password' => 'required|string|min:8',
        ]);


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
        ]);

        if (!$user) {
            return response()->json(['error' => 'User creation failed'], 500);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        if (!$user->id) {
            return response()->json(['error' => 'User ID not found'], 500);
        }


        $student = Student::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($student, 201);
    }

    public function show($id)
    {
        return Student::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students,email,' . $id,
            'password' => 'nullable|string|min:8',
        ]);

        $student->name = $request->name;
        $student->email = $request->email;

        if ($request->password) {
            $student->password = Hash::make($request->password);
        }

        $student->save();

        return response()->json($student, 200);
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $user = User::findOrFail($student->user_id);
        $user->delete();
        $student->delete();

        return response()->json(null, 204);
    }
}