<?php
namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with('teacher')->get();
        return response()->json($schedules);
    }

    public function show($id)
    {
        $schedule = Schedule::with('teacher')->findOrFail($id);
        return response()->json($schedule);
    }

    public function store(Request $request)
    {
        $request->validate([
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'required|date_format:H:i',
            'room' => 'required|string|max:255',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $schedule = Schedule::create($request->all());
        return response()->json($schedule->load('teacher'), 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'required|date_format:H:i',
            'room' => 'required|string|max:255',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $schedule = Schedule::findOrFail($id);
        $schedule->update($request->all());
        return response()->json($schedule->load('teacher'));
    }

    public function destroy($id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();
        return response()->json(null, 204);
    }
}