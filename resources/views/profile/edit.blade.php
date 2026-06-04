@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola informasi akun Anda')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- CARD 1: Informasi Akun + Password (+ Developer fields)             --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">

        {{-- Header / Avatar --}}
        <div class="bg-gradient-to-r from-slate-700 to-slate-800 px-6 py-6">
            <div class="flex items-center gap-4">

                @php
                    $avatarUrl = $user->avatar
                        ? (str_starts_with($user->avatar, 'http')
                            ? $user->avatar
                            : route('storage.file', ['path' => $user->avatar]))
                        : null;
                @endphp

                @if(auth()->user()->isVisitor())
                {{-- Visitor: avatar dengan fallback inisial kalau file tidak ada --}}
                <div class="relative shrink-0 w-16 h-16">
                    <div class="w-16 h-16 rounded-2xl bg-blue-600 flex items-center justify-center absolute inset-0"
                         id="vis-avatar-initials" style="{{ $avatarUrl ? 'display:none' : '' }}">
                        <span class="text-2xl font-black text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                    <img src="{{ $avatarUrl ?? '' }}" alt="Foto Profil"
                         class="w-16 h-16 rounded-2xl object-cover border-2 border-white/20 relative z-10"
                         style="{{ $avatarUrl ? '' : 'display:none' }}"
                         onerror="this.style.display='none'; document.getElementById('vis-avatar-initials').style.display='flex';">
                </div>
                @else
                {{-- Non-visitor: sama, plus hover overlay untuk klik ubah foto --}}
                <div class="relative shrink-0 group w-16 h-16" id="avatar-wrapper">
                    {{-- Initials fallback (tampil kalau belum ada foto atau foto gagal load) --}}
                    <div id="avatar-initials"
                         class="w-16 h-16 rounded-2xl bg-blue-600 flex items-center justify-center absolute inset-0"
                         style="{{ $avatarUrl ? 'display:none' : '' }}">
                        <span class="text-2xl font-black text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                    {{-- Avatar image --}}
                    <img id="avatar-preview"
                         src="{{ $avatarUrl ?? '' }}" alt="Foto Profil"
                         class="w-16 h-16 rounded-2xl object-cover border-2 border-white/20 relative z-10"
                         style="{{ $avatarUrl ? '' : 'display:none' }}"
                         onerror="this.style.display='none'; document.getElementById('avatar-initials').style.display='flex';">
                    <div onclick="document.getElementById('avatar-url-section').scrollIntoView({behavior:'smooth'})"
                         class="absolute inset-0 rounded-2xl bg-black/50 flex items-center justify-center
                                opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    </div>
                </div>
                @endif

                <div>
                    <p class="text-lg font-bold text-white">{{ $user->name }}</p>
                    <p class="text-slate-400 text-sm capitalize mt-0.5">{{ $user->role }}
                        @if($user->department) · {{ $user->department }} @endif
                    </p>
                    <p class="text-slate-500 text-xs mt-1">Bergabung {{ $user->created_at->format('d M Y') }}</p>
                    @unless(auth()->user()->isVisitor())
                    <p class="text-slate-500 text-xs mt-0.5">Klik foto untuk scroll ke field link foto</p>
                    @endunless
                </div>
            </div>
        </div>

        {{-- ── MAIN PROFILE FORM ── --}}
        <form method="POST" action="{{ route('profile.update') }}" class="p-6 space-y-5">
            @csrf @method('PUT')

            {{-- URL Foto Profil --}}
            @unless(auth()->user()->isVisitor())
            <div id="avatar-url-section">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    Link Foto Profil
                    <span class="ml-1 text-xs font-normal text-slate-400">(URL gambar / Google Drive)</span>
                </label>
                <div class="flex gap-2">
                    <input type="url" id="avatar-url-input" name="avatar"
                           value="{{ old('avatar', str_starts_with($user->avatar ?? '', 'http') ? $user->avatar : '') }}"
                           placeholder="https://... link langsung ke gambar"
                           class="flex-1 px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                  border {{ $errors->has('avatar') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <button type="button" onclick="previewAvatarUrl()"
                            class="shrink-0 px-3 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600
                                   text-slate-600 dark:text-slate-300 rounded-xl transition-colors text-sm font-medium">
                        Preview
                    </button>
                </div>
                @error('avatar')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                <div class="mt-2 bg-slate-50 dark:bg-slate-700/50 rounded-xl p-3 space-y-2">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300">Cara upload foto:</p>
                    <ol class="text-xs text-slate-500 dark:text-slate-400 space-y-1 list-none">
                        <li>1. Buka <a href="https://imgbb.com" target="_blank" class="text-blue-500 hover:underline font-medium">imgbb.com</a> → upload foto</li>
                        <li>2. Setelah upload, di bawah foto ada pilihan link:</li>
                    </ol>
                    <div class="bg-white dark:bg-slate-800 rounded-lg p-2.5 text-xs space-y-1 border border-slate-200 dark:border-slate-600">
                        <p class="text-red-500">❌ <span class="line-through">https://ibb.co/SwhYCW43</span> <span class="text-slate-400">(Viewer — jangan ini)</span></p>
                        <p class="text-green-500">✓ <span class="font-mono">https://i.ibb.co/xxx/foto.jpg</span> <span class="text-slate-400">(Direct link — ini yang benar)</span></p>
                    </div>
                    <p id="avatar-url-hint" class="hidden text-xs text-amber-500 font-medium">
                        ⚠ Saat upload selesai, pilih dropdown di bawah gambar → pilih <strong>Direct link</strong>
                    </p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 italic">
                        Google Drive juga bisa — link share akan otomatis dikonversi.
                    </p>
                </div>
                <div id="avatar-url-preview" class="mt-2 hidden items-center gap-3">
                    <img id="avatar-url-preview-img" src="" alt="Preview"
                         class="w-14 h-14 rounded-xl object-cover border border-slate-200 dark:border-slate-700"
                         onerror="document.getElementById('avatar-url-preview').classList.add('hidden');
                                  document.getElementById('avatar-url-error').classList.remove('hidden');">
                    <p class="text-xs text-green-500">Preview berhasil dimuat</p>
                </div>
                <p id="avatar-url-error" class="mt-1 text-xs text-red-500 hidden">
                    Gambar tidak bisa dimuat — pastikan link langsung ke file gambar.
                </p>
            </div>
            @endunless

            {{-- Informasi Akun --}}
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
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                               placeholder="email@perusahaan.com" required
                               class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                      border {{ $errors->has('email') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                      focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
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
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        {{-- ── END MAIN FORM ── --}}

    </div>
    {{-- END CARD 1 --}}



</div>

<script>
// ── Avatar profil: preview dari URL ──
function convertToDirectImageUrl(url) {
    // Google Drive: /file/d/FILE_ID/view  atau  /file/d/FILE_ID/
    const gdFile = url.match(/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/);
    if (gdFile) return `https://drive.google.com/uc?export=view&id=${gdFile[1]}`;

    // Google Drive: open?id=FILE_ID  atau  uc?id=FILE_ID
    const gdOpen = url.match(/drive\.google\.com\/(?:open|uc)\?(?:.*&)?id=([a-zA-Z0-9_-]+)/);
    if (gdOpen) return `https://drive.google.com/uc?export=view&id=${gdOpen[1]}`;

    // Google Drive thumbnail: thumbnail?id=FILE_ID
    const gdThumb = url.match(/drive\.google\.com\/thumbnail\?(?:.*&)?id=([a-zA-Z0-9_-]+)/);
    if (gdThumb) return `https://drive.google.com/uc?export=view&id=${gdThumb[1]}`;

    return url; // URL lain dikembalikan apa adanya
}

function previewAvatarUrl() {
    const input      = document.getElementById('avatar-url-input');
    const preview    = document.getElementById('avatar-url-preview');
    const previewImg = document.getElementById('avatar-url-preview-img');
    const error      = document.getElementById('avatar-url-error');
    const hint       = document.getElementById('avatar-url-hint');
    const raw        = input?.value?.trim();
    if (!raw) return;

    // Deteksi link viewer imgbb (ibb.co/xxxx) bukan direct (i.ibb.co/xxxx/...)
    if (/^https?:\/\/ibb\.co\//i.test(raw) && !/^https?:\/\/i\.ibb\.co\//i.test(raw)) {
        preview.classList.add('hidden');
        error.classList.remove('hidden');
        error.textContent = 'Ini adalah link halaman viewer imgbb. Salin "Direct link" yang dimulai dengan https://i.ibb.co/...';
        if (hint) hint.classList.remove('hidden');
        return;
    }

    const url = convertToDirectImageUrl(raw);
    input.value = url;

    error.classList.add('hidden');
    if (hint) hint.classList.add('hidden');
    preview.classList.remove('hidden');
    preview.classList.add('flex');
    previewImg.src = url;

    const headerImg = document.getElementById('avatar-preview');
    const initials  = document.getElementById('avatar-initials');
    if (headerImg) { headerImg.src = url; headerImg.style.display = ''; }
    if (initials)  initials.style.display = 'none';
}


// ── Toggle password visibility ──
function togglePwd(id, btn) {
    const input  = document.getElementById(id);
    const isText = input.type === 'text';
    input.type   = isText ? 'password' : 'text';
    const paths  = btn.querySelectorAll('path');
    if (isText) {
        paths[0].setAttribute('d', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z');
        paths[1].setAttribute('d', 'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z');
    } else {
        paths[0].setAttribute('d', 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21');
        paths[1].setAttribute('d', '');
    }
}
</script>
@endsection
