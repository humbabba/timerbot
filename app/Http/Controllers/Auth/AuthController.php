<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\MagicLinkNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function sendMagicLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        $url = URL::temporarySignedRoute(
            'login.verify',
            now()->addMinutes(15),
            ['user' => $user->id]
        );

        // In local environment, show the link directly instead of emailing
        if (app()->environment('local')) {
            return back()->with('magic_link', $url);
        }

        $user->notify(new MagicLinkNotification($url));

        return back()->with('status', 'Magic link sent! Check your email.');
    }

    public function verify(Request $request, User $user)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired link.');
        }

        $user->update(['last_login_at' => now()]);

        Auth::login($user, remember: true);

        return redirect($user->starting_view ?: '/');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $url = URL::temporarySignedRoute(
            'login.verify',
            now()->addMinutes(15),
            ['user' => $user->id]
        );

        $user->notify(new MagicLinkNotification($url));

        return redirect()->route('login')->with('status', 'Account created! Check your email for the login link.');
    }
}
