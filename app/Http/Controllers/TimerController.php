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
        $showMine = $user && $request->boolean('mine');

        $allowedSorts = ['name', 'visibility', 'end_time', 'participant_count'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'name';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        if (!$user) {
            // Guests see only public timers
            $query = Timer::with('group', 'creator')
                ->where('visibility', 'public');
        } elseif ($showMine) {
            // My Timers: only timers the user is a group member of
            $userGroupIds = $user->groups()->pluck('groups.id');

            $query = Timer::with('group', 'creator')
                ->whereIn('group_id', $userGroupIds);
        } elseif ($user->isAppAdmin()) {
            // Admin "All Timers": every timer in the database
            $query = Timer::with('group', 'creator');
        } else {
            // Member "All Timers": public timers + their group timers
            $userGroupIds = $user->groups()->pluck('groups.id');

            $query = Timer::with('group', 'creator')
                ->where(function ($q) use ($userGroupIds) {
                    $q->where('visibility', 'public')
                      ->orWhereIn('group_id', $userGroupIds);
                });
        }

        $query->orderBy($sort, $direction);

        if ($request->filled('search')) {
            $query->search($request->search, ['name']);
        }

        if ($request->filled('from') || $request->filled('to')) {
            $query->createdBetween($request->from, $request->to);
        }

        $timers = $query->paginate(20)->withQueryString();

        foreach ($timers as $timer) {
            $timer->checkOvertimeReset();
        }

        return view('timers.index', compact('timers', 'showMine', 'sort', 'direction'));
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
            'participant_term' => 'nullable|string|max:50',
            'participant_term_plural' => 'nullable|string|max:50',
            'overtime_reset_minutes' => 'required|integer|min:1|max:59',
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

        $warnings = $validated['warnings'] ?? null;
        if ($warnings) {
            usort($warnings, fn ($a, $b) => $b['seconds_before'] <=> $a['seconds_before']);
            $warnings = array_values($warnings);
        }

        $timer = Timer::create([
            'name' => $validated['name'],
            'visibility' => $validated['visibility'],
            'group_id' => $groupId,
            'created_by' => $user->id,
            'end_time' => $validated['end_time'],
            'participant_count' => $validated['participant_count'],
            'participant_term' => $validated['participant_term'] ?? 'speaker',
            'participant_term_plural' => $validated['participant_term_plural'] ?? 'speakers',
            'overtime_reset_minutes' => $validated['overtime_reset_minutes'],
            'warnings' => $warnings,
            'message' => $validated['message'] ?? null,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'timer' => $timer]);
        }

        return redirect()->route('timers.index')->with('status', 'Timer created successfully.');
    }

    public function show(Timer $timer)
    {
        if (!$timer->canView(auth()->user())) {
            abort(403);
        }

        $timer->load('group.members', 'creator');
        $timer->checkOvertimeReset();

        return view('timers.show', compact('timer'));
    }

    public function getState(Timer $timer)
    {
        $timer->checkOvertimeReset();

        $state = $timer->run_state ?? ['status' => 'idle'];

        // Always return current model values so show page picks up
        // changes made via the edit page, not just the run page
        $state['end_time'] = $timer->end_time;
        $state['total_speakers'] = $timer->participant_count;
        $state['participant_term'] = $timer->participant_term;
        $state['participant_term_plural'] = $timer->participant_term_plural;

        return response()->json($state);
    }

    public function updateState(Request $request, Timer $timer)
    {
        $user = auth()->user();

        if (!$timer->canRun($user)) {
            abort(403);
        }

        if ($timer->isLockedByOther($user)) {
            $timer->load('lockedByUser');
            return response()->json([
                'locked' => true,
                'locked_by_name' => $timer->lockedByUser?->name ?? 'another user',
            ], 423);
        }

        $timer->acquireLock($user);

        $state = $request->input('state');
        $state['synced_at'] = round(microtime(true) * 1000);
        $timer->update(['run_state' => $state]);

        return response()->json(['success' => true]);
    }

    public function edit(Timer $timer)
    {
        $user = auth()->user();

        if (!$timer->canManage($user)) {
            if ($timer->canView($user)) {
                return redirect()->route('timers.show', $timer);
            }
            abort(403);
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
            'participant_term' => 'nullable|string|max:50',
            'participant_term_plural' => 'nullable|string|max:50',
            'overtime_reset_minutes' => 'required|integer|min:1|max:59',
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

        $warnings = $validated['warnings'] ?? null;
        if ($warnings) {
            usort($warnings, fn ($a, $b) => $b['seconds_before'] <=> $a['seconds_before']);
            $warnings = array_values($warnings);
        }

        $timer->update([
            'name' => $validated['name'],
            'visibility' => $validated['visibility'],
            'group_id' => $groupId,
            'end_time' => $validated['end_time'],
            'participant_count' => $validated['participant_count'],
            'participant_term' => $validated['participant_term'] ?? 'speaker',
            'participant_term_plural' => $validated['participant_term_plural'] ?? 'speakers',
            'overtime_reset_minutes' => $validated['overtime_reset_minutes'],
            'warnings' => $warnings,
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
        $user = auth()->user();

        $copy = $timer->duplicate();

        // Create a new group for the copy and make the user its admin
        $group = Group::create([
            'name' => $copy->name,
            'created_by' => $user->id,
        ]);
        $group->members()->attach($user->id, ['is_admin' => true]);

        $copy->update([
            'created_by' => $user->id,
            'group_id' => $group->id,
        ]);

        return redirect()->route('timers.edit', $copy)->with('status', 'Timer copied successfully.');
    }

    public function updateSettings(Request $request, Timer $timer)
    {
        $user = auth()->user();

        if (!$timer->canRun($user)) {
            abort(403);
        }

        if ($timer->isLockedByOther($user)) {
            return response()->json(['locked' => true], 423);
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
            if ($timer->canView($user)) {
                return redirect()->route('timers.show', $timer);
            }
            abort(403);
        }

        if ($timer->isLockedByOther($user)) {
            $timer->load('lockedByUser');
            return response()->view('timers.locked', compact('timer'), 423);
        }

        $timer->acquireLock($user);

        return view('timers.run', compact('timer'));
    }

    public function releaseLock(Request $request, Timer $timer)
    {
        $user = auth()->user();

        if (!$timer->canRun($user)) {
            abort(403);
        }

        $timer->releaseLock($user);

        return response()->json(['success' => true]);
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
