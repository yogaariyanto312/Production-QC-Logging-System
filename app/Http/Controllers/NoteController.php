<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\User;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        // Semua role aktif kecuali visitor dan diri sendiri, diurutkan berdasarkan role
        $targets = User::where('id', '!=', auth()->id())
            ->where('is_active', true)
            ->whereIn('role', ['developer', 'admin', 'supervisor', 'operator'])
            ->orderByRaw("FIELD(role, 'developer', 'admin', 'supervisor', 'operator')")
            ->orderBy('name')
            ->get(['id', 'name', 'role', 'department']);

        return view('notes.index', compact('targets'));
    }

    public function list()
    {
        $userId = auth()->id();

        $notes = Note::with(['user:id,name,role', 'targetUser:id,name'])
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('target_user_id', $userId);
            })
            ->orderByRaw('is_done ASC, CASE WHEN due_date IS NULL THEN 1 ELSE 0 END ASC, due_date ASC, created_at DESC')
            ->get();

        return response()->json($notes);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'content'        => ['nullable', 'string'],
            'due_date'       => ['nullable', 'date'],
            'color'          => ['nullable', 'string', 'in:blue,green,yellow,amber,red,purple,teal,slate'],
            'target_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $note = Note::create(array_merge($data, [
            'user_id' => auth()->id(),
            'color'   => $data['color'] ?? 'blue',
        ]));

        return response()->json($note->fresh()->load(['user:id,name,role', 'targetUser:id,name']));
    }

    public function update(Request $request, Note $note)
    {
        $userId = auth()->id();

        if ($note->user_id !== $userId && $note->target_user_id !== $userId) {
            abort(403);
        }

        // Penerima catatan hanya bisa update is_done
        if ($note->user_id !== $userId) {
            $note->update(['is_done' => $request->boolean('is_done')]);
            return response()->json($note->fresh()->load(['user:id,name,role', 'targetUser:id,name']));
        }

        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'content'        => ['nullable', 'string'],
            'due_date'       => ['nullable', 'date'],
            'color'          => ['nullable', 'string', 'in:blue,green,yellow,amber,red,purple,teal,slate'],
            'is_done'        => ['boolean'],
            'target_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $note->update($data);

        return response()->json($note->fresh()->load(['user:id,name,role', 'targetUser:id,name']));
    }

    public function destroy(Note $note)
    {
        abort_if($note->user_id !== auth()->id(), 403);
        $note->delete();
        return response()->json(['ok' => true]);
    }
}
