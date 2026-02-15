<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('action')) {
            $query->ofAction($request->action);
        }

        if ($request->filled('user')) {
            $query->byUser($request->user);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('from') || $request->filled('to')) {
            $query->createdBetween($request->from, $request->to);
        }

        $logs = $query->paginate(20)->withQueryString();

        $types = ActivityLog::select('loggable_type')
            ->distinct()
            ->pluck('loggable_type')
            ->mapWithKeys(fn($type) => [$type => class_basename($type)]);

        $users = User::orderBy('name')
            ->pluck('name', 'id');

        return view('activity-logs.index', compact('logs', 'types', 'users'));
    }

    public function show(ActivityLog $activityLog)
    {
        return view('activity-logs.show', compact('activityLog'));
    }
}
