<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MessageController extends Controller
{
    // Operator: kirim pesan ke admin
    public function store(Request $request)
    {
        $request->validate(['message' => ['required', 'string', 'max:1000']]);

        \App\Models\Message::create([
            'sender_id' => auth()->id(),
            'message'   => $request->message,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('chat_sent', 'Pesan terkirim.');
    }

    // Operator: ambil pesan miliknya (AJAX)
    public function myMessages()
    {
        $messages = \App\Models\Message::where('sender_id', auth()->id())
            ->orderBy('created_at')
            ->get(['id', 'message', 'reply', 'replied_at', 'created_at']);

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

    // Operator: halaman chat
    public function chat()
    {
        return view('chat.index');
    }

    // Admin: halaman + AJAX semua pesan
    public function adminMessages(Request $request)
    {
        $messages = \App\Models\Message::with('sender:id,name,department')
            ->orderByDesc('created_at')
            ->get();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($messages);
        }

        $unreadCount = $messages->where('is_read', false)->count();
        return view('chat.admin', compact('messages', 'unreadCount'));
    }
}
