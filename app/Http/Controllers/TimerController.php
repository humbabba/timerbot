<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Timer;
use App\Models\User;
use Illuminate\Http\Request;

class TimerController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $showAll = $request->boolean('all') && $user->isAppAdmin();

        if ($showAll) {
            $query = Timer::with('group', 'creator')->orderBy('name');
        } else {
            // Show timers the user is a group member of, plus all public timers
            $userGroupIds = $user->groups()->pluck('groups.id');

            $query = Timer::with('group', 'creator')
                ->where(function ($q) use ($userGroupIds) {
                    $q->where('visibility', 'public')
                      ->orWhereIn('group_id', $userGroupIds);
                })
                ->orderBy('name');
        }

        if ($request->filled('search')) {
            $query->search($request->search, ['name']);
        }

        if ($request->filled('from') || $request->filled('to')) {
            $query->createdBetween($request->from, $request->to);
        }

        $timers = $query->paginate(20)->withQueryString();

        return view('timers.index', compact('timers', 'showAll'));
    }

    public function create()
    {
        $groups = auth()->user()->isAppAdmin()
            ? Group::with('members')->orderBy('name')->get()
            : auth()->user()->groups()->with('members')->wherePivot('is_admin', true)->orderBy('name')->get();

        return view('timers.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'visibility' => 'required|in:public,private',
            'end_time' => 'required|date_format:H:i',
            'participant_count' => 'required|integer|min:1|max:999',
            'warnings' => 'nullable|array',
            'warnings.*.seconds_before' => 'required_with:warnings|integer|min:-3600|max:3600',
            'warnings.*.sound' => 'required_with:warnings|in:alarm,bell,beep,chime,ding,twang,warning',
            'message' => 'nullable|string|max:10000',
            'group_id' => 'nullable|exists:groups,id',
            'new_group_name' => 'nullable|string|max:255',
            'members' => 'nullable|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.is_admin' => 'boolean',
        ]);

        $user = auth()->user();

        // Handle group: use existing or create new
        $groupId = $this->resolveGroup($request, $user);

        $timer = Timer::create([
            'name' => $validated['name'],
            'visibility' => $validated['visibility'],
            'group_id' => $groupId,
            'created_by' => $user->id,
            'end_time' => $validated['end_time'],
            'participant_count' => $validated['participant_count'],
            'warnings' => $validated['warnings'] ?? null,
            'message' => $validated['message'] ?? null,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'timer' => $timer]);
        }

        return redirect()->route('timers.index')->with('status', 'Timer created successfully.');
    }

    public function show(Timer $timer)
    {
        $timer->load('group.members', 'creator');

        return view('timers.show', compact('timer'));
    }

    public function getState(Timer $timer)
    {
        $state = $timer->run_state ?? ['status' => 'idle'];

        // Always return current model values so show page picks up
        // changes made via the edit page, not just the run page
        $state['end_time'] = $timer->end_time;
        $state['total_speakers'] = $timer->participant_count;

        return response()->json($state);
    }

    public function updateState(Request $request, Timer $timer)
    {
        if (!$timer->canRun(auth()->user())) {
            abort(403);
        }

        $state = $request->input('state');
        $state['synced_at'] = round(microtime(true) * 1000);
        $timer->update(['run_state' => $state]);

        return response()->json(['success' => true]);
    }

    public function edit(Timer $timer)
    {
        $user = auth()->user();

        if (!$timer->canManage($user)) {
            abort(403, 'You do not have permission to edit this timer.');
        }

        $timer->load('group.members');

        $groups = $user->isAppAdmin()
            ? Group::with('members')->orderBy('name')->get()
            : $user->groups()->with('members')->wherePivot('is_admin', true)->orderBy('name')->get();

        return view('timers.edit', compact('timer', 'groups'));
    }

    public function update(Request $request, Timer $timer)
    {
        $user = auth()->user();

        if (!$timer->canManage($user)) {
            abort(403, 'You do not have permission to edit this timer.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'visibility' => 'required|in:public,private',
            'end_time' => 'required|date_format:H:i',
            'participant_count' => 'required|integer|min:1|max:999',
            'warnings' => 'nullable|array',
            'warnings.*.seconds_before' => 'required_with:warnings|integer|min:-3600|max:3600',
            'warnings.*.sound' => 'required_with:warnings|in:alarm,bell,beep,chime,ding,twang,warning',
            'message' => 'nullable|string|max:10000',
            'group_id' => 'nullable|exists:groups,id',
            'new_group_name' => 'nullable|string|max:255',
            'members' => 'nullable|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.is_admin' => 'boolean',
        ]);

        // Handle group: use existing or create new
        $groupId = $this->resolveGroup($request, $user);

        $timer->update([
            'name' => $validated['name'],
            'visibility' => $validated['visibility'],
            'group_id' => $groupId,
            'end_time' => $validated['end_time'],
            'participant_count' => $validated['participant_count'],
            'warnings' => $validated['warnings'] ?? null,
            'message' => $validated['message'] ?? null,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'timer' => $timer]);
        }

        return redirect()->route('timers.index')->with('status', 'Timer updated successfully.');
    }

    public function destroy(Timer $timer)
    {
        $user = auth()->user();

        if (!$timer->canManage($user)) {
            abort(403, 'You do not have permission to delete this timer.');
        }

        $timer->delete();

        return redirect()->route('timers.index')->with('status', 'Timer deleted successfully.');
    }

    public function copy(Timer $timer)
    {
        $copy = $timer->duplicate();
        $copy->update(['created_by' => auth()->id()]);

        return redirect()->route('timers.edit', $copy)->with('status', 'Timer copied successfully.');
    }

    public function updateSettings(Request $request, Timer $timer)
    {
        if (!$timer->canRun(auth()->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'participant_count' => 'required|integer|min:1|max:999',
            'end_time' => 'required|date_format:H:i,H:i:s',
        ]);
        // Normalize to H:i for storage
        $validated['end_time'] = substr($validated['end_time'], 0, 5);

        $timer->update($validated);

        return response()->json([
            'success' => true,
            'participant_count' => $timer->participant_count,
            'end_time' => $timer->end_time,
        ]);
    }

    public function run(Timer $timer)
    {
        $user = auth()->user();

        if (!$timer->canRun($user)) {
            abort(403, 'You do not have permission to run this timer.');
        }

        return view('timers.run', compact('timer'));
    }

    /**
     * Resolve group from request: use existing group_id, create new group, or null.
     */
    protected function resolveGroup(Request $request, User $user): ?int
    {
        // Creating a new group
        if ($request->filled('new_group_name')) {
            $group = Group::create([
                'name' => $request->new_group_name,
                'created_by' => $user->id,
            ]);

            // Add creator as admin
            $group->members()->attach($user->id, ['is_admin' => true]);

            // Add additional members
            if ($request->filled('members')) {
                foreach ($request->members as $member) {
                    if ($member['user_id'] != $user->id) {
                        $group->members()->attach($member['user_id'], [
                            'is_admin' => !empty($member['is_admin']),
                        ]);
                    }
                }
            }

            return $group->id;
        }

        // Using existing group
        if ($request->filled('group_id')) {
            $group = Group::findOrFail($request->group_id);

            // Sync members if provided (only if user is group admin or app admin)
            if ($request->has('members') && ($group->hasAdmin($user) || $user->isAppAdmin())) {
                $syncData = [$user->id => ['is_admin' => true]]; // Always keep current user as admin
                if ($request->filled('members')) {
                    foreach ($request->members as $member) {
                        if ($member['user_id'] != $user->id) {
                            $syncData[$member['user_id']] = [
                                'is_admin' => !empty($member['is_admin']),
                            ];
                        }
                    }
                }
                $group->members()->sync($syncData);
            }

            return $group->id;
        }

        return null;
    }
}
