@extends('layouts.app')
@section('title', 'Catatan')
@section('page-title', 'Catatan')
@section('page-subtitle', 'Catat deadline, pengingat, atau hal penting')

@section('content')
<div x-data="notesApp()" x-init="load()" @keydown.escape.window="closeModal()">

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
        <div class="relative flex-1 max-w-xs">
            <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input x-model="search" type="text" placeholder="Cari catatan..."
                   class="w-full pl-9 pr-4 py-2 bg-slate-800 border border-slate-700 text-slate-100 text-sm
                          rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-slate-500">
        </div>

        <div class="flex items-center gap-2 ml-auto">
            <select x-model="filter"
                    class="py-2 px-3 bg-slate-800 border border-slate-700 text-slate-300 text-sm rounded-xl
                           focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                <option value="all">Semua</option>
                <option value="active">Aktif</option>
                <option value="done">Selesai</option>
                <option value="today">Deadline Hari Ini</option>
                <option value="overdue">Terlambat</option>
            </select>

            <button @click="openModal()"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm
                           font-semibold rounded-xl transition-colors shadow-lg shadow-blue-900/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Baru
            </button>
        </div>
    </div>

    {{-- Stats bar --}}
    <div class="flex gap-4 mb-6">
        <template x-for="s in stats" :key="s.label">
            <div class="flex items-center gap-2 text-sm">
                <span class="font-bold text-white" x-text="s.count"></span>
                <span class="text-slate-500" x-text="s.label"></span>
            </div>
        </template>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="flex justify-center py-24">
        <svg class="w-8 h-8 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
    </div>

    {{-- Empty state --}}
    <div x-show="!loading && filtered.length === 0" class="flex flex-col items-center justify-center py-24 text-center select-none">
        <div class="w-20 h-20 bg-slate-800 rounded-3xl flex items-center justify-center mb-4">
            <svg class="w-10 h-10 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </div>
        <p class="text-slate-400 font-medium" x-text="search || filter !== 'all' ? 'Tidak ada catatan yang cocok' : 'Belum ada catatan'"></p>
        <p class="text-slate-600 text-sm mt-1" x-show="!search && filter === 'all'">Klik "Baru" untuk mulai mencatat</p>
    </div>

    {{-- Grid --}}
    <div x-show="!loading && filtered.length > 0"
         class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <template x-for="note in filtered" :key="note.id">
            <div @click="openModal(note)"
                 class="group bg-slate-800 rounded-2xl overflow-hidden cursor-pointer
                        border border-slate-700 hover:border-slate-600
                        transition-all duration-200 hover:shadow-xl hover:-translate-y-0.5">

                {{-- Top color bar --}}
                <div class="h-1.5 w-full" :style="`background-color: ${colorHex(note.color)}`"></div>

                <div class="p-4">
                    {{-- Title row --}}
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <h3 class="font-semibold text-sm leading-snug flex-1 min-w-0"
                            :class="note.is_done ? 'text-slate-500 line-through' : 'text-white'"
                            x-text="note.title"></h3>
                        <span x-show="note.is_done"
                              class="shrink-0 text-[10px] font-bold px-1.5 py-0.5 rounded bg-green-900/40 text-green-400">
                            SELESAI
                        </span>
                    </div>

                    {{-- Badge: dari siapa / untuk siapa --}}
                    <div x-show="isAssigned(note) || note.target_user_id" class="mb-2">
                        {{-- Operator melihat: catatan dari admin/supervisor --}}
                        <span x-show="isAssigned(note)"
                              class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full
                                     bg-purple-900/40 text-purple-300">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span x-text="'Dari ' + (note.user?.name ?? 'Admin')"></span>
                        </span>
                        {{-- Admin/supervisor melihat: catatan untuk operator --}}
                        <span x-show="!isAssigned(note) && note.target_user_id"
                              class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full
                                     bg-blue-900/40 text-blue-300">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <span x-text="'Untuk ' + (note.target_user?.name ?? '')"></span>
                        </span>
                    </div>

                    {{-- Content preview --}}
                    <p x-show="note.content"
                       x-text="note.content"
                       :class="note.is_done ? 'text-slate-600' : 'text-slate-400'"
                       class="text-xs leading-relaxed line-clamp-3 mb-3"
                       style="overflow-wrap: anywhere; word-break: break-word;"></p>

                    {{-- Due date badge --}}
                    <div x-show="note.due_date" class="mb-2">
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium px-2 py-1 rounded-lg"
                              :class="dueBadgeClass(note)">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span x-text="dueLabel(note)"></span>
                        </span>
                    </div>

                    {{-- Timestamp --}}
                    <p class="text-[10px] text-slate-600" x-text="fmtDate(note.updated_at)"></p>
                </div>
            </div>
        </template>
    </div>

    {{-- ===== MODAL ===== --}}
    <div x-show="showModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="closeModal()">

        <div @click="closeModal()" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

        <div class="relative w-full max-w-2xl bg-slate-900 rounded-2xl shadow-2xl border border-slate-700 overflow-hidden"
             @click.stop x-transition>

            {{-- Header --}}
            <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-800">
                {{-- Color dot --}}
                <div class="w-3 h-3 rounded-full shrink-0" :style="`background-color: ${colorHex(form.color)}`"></div>
                <h2 class="flex-1 text-white font-bold" x-text="editId ? 'Edit Catatan' : 'Catatan Baru'"></h2>
                <button @click="closeModal()" type="button"
                        class="p-1.5 text-slate-500 hover:text-white rounded-lg hover:bg-slate-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5">

                {{-- Info: catatan dari admin (read-only untuk operator) --}}
                <div x-show="readOnly"
                     class="flex items-center gap-2 p-3 mb-4 bg-purple-900/20 border border-purple-800/50 rounded-xl">
                    <svg class="w-4 h-4 text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-purple-300">
                        Catatan dari <span class="font-semibold" x-text="form.from_name"></span>.
                        Kamu hanya bisa menandai selesai.
                    </p>
                </div>

                {{-- Two-column layout --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-stretch">

                    {{-- LEFT: Judul, Tujuan, Deadline, Checkbox --}}
                    <div class="space-y-4">

                        {{-- Title --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Judul</label>
                            <input x-model="form.title" type="text" placeholder="Judul catatan..."
                                   x-ref="titleInput"
                                   :disabled="readOnly"
                                   class="w-full px-4 py-2.5 bg-slate-800 border text-white text-sm rounded-xl
                                          focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-slate-500
                                          transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                   :class="errors.title ? 'border-red-500' : 'border-slate-700'">
                            <p x-show="errors.title" x-text="errors.title" class="mt-1 text-xs text-red-400"></p>
                        </div>

                        {{-- Tujuan (admin/supervisor saja) --}}
                        <div x-show="isPrivileged && !readOnly">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Tujuan</label>
                            <select x-model="form.target_user_id"
                                    class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 text-slate-200 text-sm
                                           rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                <option value="">Catatan pribadi</option>
                                <template x-for="op in operators" :key="op.id">
                                    <option :value="op.id"
                                            x-text="op.name + (op.department ? ' (' + op.department + ')' : '')"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Deadline --}}
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Deadline</label>
                            <div class="relative">
                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <input x-model="form.due_date" type="date"
                                       :disabled="readOnly"
                                       class="w-full pl-10 pr-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-200 text-sm
                                              rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 scheme-dark
                                              disabled:opacity-50 disabled:cursor-not-allowed">
                            </div>
                            <p x-show="form.due_date"
                               class="mt-1 text-xs font-medium"
                               :class="previewDueClass"
                               x-text="previewDueLabel"></p>
                        </div>

                        {{-- Done checkbox (edit only) --}}
                        <label x-show="!!editId"
                               class="flex items-center gap-3 p-3 bg-slate-800 rounded-xl border border-slate-700 cursor-pointer
                                      hover:bg-slate-700/60 transition-colors select-none">
                            <input type="checkbox" x-model="form.is_done"
                                   class="w-4 h-4 rounded border-slate-500 bg-slate-700 text-green-500
                                          focus:ring-green-500 focus:ring-offset-slate-900 cursor-pointer">
                            <div>
                                <p class="text-sm font-medium text-white">Tandai selesai</p>
                                <p class="text-xs text-slate-500" x-text="form.is_done ? 'Catatan ini sudah selesai' : 'Catatan masih aktif'"></p>
                            </div>
                        </label>
                    </div>

                    {{-- RIGHT: Catatan, Warna Label --}}
                    <div class="flex flex-col gap-4">

                        {{-- Content --}}
                        <div class="flex flex-col flex-1">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Catatan</label>
                            <textarea x-model="form.content"
                                      :disabled="readOnly"
                                      placeholder="Isi catatan, detail pekerjaan, hal yang perlu diingat..."
                                      class="flex-1 w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-white text-sm
                                             rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-slate-500
                                             resize-none transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                      style="min-height: 160px"></textarea>
                        </div>

                        {{-- Color picker (hanya jika bukan read-only) --}}
                        <div x-show="!readOnly">
                            <label class="block text-xs font-medium text-slate-400 mb-2">Warna Label</label>
                            <div class="flex gap-2.5 flex-wrap">
                                <template x-for="c in colors" :key="c">
                                    <button @click="form.color = c" type="button" :title="c"
                                            class="w-8 h-8 rounded-full transition-all duration-150 relative"
                                            :class="form.color === c ? 'ring-2 ring-offset-2 ring-offset-slate-900 scale-110' : 'hover:scale-110 opacity-70 hover:opacity-100'"
                                            :style="`background-color: ${colorHex(c)}`">
                                        <span x-show="form.color === c"
                                              class="absolute inset-0 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-white drop-shadow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between px-5 py-4 border-t border-slate-800 bg-slate-950/30">
                <button x-show="!!editId && !readOnly" @click="deleteNote()" type="button"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-red-400 hover:text-red-300
                               hover:bg-red-900/20 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
                <div x-show="!editId || readOnly"></div>

                <div class="flex gap-2">
                    <button @click="closeModal()" type="button"
                            class="px-4 py-2 text-sm text-slate-400 hover:text-white rounded-xl
                                   hover:bg-slate-800 transition-colors">
                        Batal
                    </button>
                    <button @click="save()" :disabled="saving"
                            class="px-5 py-2 text-sm font-semibold text-white rounded-xl transition-colors
                                   disabled:opacity-50 disabled:cursor-not-allowed"
                            :style="`background-color: ${colorHex(form.color)}`">
                        <span x-show="!saving" x-text="editId ? 'Simpan' : 'Buat'"></span>
                        <span x-show="saving">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const NOTES_BASE = window.APP_BASE;

function notesApp() {
    return {
        notes:       [],
        loading:     true,
        search:      '',
        filter:      'all',
        showModal:   false,
        saving:      false,
        editId:      null,
        errors:      {},
        form: { title: '', content: '', due_date: '', color: 'blue', is_done: false, target_user_id: '', from_name: '' },

        userId:      {{ auth()->id() }},
        isPrivileged: {{ auth()->user()->isPrivileged() ? 'true' : 'false' }},
        operators:   @json($operators),

        colors: ['blue', 'green', 'teal', 'purple', 'amber', 'red', 'yellow', 'slate'],

        colorHex(c) {
            return { blue: '#3b82f6', green: '#22c55e', teal: '#14b8a6', purple: '#a855f7',
                     amber: '#f59e0b', red: '#ef4444', yellow: '#eab308', slate: '#94a3b8' }[c] ?? '#3b82f6';
        },

        get stats() {
            const total   = this.notes.length;
            const done    = this.notes.filter(n => n.is_done).length;
            const overdue = this.notes.filter(n => n.due_date && this.daysLeft(n) < 0 && !n.is_done).length;
            const today   = this.notes.filter(n => n.due_date && this.daysLeft(n) === 0 && !n.is_done).length;
            return [
                { label: 'total', count: total },
                { label: 'aktif', count: total - done },
                { label: 'selesai', count: done },
                ...(overdue ? [{ label: 'terlambat', count: overdue }] : []),
                ...(today   ? [{ label: 'hari ini', count: today }]   : []),
            ];
        },

        get filtered() {
            let list = this.notes;
            if (this.search.trim()) {
                const q = this.search.toLowerCase();
                list = list.filter(n =>
                    n.title.toLowerCase().includes(q) ||
                    (n.content || '').toLowerCase().includes(q)
                );
            }
            if (this.filter === 'active')  list = list.filter(n => !n.is_done);
            if (this.filter === 'done')    list = list.filter(n => n.is_done);
            if (this.filter === 'today')   list = list.filter(n => n.due_date && this.daysLeft(n) === 0 && !n.is_done);
            if (this.filter === 'overdue') list = list.filter(n => n.due_date && this.daysLeft(n) < 0 && !n.is_done);
            return list;
        },

        get previewDueLabel() {
            if (!this.form.due_date) return '';
            const fake = { due_date: this.form.due_date };
            return this.dueLabel(fake);
        },

        get previewDueClass() {
            if (!this.form.due_date) return '';
            const fake = { due_date: this.form.due_date, is_done: false };
            const d = this.daysLeft(fake);
            if (d < 0)   return 'text-red-400';
            if (d === 0) return 'text-amber-400';
            if (d <= 3)  return 'text-yellow-400';
            return 'text-slate-500';
        },

        daysLeft(note) {
            if (!note.due_date) return null;
            const due   = new Date(note.due_date);
            const today = new Date();
            due.setHours(0, 0, 0, 0);
            today.setHours(0, 0, 0, 0);
            return Math.round((due - today) / 86400000);
        },

        dueLabel(note) {
            const d = this.daysLeft(note);
            if (d === null) return '';
            if (d < -1)  return `Terlambat ${Math.abs(d)} hari`;
            if (d === -1) return 'Terlambat 1 hari';
            if (d === 0)  return 'Deadline hari ini!';
            if (d === 1)  return 'Besok';
            if (d <= 7)   return `${d} hari lagi`;
            return new Date(note.due_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        },

        dueBadgeClass(note) {
            if (note.is_done) return 'bg-slate-700/40 text-slate-600';
            const d = this.daysLeft(note);
            if (d < 0)   return 'bg-red-900/40 text-red-400';
            if (d === 0) return 'bg-amber-900/50 text-amber-300';
            if (d <= 3)  return 'bg-yellow-900/40 text-yellow-400';
            return 'bg-slate-700/50 text-slate-400';
        },

        fmtDate(ts) {
            if (!ts) return '';
            return new Date(ts).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        },

        async load() {
            this.loading = true;
            try {
                const res  = await fetch(NOTES_BASE + '/notes/list', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                this.notes = await res.json();
            } catch (e) {}
            this.loading = false;
        },

        isAssigned(note) {
            return note.user_id !== this.userId && note.target_user_id === this.userId;
        },

        get readOnly() {
            if (!this.editId) return false;
            const note = this.notes.find(n => n.id === this.editId);
            return note ? this.isAssigned(note) : false;
        },

        openModal(note = null) {
            this.errors = {};
            if (note) {
                this.editId = note.id;
                this.form   = {
                    title:          note.title,
                    content:        note.content || '',
                    due_date:       note.due_date ? note.due_date.slice(0, 10) : '',
                    color:          note.color || 'blue',
                    is_done:        note.is_done,
                    target_user_id: note.target_user_id || '',
                    from_name:      note.user?.name || '',
                };
            } else {
                this.editId = null;
                this.form   = { title: '', content: '', due_date: '', color: 'blue', is_done: false, target_user_id: '', from_name: '' };
            }
            this.showModal = true;
            this.$nextTick(() => this.$refs.titleInput?.focus());
        },

        closeModal() {
            this.showModal = false;
            this.editId    = null;
        },

        async save() {
            this.errors = {};
            if (!this.readOnly && !this.form.title.trim()) { this.errors.title = 'Judul wajib diisi'; return; }
            this.saving = true;
            const token   = document.querySelector('meta[name=csrf-token]').content;
            const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' };
            const body    = this.readOnly
                ? JSON.stringify({ is_done: !!this.form.is_done })
                : JSON.stringify({
                    title:          this.form.title.trim(),
                    content:        this.form.content.trim() || null,
                    due_date:       this.form.due_date || null,
                    color:          this.form.color,
                    is_done:        !!this.form.is_done,
                    target_user_id: this.form.target_user_id || null,
                });
            try {
                const url = this.editId
                    ? `${NOTES_BASE}/notes/${this.editId}`
                    : `${NOTES_BASE}/notes`;
                const res = await fetch(url, {
                    method: this.editId ? 'PUT' : 'POST',
                    headers,
                    body,
                });
                if (res.ok) {
                    const saved = await res.json();
                    if (this.editId) {
                        this.notes = this.notes.map(n => n.id === saved.id ? saved : n);
                    } else {
                        this.notes.unshift(saved);
                    }
                    this.closeModal();
                } else {
                    const err = await res.json().catch(() => ({}));
                    if (err.errors) {
                        this.errors = Object.fromEntries(
                            Object.entries(err.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
                        );
                    }
                }
            } catch (e) {}
            this.saving = false;
        },

        async deleteNote() {
            if (!confirm('Hapus catatan ini?')) return;
            const token = document.querySelector('meta[name=csrf-token]').content;
            const res   = await fetch(`${NOTES_BASE}/notes/${this.editId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (res.ok) {
                this.notes = this.notes.filter(n => n.id !== this.editId);
                this.closeModal();
            }
        },
    };
}
</script>
@endpush
