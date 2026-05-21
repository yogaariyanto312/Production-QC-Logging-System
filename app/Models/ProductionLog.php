<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'operator_name',
        'production_date',
        'shift1_qty',
        'shift2_qty',
        'shift3_qty',
        'total_qty',
        'notes',
        'status',
    ];

    protected $casts = [
        'production_date' => 'date',
        'shift1_qty' => 'integer',
        'shift2_qty' => 'integer',
        'shift3_qty' => 'integer',
        'total_qty' => 'float',
    ];

    // Relasi ke produk
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Relasi ke operator yang menginput
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByDate(\Illuminate\Database\Eloquent\Builder $query, string $date): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereDate('production_date', $date);
    }

    public function scopeByMonth(\Illuminate\Database\Eloquent\Builder $query, int $month, int $year): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereMonth('production_date', $month)
                     ->whereYear('production_date', $year);
    }

    public function scopeByProduct(\Illuminate\Database\Eloquent\Builder $query, int $productId): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('product_id', $productId);
    }

    public function scopeSearch(\Illuminate\Database\Eloquent\Builder $query, string $keyword): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereHas('product', function (\Illuminate\Database\Eloquent\Builder $q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
              ->orWhere('series', 'like', "%{$keyword}%");
        })->orWhere('notes', 'like', "%{$keyword}%");
    }
}
