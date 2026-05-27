<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return response(view('auth.login'))
            ->header('Permissions-Policy', 'publickey-credentials-get=(), publickey-credentials-create=()');
    }

    public function login(Request $request)
    {
        $request->validate([
            'identifier' => ['required', 'string'],
            'password'   => ['required'],
        ], [
            'identifier.required' => 'Username atau email wajib diisi.',
            'password.required'   => 'Password wajib diisi.',
        ]);

        $identifier = $request->input('identifier');
        $isEmail    = str_contains($identifier, '@');

        if ($isEmail) {
            $user = \App\Models\User::where('email', $identifier)->first();
        } else {
            $user = \App\Models\User::where('username', $identifier)
                ->orWhere('name', $identifier)
                ->first();
        }

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['identifier' => 'Username/email atau password salah.'])->onlyInput('identifier');
        }

        if (!$user->is_active) {
            return back()->withErrors(['identifier' => 'Akun Anda tidak aktif. Hubungi administrator.'])->onlyInput('identifier');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        ActivityLog::record('login', 'User login ke sistem');

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        ActivityLog::record('logout', 'User logout dari sistem');
        Auth::user()?->update(['last_seen_at' => null]);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda berhasil logout.');
    }
}
