<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['user_id', 'target_user_id', 'title', 'content', 'due_date', 'color', 'is_done', 'done_at'];

    protected $casts = [
        'due_date' => 'date',
        'is_done'  => 'boolean',
        'done_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
