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
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $isOperator = $request->input('login_type') === 'operator';

        if ($isOperator) {
            $credentials = $request->validate([
                'username' => ['required', 'string'],
                'password' => ['required'],
            ], [
                'username.required' => 'Username wajib diisi.',
                'password.required' => 'Password wajib diisi.',
            ]);
            $errorKey = 'username';
            $errorMsg = 'Username atau password yang Anda masukkan salah.';
        } else {
            $credentials = $request->validate([
                'email'    => ['required', 'email'],
                'password' => ['required'],
            ], [
                'email.required'    => 'Email wajib diisi.',
                'email.email'       => 'Format email tidak valid.',
                'password.required' => 'Password wajib diisi.',
            ]);
            $errorKey = 'email';
            $errorMsg = 'Email atau password yang Anda masukkan salah.';
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([$errorKey => 'Akun Anda tidak aktif. Hubungi administrator.'])->withInput();
            }

            // Pastikan login sesuai role
            if ($isOperator && $user->role !== 'operator') {
                Auth::logout();
                return back()->withErrors(['username' => 'Akun ini bukan akun operator. Gunakan tab Admin.'])->withInput();
            }
            if (!$isOperator && $user->role === 'operator') {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun operator harus login lewat tab Operator.'])->withInput();
            }

            $request->session()->regenerate();
            ActivityLog::record('login', 'User login ke sistem');
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([$errorKey => $errorMsg])->onlyInput($errorKey);
    }

    public function logout(Request $request)
    {
        ActivityLog::record('logout', 'User logout dari sistem');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Anda berhasil logout.');
    }
}
