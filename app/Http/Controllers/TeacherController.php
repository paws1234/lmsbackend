<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index()
    {
        $teacherCount = Teacher::count();
        $teachers = Teacher::all();

        return response()->json([
            'count' => $teacherCount,
            'teachers' => $teachers
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:teachers,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'teacher',
        ]);

        if (!$user) {
            return response()->json(['error' => 'User creation failed'], 500);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        if (!$user->id) {
            return response()->json(['error' => 'User ID not found'], 500);
        }

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json($teacher, 201);
    }

    public function show($id)
    {
        return Teacher::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:teachers,email,' . $id,
            'password' => 'nullable|string|min:8',
        ]);

        $teacher->name = $request->name;
        $teacher->email = $request->email;

        if ($request->password) {
            $user = User::findOrFail($teacher->user_id);
            $user->password = Hash::make($request->password);
            $user->save();
        }

        $teacher->save();

        return response()->json($teacher, 200);
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        $user = User::findOrFail($teacher->user_id);
        $user->delete();
        $teacher->delete();

        return response()->json(null, 204);
    }
}