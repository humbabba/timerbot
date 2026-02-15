<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\NodeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VoiceProfileController;
use App\Http\Controllers\VoiceRewriteController;
use App\Http\Controllers\WaveController;
use App\Http\Controllers\WaveExecutionController;
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

    // Node management
    Route::get('/nodes', [NodeController::class, 'index'])->name('nodes.index')->middleware('permission:nodes.view');
    Route::get('/nodes/create', [NodeController::class, 'create'])->name('nodes.create')->middleware('permission:nodes.create');
    Route::post('/nodes', [NodeController::class, 'store'])->name('nodes.store')->middleware('permission:nodes.create');
    Route::get('/nodes/{node}', [NodeController::class, 'show'])->name('nodes.show')->middleware('permission:nodes.view');
    Route::post('/nodes/{node}/run', [NodeController::class, 'run'])->name('nodes.run')->middleware('permission:nodes.run');
    Route::post('/nodes/{node}/fetch-url', [NodeController::class, 'fetchUrl'])->name('nodes.fetch-url')->middleware('permission:nodes.run');
    Route::post('/nodes/{node}/generate-image', [NodeController::class, 'generateImage'])->name('nodes.generate-image')->middleware('permission:nodes.run');
    Route::get('/download-image', [NodeController::class, 'downloadImage'])->name('download-image');
    Route::get('/nodes/{node}/edit', [NodeController::class, 'edit'])->name('nodes.edit')->middleware('permission:nodes.edit');
    Route::put('/nodes/{node}', [NodeController::class, 'update'])->name('nodes.update')->middleware('permission:nodes.edit');
    Route::delete('/nodes/{node}', [NodeController::class, 'destroy'])->name('nodes.destroy')->middleware('permission:nodes.delete');
    Route::post('/nodes/{node}/copy', [NodeController::class, 'copy'])->name('nodes.copy')->middleware('permission:nodes.create');

    // Voice profile management
    Route::get('/voice-profiles', [VoiceProfileController::class, 'index'])->name('voice-profiles.index')->middleware('permission:voice-profiles.view');
    Route::get('/voice-profiles/create', [VoiceProfileController::class, 'create'])->name('voice-profiles.create')->middleware('permission:voice-profiles.create');
    Route::post('/voice-profiles', [VoiceProfileController::class, 'store'])->name('voice-profiles.store')->middleware('permission:voice-profiles.create');
    Route::get('/voice-profiles/{voiceProfile}', [VoiceProfileController::class, 'show'])->name('voice-profiles.show')->middleware('permission:voice-profiles.view');
    Route::get('/voice-profiles/{voiceProfile}/edit', [VoiceProfileController::class, 'edit'])->name('voice-profiles.edit')->middleware('permission:voice-profiles.edit');
    Route::put('/voice-profiles/{voiceProfile}', [VoiceProfileController::class, 'update'])->name('voice-profiles.update')->middleware('permission:voice-profiles.edit');
    Route::delete('/voice-profiles/{voiceProfile}', [VoiceProfileController::class, 'destroy'])->name('voice-profiles.destroy')->middleware('permission:voice-profiles.delete');
    Route::post('/voice-profiles/{voiceProfile}/refine', [VoiceProfileController::class, 'refine'])->name('voice-profiles.refine')->middleware('permission:voice-profiles.edit');

    // Voice rewrite management (nested under voice profiles)
    Route::get('/voice-profiles/{voiceProfile}/rewrites/create', [VoiceRewriteController::class, 'create'])->name('voice-rewrites.create')->middleware('permission:voice-rewrites.create');
    Route::post('/voice-profiles/{voiceProfile}/rewrites', [VoiceRewriteController::class, 'store'])->name('voice-rewrites.store')->middleware('permission:voice-rewrites.create');
    Route::post('/voice-profiles/{voiceProfile}/rewrites/compare', [VoiceRewriteController::class, 'compare'])->name('voice-rewrites.compare')->middleware('permission:voice-rewrites.create');
    Route::get('/voice-profiles/{voiceProfile}/rewrites/{rewrite}/edit', [VoiceRewriteController::class, 'edit'])->name('voice-rewrites.edit')->middleware('permission:voice-rewrites.edit');
    Route::put('/voice-profiles/{voiceProfile}/rewrites/{rewrite}', [VoiceRewriteController::class, 'update'])->name('voice-rewrites.update')->middleware('permission:voice-rewrites.edit');
    Route::delete('/voice-profiles/{voiceProfile}/rewrites/{rewrite}', [VoiceRewriteController::class, 'destroy'])->name('voice-rewrites.destroy')->middleware('permission:voice-rewrites.delete');
    Route::delete('/voice-profiles/{voiceProfile}/rewrites-oldest', [VoiceRewriteController::class, 'destroyOldest'])->name('voice-rewrites.destroy-oldest')->middleware('permission:voice-rewrites.delete');

    // API: Activity log wave outputs (for rewrite selector)
    Route::get('/api/activity-logs/wave-outputs', [VoiceRewriteController::class, 'waveOutputs'])->name('api.wave-outputs')->middleware('permission:voice-rewrites.create');

    // Trash management
    Route::get('/trash', [TrashController::class, 'index'])->name('trash.index')->middleware('permission:trash.view');
    Route::delete('/trash/empty', [TrashController::class, 'empty'])->name('trash.empty')->middleware('permission:trash.delete');
    Route::get('/trash/{trash}', [TrashController::class, 'show'])->name('trash.show')->middleware('permission:trash.view');
    Route::post('/trash/{trash}/restore', [TrashController::class, 'restore'])->name('trash.restore')->middleware('permission:trash.restore');
    Route::delete('/trash/{trash}', [TrashController::class, 'destroy'])->name('trash.destroy')->middleware('permission:trash.delete');

    // App settings
    Route::get('/settings', [AppSettingController::class, 'index'])->name('settings.index')->middleware('permission:settings.manage');
    Route::put('/settings', [AppSettingController::class, 'update'])->name('settings.update')->middleware('permission:settings.manage');

    // Wave management
    Route::get('/waves', [WaveController::class, 'index'])->name('waves.index')->middleware('permission:waves.view');
    Route::get('/waves/favorites', [WaveController::class, 'favorites'])->name('waves.favorites')->middleware('permission:waves.view');
    Route::get('/waves/create', [WaveController::class, 'create'])->name('waves.create')->middleware('permission:waves.create');
    Route::post('/waves', [WaveController::class, 'store'])->name('waves.store')->middleware('permission:waves.create');
    Route::post('/waves/{wave}/toggle-favorite', [WaveController::class, 'toggleFavorite'])->name('waves.toggle-favorite')->middleware('permission:waves.view');
    Route::get('/waves/{wave}', [WaveController::class, 'show'])->name('waves.show')->middleware('permission:waves.view');
    Route::get('/waves/{wave}/edit', [WaveController::class, 'edit'])->name('waves.edit')->middleware('permission:waves.edit');
    Route::put('/waves/{wave}', [WaveController::class, 'update'])->name('waves.update')->middleware('permission:waves.edit');
    Route::delete('/waves/{wave}', [WaveController::class, 'destroy'])->name('waves.destroy')->middleware('permission:waves.delete');
    Route::post('/waves/{wave}/copy', [WaveController::class, 'copy'])->name('waves.copy')->middleware('permission:waves.create');

    // Wave execution
    Route::get('/waves/{wave}/run', [WaveExecutionController::class, 'start'])->name('waves.run')->middleware('permission:waves.run');
    Route::post('/waves/{wave}/run-step', [WaveExecutionController::class, 'runStep'])->name('waves.run-step')->middleware('permission:waves.run');
    Route::post('/waves/{wave}/rerun-step', [WaveExecutionController::class, 'rerunStep'])->name('waves.rerun-step')->middleware('permission:waves.run');

    // Activity logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index')->middleware('permission:activity-logs.view');
    Route::get('/activity-logs/{activityLog}', [ActivityLogController::class, 'show'])->name('activity-logs.show')->middleware('permission:activity-logs.view');
});
