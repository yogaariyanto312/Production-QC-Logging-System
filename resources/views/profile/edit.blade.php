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
                        ? route('storage.file', ['path' => $user->avatar])
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
                        @if($user->department) · {{ $user->department }} @endif
                    </p>
                    <p class="text-slate-500 text-xs mt-1">Bergabung {{ $user->created_at->format('d M Y') }}</p>
                    @unless(auth()->user()->isVisitor())
                    <p class="text-slate-500 text-xs mt-0.5">Klik foto untuk mengubah · Maks. 5 MB</p>
                    @endunless
                </div>
            </div>
        </div>

        {{-- ── MAIN PROFILE FORM (tidak ada form lain di dalamnya) ── --}}
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf @method('PUT')

            {{-- Hidden file input untuk avatar (di-trigger dari label di header) --}}
            @unless(auth()->user()->isVisitor())
            <input type="file" id="avatar-input" name="avatar"
                   accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
            @error('avatar')<p class="text-xs text-red-500 -mt-2">{{ $message }}</p>@enderror
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
                            Email <span class="ml-1 text-xs font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                               placeholder="email@perusahaan.com"
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

            {{-- ── DEVELOPER: Handle, Bio, Social Links (dalam main form) ── --}}
            @if(auth()->user()->isDeveloper())
            <hr class="border-slate-100 dark:border-slate-700">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-full uppercase tracking-wide">Developer</span>
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
                        Social Links
                    </h3>
                </div>
                <p class="text-xs text-slate-400 mb-4">Ditampilkan di halaman <em>Tentang Aplikasi</em></p>

                <div class="space-y-4">

                    {{-- Handle --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Handle <span class="ml-1 text-xs font-normal text-slate-400">(contoh: @qc.yoga)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">@</span>
                            <input type="text" name="handle"
                                   value="{{ old('handle', ltrim($user->handle ?? '', '@')) }}"
                                   placeholder="qc.yoga"
                                   class="w-full pl-8 pr-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                          border {{ $errors->has('handle') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                          focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        @error('handle')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Bio --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Bio <span class="ml-1 text-xs font-normal text-slate-400">(maks 500 karakter)</span>
                        </label>
                        <textarea name="bio" rows="3"
                                  placeholder="Tuliskan bio singkat tentang kamu..."
                                  class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                         border {{ $errors->has('bio') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                         focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    {{-- Social Links Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-pink-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                    Instagram
                                </span>
                            </label>
                            <input type="url" name="link_instagram"
                                   value="{{ old('link_instagram', $user->link_instagram) }}"
                                   placeholder="https://instagram.com/username"
                                   class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                          border {{ $errors->has('link_instagram') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                          focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('link_instagram')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-600 dark:text-slate-300" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                    </svg>
                                    GitHub
                                </span>
                            </label>
                            <input type="url" name="link_github"
                                   value="{{ old('link_github', $user->link_github) }}"
                                   placeholder="https://github.com/username"
                                   class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                          border {{ $errors->has('link_github') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                          focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('link_github')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Portfolio
                                </span>
                            </label>
                            <input type="url" name="link_portfolio"
                                   value="{{ old('link_portfolio', $user->link_portfolio) }}"
                                   placeholder="https://namakamu.com"
                                   class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                          border {{ $errors->has('link_portfolio') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                          focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('link_portfolio')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Email Publik
                                </span>
                            </label>
                            <input type="email" name="link_email"
                                   value="{{ old('link_email', $user->link_email) }}"
                                   placeholder="email@domain.com"
                                   class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white
                                          border {{ $errors->has('link_email') ? 'border-red-500' : 'border-slate-300 dark:border-slate-600' }}
                                          focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('link_email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                    </div>
                </div>
            </div>
            @endif
            {{-- ── END DEVELOPER ── --}}

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


    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    {{-- CARD 2: Foto Halaman About (Developer only) — form TERPISAH        --}}
    {{-- ═══════════════════════════════════════════════════════════════════ --}}
    @if(auth()->user()->isDeveloper())
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">

        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
            <span class="px-2 py-0.5 text-[10px] font-bold bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 rounded-full uppercase tracking-wide">Developer</span>
            <div>
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wide">
                    Foto Halaman About
                </h3>
                <p class="text-xs text-slate-400">Berbeda dari foto profil akun · Ditampilkan di halaman <em>Tentang Aplikasi</em></p>
            </div>
        </div>

        <form method="POST"
              action="{{ route('profile.about-avatar') }}"
              enctype="multipart/form-data"
              id="about-avatar-form"
              class="p-6">
            @csrf

            <div style="display:flex; align-items:center; gap:16px;">

                {{-- Preview foto --}}
                <div class="shrink-0 rounded-2xl overflow-hidden border-2 border-slate-200 dark:border-slate-600"
                     style="width:80px; height:80px; background: linear-gradient(145deg, #93c5fd, #1d4ed8);">
                    {{-- Initials fallback --}}
                    <div id="about-avatar-placeholder"
                         style="width:100%; height:100%; display:{{ $user->about_avatar ? 'none' : 'flex' }}; align-items:center; justify-content:center;">
                        <span style="font-size:1.5rem; font-weight:900; color:white;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    </div>
                    {{-- About avatar image --}}
                    <img id="about-avatar-preview"
                         src="{{ $user->about_avatar ? route('storage.file', ['path' => $user->about_avatar]) : '' }}"
                         alt="Foto About"
                         style="width:100%; height:100%; object-fit:cover; display:{{ $user->about_avatar ? 'block' : 'none' }};"
                         onerror="this.style.display='none'; document.getElementById('about-avatar-placeholder').style.display='flex';">
                </div>

                {{-- Kontrol --}}
                <div>
                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <label for="about-avatar-input"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700
                                      hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300
                                      text-sm font-semibold rounded-xl cursor-pointer transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Pilih Foto
                        </label>
                        <input type="file" id="about-avatar-input" name="about_avatar"
                               accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
                        <button type="submit" id="about-avatar-submit"
                                style="display:none;"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700
                                       text-white text-sm font-semibold rounded-xl transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Simpan Foto
                        </button>
                    </div>
                    <p class="text-xs text-slate-400 mt-1.5">JPG, PNG, WebP · Maks 5 MB</p>
                    @error('about_avatar')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    @if(session('success') && request()->routeIs('profile.edit'))
                    {{-- success flash sudah di-handle layout, tidak perlu ulang --}}
                    @endif
                </div>

            </div>
        </form>

    </div>
    @endif
    {{-- END CARD 2 --}}

</div>

<script>
// ── Avatar profil: auto-submit saat foto dipilih ──
const avatarInput = document.getElementById('avatar-input');
if (avatarInput) {
    avatarInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran foto maksimal 5 MB.');
            this.value = '';
            return;
        }
        const preview  = document.getElementById('avatar-preview');
        const initials = document.getElementById('avatar-initials');
        const reader   = new FileReader();
        reader.onload = e => {
            if (preview) { preview.src = e.target.result; preview.style.display = ''; }
            if (initials) initials.style.display = 'none';
            this.closest('form')?.submit();
        };
        reader.readAsDataURL(file);
    });
}

// ── About avatar: preview + tampilkan tombol simpan ──
const aboutInput  = document.getElementById('about-avatar-input');
const aboutSubmit = document.getElementById('about-avatar-submit');
if (aboutInput) {
    aboutInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran foto maksimal 5 MB.');
            this.value = '';
            return;
        }
        const preview     = document.getElementById('about-avatar-preview');
        const placeholder = document.getElementById('about-avatar-placeholder');
        const reader      = new FileReader();
        reader.onload = e => {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(file);
        if (aboutSubmit) aboutSubmit.style.display = 'inline-flex';
    });
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
