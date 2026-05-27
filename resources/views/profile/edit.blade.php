@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola informasi akun Anda')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header card --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
        <div class="bg-linear-to-r from-slate-700 to-slate-800 px-6 py-6">
            <div class="flex items-center gap-4">
                {{-- Avatar --}}
                @if(auth()->user()->isVisitor())
                {{-- Visitor: foto tampil saja, tidak bisa klik/ubah --}}
                <div class="relative shrink-0" id="avatar-wrapper">
                    @if($user->avatar)
                    <img id="avatar-preview" src="{{ Storage::disk('public')->url($user->avatar) }}" alt="Foto Profil"
                         class="w-16 h-16 rounded-2xl object-cover border-2 border-white/20">
                    @else
                    <div class="w-16 h-16 rounded-2xl bg-blue-600 flex items-center justify-center">
                        <span class="text-2xl font-black text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                    @endif
                </div>
                @else
                <div class="relative shrink-0 group" id="avatar-wrapper">
                    @if($user->avatar)
                    <img id="avatar-preview" src="{{ Storage::disk('public')->url($user->avatar) }}" alt="Foto Profil"
                         class="w-16 h-16 rounded-2xl object-cover border-2 border-white/20">
                    @else
                    <div id="avatar-initials" class="w-16 h-16 rounded-2xl bg-blue-600 flex items-center justify-center">
                        <span class="text-2xl font-black text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                    <img id="avatar-preview" src="" alt="" class="w-16 h-16 rounded-2xl object-cover border-2 border-white/20 hidden">
                    @endif
                    <label for="avatar-input"
                           class="absolute inset-0 rounded-2xl bg-black/50 flex items-center justify-center
                                  opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </label>
                </div>
                @endif
                <div>
                    <p class="text-lg font-bold text-white">{{ $user->name }}</p>
                    <p class="text-slate-400 text-sm capitalize mt-0.5">{{ $user->role }}
                        @if($user->department)
                        · {{ $user->department }}
                        @endif
                    </p>
                    <p class="text-slate-500 text-xs mt-1">Bergabung {{ $user->created_at->format('d M Y') }}</p>
                    @unless(auth()->user()->isVisitor())
                    <p class="text-slate-500 text-xs mt-0.5">Klik foto untuk mengubah · Maks. 5 MB</p>
                    @endunless
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf @method('PUT')
            @unless(auth()->user()->isVisitor())
            <input type="file" id="avatar-input" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
            @error('avatar')<p class="text-xs text-red-500 -mt-2">{{ $message }}</p>@enderror
            @endunless

            {{-- Informasi Umum --}}
            <div>
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4 uppercase tracking-wide">
                    Informasi Akun
                </h3>
                <div class="space-y-4">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                               class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                      border {{ $errors->has('name') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Email
                            <span class="ml-1 text-xs font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                               placeholder="email@perusahaan.com"
                               class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                      border {{ $errors->has('email') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            No. Telepon
                            <span class="ml-1 text-xs font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                               placeholder="Contoh: 0812-3456-7890"
                               class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                      border {{ $errors->has('phone') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                </div>
            </div>

            <hr class="border-slate-100 dark:border-slate-700">

            {{-- Ganti Password --}}
            <div>
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-1 uppercase tracking-wide">
                    Ganti Password
                </h3>
                <p class="text-xs text-slate-400 mb-4">Kosongkan jika tidak ingin mengubah password</p>
                <div class="space-y-4">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Password Saat Ini
                        </label>
                        <div class="relative">
                            <input type="password" id="current_password" name="current_password"
                                   placeholder="Masukkan password saat ini"
                                   class="w-full px-4 py-3 pr-11 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                          border {{ $errors->has('current_password') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                          focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="button" onclick="togglePwd('current_password', this)"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        @error('current_password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Password Baru
                            </label>
                            <div class="relative">
                                <input type="password" id="new_password" name="password"
                                       placeholder="Min. 6 karakter"
                                       class="w-full px-4 py-3 pr-11 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                              border {{ $errors->has('password') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                              focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button type="button" onclick="togglePwd('new_password', this)"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5 eye-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Konfirmasi Password
                            </label>
                            <input type="password" name="password_confirmation"
                                   placeholder="Ulangi password baru"
                                   class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                          border border-slate-300 dark:border-slate-600
                                          focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                </div>
            </div>

            {{-- Submit --}}
            <div class="flex gap-3 pt-2">
                <a href="{{ route('dashboard') }}"
                   class="flex-1 py-3 text-center text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700
                          hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl font-semibold transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-colors">
                    Simpan Perubahan
                </button>
            </div>

        </form>
    </div>

</div>

<script>
// Avatar preview
document.getElementById('avatar-input').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;

    if (file.size > 5 * 1024 * 1024) {
        alert('Ukuran foto maksimal 5 MB.');
        this.value = '';
        return;
    }

    const preview = document.getElementById('avatar-preview');
    const initials = document.getElementById('avatar-initials');
    const reader = new FileReader();
    reader.onload = e => {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        if (initials) initials.classList.add('hidden');
    };
    reader.readAsDataURL(file);
});

function togglePwd(id, btn) {
    const input = document.getElementById(id);
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    const paths = btn.querySelectorAll('path');
    if (isText) {
        // eye icon
        paths[0].setAttribute('d', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z');
        paths[1].setAttribute('d', 'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z');
    } else {
        // eye-off icon
        paths[0].setAttribute('d', 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21');
        paths[1].setAttribute('d', '');
    }
}
</script>
@endsection
