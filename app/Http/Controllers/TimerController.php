<?php

namespace App\Http\Controllers;

use App\Models\Timer;
use Illuminate\Http\Request;

class TimerController extends Controller
{
    public function index(Request $request)
    {
        $query = Timer::orderBy('name');

        if ($request->filled('search')) {
            $query->search($request->search, ['name']);
        }

        if ($request->filled('from') || $request->filled('to')) {
            $query->createdBetween($request->from, $request->to);
        }

        $timers = $query->paginate(20)->withQueryString();

        return view('timers.index', compact('timers'));
    }

    public function create()
    {
        return view('timers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'end_time' => 'required|date_format:H:i',
            'participant_count' => 'required|integer|min:1|max:999',
            'warnings' => 'nullable|array',
            'warnings.*.seconds_before' => 'required_with:warnings|integer|min:1|max:3600',
            'warnings.*.sound' => 'required_with:warnings|in:beep,buzzer,chime,bell,horn',
        ]);

        $timer = Timer::create($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'timer' => $timer]);
        }

        return redirect()->route('timers.index')->with('status', 'Timer created successfully.');
    }

    public function show(Timer $timer)
    {
        return view('timers.show', compact('timer'));
    }

    public function edit(Timer $timer)
    {
        return view('timers.edit', compact('timer'));
    }

    public function update(Request $request, Timer $timer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'end_time' => 'required|date_format:H:i',
            'participant_count' => 'required|integer|min:1|max:999',
            'warnings' => 'nullable|array',
            'warnings.*.seconds_before' => 'required_with:warnings|integer|min:1|max:3600',
            'warnings.*.sound' => 'required_with:warnings|in:beep,buzzer,chime,bell,horn',
        ]);

        $timer->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'timer' => $timer]);
        }

        return redirect()->route('timers.index')->with('status', 'Timer updated successfully.');
    }

    public function destroy(Timer $timer)
    {
        $timer->delete();

        return redirect()->route('timers.index')->with('status', 'Timer deleted successfully.');
    }

    public function copy(Timer $timer)
    {
        $copy = $timer->duplicate();

        return redirect()->route('timers.edit', $copy)->with('status', 'Timer copied successfully.');
    }

    public function run(Timer $timer)
    {
        return view('timers.run', compact('timer'));
    }
}
