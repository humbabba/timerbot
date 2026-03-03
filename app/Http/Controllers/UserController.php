<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public const ALLOWED_STARTING_VIEWS = [
        '/waves' => ['label' => 'All Waves', 'permission' => 'waves.view'],
        '/waves/favorites' => ['label' => 'Favorite Waves', 'permission' => 'waves.view'],
        '/nodes' => ['label' => 'All Nodes', 'permission' => 'nodes.view'],
        '/voice-profiles' => ['label' => 'Voice Profiles', 'permission' => 'voice-profiles.view'],
        '/users' => ['label' => 'Users', 'permission' => 'users.view'],
        '/roles' => ['label' => 'Roles', 'permission' => 'roles.view'],
        '/activity-logs' => ['label' => 'Activity Log', 'permission' => 'activity-logs.view'],
        '/trash' => ['label' => 'Trash', 'permission' => 'trash.view'],
        '/settings' => ['label' => 'Settings', 'permission' => 'settings.manage'],
    ];

    public static function getStartingViewsForUser($user): array
    {
        $views = ['' => 'Home (default)', '/timers' => 'Timers'];
        foreach (self::ALLOWED_STARTING_VIEWS as $path => $config) {
            if ($user->hasPermission($config['permission'])) {
                $views[$path] = $config['label'];
            }
        }
        return $views;
    }

    public function index(Request $request)
    {
        $allowedSorts = ['name', 'email', 'created_at', 'last_login_at'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $query = User::with('roles')->orderBy($sort, $direction);

        if ($request->filled('search')) {
            $query->search($request->search, ['name', 'email']);
        }

        if ($request->filled('from') || $request->filled('to')) {
            $query->createdBetween($request->from, $request->to);
        }

        $users = $query->paginate(20)->withQueryString();
        $assignableRoleIds = $this->getAssignableRoles()->pluck('id')->toArray();

        return view('users.index', compact('users', 'assignableRoleIds', 'sort', 'direction'));
    }

    public function create()
    {
        $roles = $this->getAssignableRoles();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $assignableRoleIds = $this->getAssignableRoles()->pluck('id')->toArray();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => ['required', 'exists:roles,id', function ($attribute, $value, $fail) use ($assignableRoleIds) {
                if (!in_array((int) $value, $assignableRoleIds)) {
                    $fail('You are not allowed to assign this role.');
                }
            }],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->roles()->attach($validated['role']);

        return redirect()->route('users.index')->with('status', 'User created successfully.');
    }

    public function show(User $user)
    {
        if (auth()->id() !== $user->id && !auth()->user()->hasPermission('users.view')) {
            abort(403, 'Unauthorized action.');
        }

        $user->load('roles');
        $assignableRoleIds = $this->getAssignableRoles()->pluck('id')->toArray();

        return view('users.show', compact('user', 'assignableRoleIds'));
    }

    public function edit(User $user)
    {
        $isOwnProfile = auth()->id() === $user->id;

        if (!$isOwnProfile && !auth()->user()->hasPermission('users.edit')) {
            return redirect()->route('users.show', $user);
        }

        $roles = $this->getAssignableRoles();

        if (!$isOwnProfile && array_diff($user->roles->pluck('id')->toArray(), $roles->pluck('id')->toArray())) {
            return redirect()->route('users.show', $user);
        }

        $userRoleIds = $user->roles->pluck('id')->toArray();
        $startingViews = self::getStartingViewsForUser($user);

        return view('users.edit', compact('user', 'roles', 'userRoleIds', 'isOwnProfile', 'startingViews'));
    }

    public function update(Request $request, User $user)
    {
        $isOwnProfile = auth()->id() === $user->id;

        if (!$isOwnProfile && !auth()->user()->hasPermission('users.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $assignableRoles = $this->getAssignableRoles();

        if (!$isOwnProfile) {
            $this->authorizeUserRoles($user, $assignableRoles);
        }

        $assignableRoleIds = $assignableRoles->pluck('id')->toArray();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'starting_view' => ['nullable', 'string', 'in:' . implode(',', array_keys(self::getStartingViewsForUser($user)))],
            'theme' => ['nullable', 'in:light,dark'],
        ];

        // Only allow role changes if user has users.edit permission
        if (auth()->user()->hasPermission('users.edit')) {
            $rules['role'] = ['required', 'exists:roles,id', function ($attribute, $value, $fail) use ($assignableRoleIds) {
                if (!in_array((int) $value, $assignableRoleIds)) {
                    $fail('You are not allowed to assign this role.');
                }
            }];
        }

        $validated = $request->validate($rules);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'starting_view' => $validated['starting_view'] ?? null,
            'theme' => $validated['theme'] ?? null,
        ]);

        if (auth()->user()->hasPermission('users.edit') && isset($validated['role'])) {
            $user->roles()->sync([$validated['role']]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'User updated successfully.']);
        }

        return redirect()->route('users.show', $user)->with('status', 'User updated successfully.');
    }

    public function updateTheme(Request $request)
    {
        $validated = $request->validate([
            'theme' => 'required|in:light,dark',
        ]);

        $request->user()->update(['theme' => $validated['theme']]);

        return response()->json(['success' => true]);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')->with('status', 'User deleted successfully.');
    }

    private function authorizeUserRoles(User $user, $assignableRoles): void
    {
        $assignableRoleIds = $assignableRoles->pluck('id')->toArray();
        $userRoleIds = $user->roles->pluck('id')->toArray();

        if (array_diff($userRoleIds, $assignableRoleIds)) {
            abort(403, 'You do not have permission to manage users with this role.');
        }
    }

    private function getAssignableRoles()
    {
        return auth()->user()->roles()
            ->with('assignableRoles')
            ->get()
            ->pluck('assignableRoles')
            ->flatten()
            ->unique('id')
            ->values();
    }
}
