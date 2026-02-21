<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TimerController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check() && auth()->user()->starting_view && !request()->has('home')) {
        return redirect(auth()->user()->starting_view);
    }
    if (auth()->check()) {
        auth()->user()->update(['news_viewed_at' => now()]);
    }
    return view('welcome');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'sendMagicLink'])->name('login.send');
    Route::get('/login/verify/{user}', [AuthController::class, 'verify'])->name('login.verify');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // User management
    Route::get('/users', [UserController::class, 'index'])->name('users.index')->middleware('permission:users.view');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store')->middleware('permission:users.create');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:users.delete');

    // Role management
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:roles.view');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('permission:roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:roles.create');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('permission:roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:roles.edit');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:roles.delete');
    Route::post('/roles/{role}/copy', [RoleController::class, 'copy'])->name('roles.copy')->middleware('permission:roles.create');

    // Timer management (authorization handled in controller via group-based access)
    Route::get('/timers', [TimerController::class, 'index'])->name('timers.index');
    Route::get('/timers/create', [TimerController::class, 'create'])->name('timers.create')->middleware('permission:timers.create');
    Route::post('/timers', [TimerController::class, 'store'])->name('timers.store')->middleware('permission:timers.create');
    Route::get('/timers/{timer}/edit', [TimerController::class, 'edit'])->name('timers.edit');
    Route::put('/timers/{timer}', [TimerController::class, 'update'])->name('timers.update');
    Route::delete('/timers/{timer}', [TimerController::class, 'destroy'])->name('timers.destroy');
    Route::post('/timers/{timer}/copy', [TimerController::class, 'copy'])->name('timers.copy')->middleware('permission:timers.create');
    Route::get('/timers/{timer}/run', [TimerController::class, 'run'])->name('timers.run');
    Route::post('/timers/{timer}/state', [TimerController::class, 'updateState'])->name('timers.state.update');
    Route::patch('/timers/{timer}/settings', [TimerController::class, 'updateSettings'])->name('timers.settings.update');
    Route::post('/timers/{timer}/release-lock', [TimerController::class, 'releaseLock'])->name('timers.lock.release');

    // Group member management (AJAX)
    Route::get('/groups/search-users', [GroupController::class, 'searchUsers'])->name('groups.search-users');

    // Trash management
    Route::get('/trash', [TrashController::class, 'index'])->name('trash.index')->middleware('permission:trash.view');
    Route::delete('/trash/empty', [TrashController::class, 'empty'])->name('trash.empty')->middleware('permission:trash.delete');
    Route::get('/trash/{trash}', [TrashController::class, 'show'])->name('trash.show')->middleware('permission:trash.view');
    Route::post('/trash/{trash}/restore', [TrashController::class, 'restore'])->name('trash.restore')->middleware('permission:trash.restore');
    Route::delete('/trash/{trash}', [TrashController::class, 'destroy'])->name('trash.destroy')->middleware('permission:trash.delete');

    // App settings
    Route::get('/settings', [AppSettingController::class, 'index'])->name('settings.index')->middleware('permission:settings.manage');
    Route::put('/settings', [AppSettingController::class, 'update'])->name('settings.update')->middleware('permission:settings.manage');

    // Activity logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index')->middleware('permission:activity-logs.view');
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show')->middleware('permission:activity-logs.view');
});

// Public routes (no auth required) — must be after auth group so /timers/create matches first
Route::get('/manual', fn () => view('manual'))->name('manual');
Route::get('/timers/{timer}', [TimerController::class, 'show'])->name('timers.show');
Route::get('/timers/{timer}/state', [TimerController::class, 'getState'])->name('timers.state');
