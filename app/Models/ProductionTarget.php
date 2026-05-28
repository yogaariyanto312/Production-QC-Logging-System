<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionTarget extends Model
{
    protected $fillable = [
        'product_id',
        'target_date',
        'target_qty',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'target_date' => 'date',
        'target_qty'  => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
