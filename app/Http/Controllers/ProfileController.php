<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'current_password' => ['required_with:password', 'nullable', 'string'],
        ], [
            'name.required'            => 'Nama wajib diisi.',
            'email.unique'             => 'Email sudah digunakan akun lain.',
            'password.min'             => 'Password baru minimal 6 karakter.',
            'password.confirmed'       => 'Konfirmasi password tidak cocok.',
            'current_password.required_with' => 'Password saat ini wajib diisi untuk mengubah password.',
        ]);

        // Verify current password before allowing password change
        if (!empty($data['password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                return back()
                    ->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])
                    ->withInput();
            }
            $user->password = Hash::make($data['password']);
        }

        $user->name  = $data['name'];
        $user->email = $data['email'] ?? null;
        $user->phone = $data['phone'] ?? null;
        $user->save();

        ActivityLog::record('update', "Update profil: {$user->name}");

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
