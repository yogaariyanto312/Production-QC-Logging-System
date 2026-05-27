<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    // Semua role: kirim pesan (dengan optional recipient_id)
    public function store(Request $request)
    {
        $request->validate([
            'message'      => ['required', 'string', 'max:1000'],
            'recipient_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        \App\Models\Message::create([
            'sender_id'    => auth()->id(),
            'recipient_id' => $request->recipient_id,
            'message'      => $request->message,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('chat_sent', 'Pesan terkirim.');
    }

    // Semua role: ambil pesan miliknya — dikirim ATAU diterima (AJAX)
    public function myMessages()
    {
        $userId   = auth()->id();
        $messages = \App\Models\Message::with('sender:id,name,role,department,avatar,last_seen_at')
            ->with('recipient:id,name,role,department,avatar,last_seen_at')
            ->where(function ($q) use ($userId) {
                $q->where('sender_id', $userId)
                  ->orWhere('recipient_id', $userId);
            })
            ->orderBy('created_at')
            ->get(['id', 'sender_id', 'recipient_id', 'message', 'reply', 'replied_at', 'is_read', 'created_at']);

        return response()->json($messages);
    }

    // Admin: balas pesan (AJAX)
    public function reply(Request $request, \App\Models\Message $message)
    {
        $request->validate(['reply' => ['required', 'string', 'max:1000']]);

        $message->update([
            'reply'      => $request->reply,
            'replied_at' => now(),
            'is_read'    => true,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Balasan terkirim.');
    }

    // Admin: tandai sudah dibaca
    public function markRead(\App\Models\Message $message)
    {
        $message->update(['is_read' => true]);
        return response()->json(['ok' => true]);
    }

    // Admin: hapus satu pesan
    public function destroy(\App\Models\Message $message)
    {
        $message->delete();
        return response()->json(['ok' => true]);
    }

    // Admin: hapus semua pesan dari satu operator
    public function destroyConversation($senderId)
    {
        \App\Models\Message::where('sender_id', $senderId)->delete();
        return response()->json(['ok' => true]);
    }

    // Operator: halaman chat (lama)
    public function chat()
    {
        return view('chat.index');
    }

    // Semua role: halaman chat terpadu (WhatsApp-style)
    public function chatUnified()
    {
        return view('chat.unified');
    }

    // Semua role: update last_seen_at (ping)
    public function ping()
    {
        auth()->user()->update(['last_seen_at' => now()]);
        return response()->json(['ok' => true]);
    }

    // Semua role: tandai sedang mengetik ke recipient tertentu
    public function typing(Request $request)
    {
        $request->validate(['recipient_id' => ['required', 'integer', 'exists:users,id']]);
        auth()->user()->update([
            'typing_to' => $request->recipient_id,
            'typing_at' => now(),
        ]);
        return response()->json(['ok' => true]);
    }

    // Semua role: daftar kontak sesuai role masing-masing
    public function contacts()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            // Admin melihat operator & supervisor (untuk inbox)
            $users = \App\Models\User::whereIn('role', ['operator', 'supervisor', 'visitor'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'role', 'department', 'avatar', 'last_seen_at']);
        } elseif ($user->role === 'supervisor') {
            // Supervisor melihat admin + operator & supervisor lain
            $users = \App\Models\User::whereIn('role', ['admin', 'operator', 'supervisor'])
                ->where('id', '!=', $user->id)
                ->where('is_active', true)
                ->orderByRaw("FIELD(role, 'admin', 'supervisor', 'operator')")
                ->orderBy('name')
                ->get(['id', 'name', 'role', 'department', 'avatar', 'last_seen_at']);
        } elseif ($user->role === 'visitor') {
            // Visitor hanya bisa chat dengan developer & admin
            $users = \App\Models\User::whereIn('role', ['developer', 'admin'])
                ->where('is_active', true)
                ->orderByRaw("FIELD(role, 'developer', 'admin')")
                ->orderBy('name')
                ->get(['id', 'name', 'role', 'department', 'avatar', 'last_seen_at']);
        } else {
            // Operator melihat admin & supervisor
            $users = \App\Models\User::whereIn('role', ['admin', 'supervisor'])
                ->where('is_active', true)
                ->orderByRaw("FIELD(role, 'admin', 'supervisor')")
                ->orderBy('name')
                ->get(['id', 'name', 'role', 'department', 'avatar', 'last_seen_at']);
        }

        $typingIds = \App\Models\User::where('typing_to', $user->id)
            ->where('typing_at', '>=', now()->subSeconds(8))
            ->pluck('id')
            ->all();

        $result = $users->map(fn($u) => [
            'id'           => $u->id,
            'name'         => $u->name,
            'role'         => $u->role,
            'department'   => $u->department,
            'avatar'       => $u->avatar,
            'last_seen_at' => $u->last_seen_at,
            'is_typing'    => in_array($u->id, $typingIds),
        ]);

        return response()->json($result);
    }

    // Admin/Supervisor: halaman + AJAX semua pesan (dikirim & diterima)
    public function adminMessages(Request $request)
    {
        $userId   = auth()->id();
        $messages = \App\Models\Message::with('sender:id,name,department,role,avatar,last_seen_at')
            ->with('recipient:id,name,department,role,avatar,last_seen_at')
            ->where(function ($q) use ($userId) {
                $q->whereNull('recipient_id')       // pesan lama (operator→admin tanpa recipient)
                  ->orWhere('recipient_id', $userId) // pesan yang ditujukan ke saya
                  ->orWhere('sender_id', $userId);   // pesan yang saya kirim
            })
            ->orderByDesc('created_at')
            ->get();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($messages);
        }

        $unreadCount = $messages->where('is_read', false)
            ->where('sender_id', '!=', $userId)->count();
        return view('chat.admin', compact('messages', 'unreadCount'));
    }
}
