<?php

namespace App\Http\Controllers;

use App\Mail\ProfileUpdatedMail;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
            'avatar'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'current_password' => ['required_with:password', 'nullable', 'string'],
        ], [
            'name.required'            => 'Nama wajib diisi.',
            'email.unique'             => 'Email sudah digunakan akun lain.',
            'avatar.image'             => 'File harus berupa gambar.',
            'avatar.mimes'             => 'Format gambar: JPG, PNG, WebP, atau GIF.',
            'avatar.max'               => 'Ukuran foto maksimal 5 MB.',
            'password.min'             => 'Password baru minimal 6 karakter.',
            'password.confirmed'       => 'Konfirmasi password tidak cocok.',
            'current_password.required_with' => 'Password saat ini wajib diisi untuk mengubah password.',
        ]);

        // Catat nilai lama sebelum diubah (untuk deteksi perubahan & notifikasi)
        $oldName     = $user->name;
        $oldEmail    = $user->email;
        $passwordChanged = false;

        if (!empty($data['password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                return back()
                    ->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])
                    ->withInput();
            }
            $user->password  = Hash::make($data['password']);
            $passwordChanged = true;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->name  = $data['name'];
        $user->email = $data['email'] ?? null;
        $user->phone = $data['phone'] ?? null;
        $user->save();

        ActivityLog::record('update', "Update profil: {$user->name}");

        // Kirim notifikasi email jika nama, email, atau password berubah
        $changes = [];
        if ($oldName !== $user->name)   $changes[] = 'Nama';
        if ($oldEmail !== $user->email) $changes[] = 'Email';
        if ($passwordChanged)            $changes[] = 'Password';

        if (!empty($changes)) {
            // Kirim ke email lama dan/atau baru (keduanya bila email berubah)
            $notifyTo = array_unique(array_filter([$oldEmail, $user->email]));
            $updatedAt = now()->timezone('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB';
            foreach ($notifyTo as $email) {
                try {
                    Mail::to($email)->send(new ProfileUpdatedMail(
                        user: $user,
                        changes: $changes,
                        updatedAt: $updatedAt,
                    ));
                } catch (\Exception $e) {}
            }
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
