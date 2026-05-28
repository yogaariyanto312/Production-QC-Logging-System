<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSetting extends Model
{
    protected $fillable = [
        'telegram_token',
        'telegram_chat_id',
        'telegram_enabled',
        'discord_webhook',
        'discord_enabled',
        'reject_threshold',
        'report_enabled',
    ];

    protected $casts = [
        'telegram_enabled' => 'boolean',
        'discord_enabled'  => 'boolean',
        'report_enabled'   => 'boolean',
        'reject_threshold' => 'decimal:2',
    ];

    public static function instance(): static
    {
        return static::first() ?? static::create([
            'telegram_enabled' => false,
            'discord_enabled'  => false,
        ]);
    }
}
