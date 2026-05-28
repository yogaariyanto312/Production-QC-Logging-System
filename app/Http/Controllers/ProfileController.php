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

        $rules = [
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'    => ['nullable', 'string', 'max:20'],
            'avatar'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'current_password' => ['required_with:password', 'nullable', 'string'],
        ];

        // Tambahan validasi khusus developer
        if ($user->isDeveloper()) {
            $rules['handle']         = ['nullable', 'string', 'max:80'];
            $rules['bio']            = ['nullable', 'string', 'max:500'];
            $rules['link_instagram'] = ['nullable', 'url', 'max:255'];
            $rules['link_github']    = ['nullable', 'url', 'max:255'];
            $rules['link_portfolio'] = ['nullable', 'url', 'max:255'];
            $rules['link_email']     = ['nullable', 'email', 'max:150'];
            $rules['about_avatar']   = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'];
        }

        $data = $request->validate($rules, [
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

        // Simpan social links + about avatar jika developer
        if ($user->isDeveloper()) {
            $user->handle         = $data['handle'] ?? null;
            $user->bio            = $data['bio'] ?? null;
            $user->link_instagram = $data['link_instagram'] ?? null;
            $user->link_github    = $data['link_github'] ?? null;
            $user->link_portfolio = $data['link_portfolio'] ?? null;
            $user->link_email     = $data['link_email'] ?? null;

            if ($request->hasFile('about_avatar')) {
                if ($user->about_avatar) {
                    Storage::disk('public')->delete($user->about_avatar);
                }
                $user->about_avatar = $request->file('about_avatar')->store('avatars/about', 'public');
            }
        }

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

    public function updateAboutAvatar(Request $request)
    {
        $user = auth()->user();
        abort_if(!$user->isDeveloper(), 403);

        $request->validate([
            'about_avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ], [
            'about_avatar.required' => 'Pilih file foto terlebih dahulu.',
            'about_avatar.image'    => 'File harus berupa gambar.',
            'about_avatar.mimes'    => 'Format: JPG, PNG, WebP, atau GIF.',
            'about_avatar.max'      => 'Ukuran maksimal 5 MB.',
        ]);

        if ($user->about_avatar) {
            Storage::disk('public')->delete($user->about_avatar);
        }

        $path = $request->file('about_avatar')->store('avatars/about', 'public');
        $user->about_avatar = $path;
        $user->save();

        return back()->with('success', 'Foto halaman About berhasil diperbarui.');
    }
}
