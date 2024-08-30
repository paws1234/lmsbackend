<?php

namespace App\Http\Controllers;

use App\Models\EventHandler;
use Illuminate\Http\Request;

class EventHandlerController extends Controller
{
    public function index()
    {
        $eventHandlers = EventHandler::all();
        return response()->json($eventHandlers);
    }

    public function show($id)
    {
        $eventHandler = EventHandler::findOrFail($id);
        return response()->json($eventHandler);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $eventHandler = EventHandler::create($request->all());
        return response()->json($eventHandler, 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $eventHandler = EventHandler::findOrFail($id);
        $eventHandler->update($request->all());
        return response()->json($eventHandler);
    }

    public function destroy($id)
    {
        $eventHandler = EventHandler::findOrFail($id);
        $eventHandler->delete();
        return response()->json(null, 204);
    }
}