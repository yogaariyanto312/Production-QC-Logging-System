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
        'production_date',
        'shift1_qty',
        'shift2_qty',
        'shift3_qty',
        'notes',
        'status',
    ];

    protected $casts = [
        'production_date' => 'date',
        'shift1_qty' => 'integer',
        'shift2_qty' => 'integer',
        'shift3_qty' => 'integer',
        'total_qty' => 'integer',
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

    // Scope filter tanggal
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('production_date', $date);
    }

    // Scope filter bulan
    public function scopeByMonth($query, $month, $year)
    {
        return $query->whereMonth('production_date', $month)
                     ->whereYear('production_date', $year);
    }

    // Scope filter produk
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // Scope search keyword
    public function scopeSearch($query, $keyword)
    {
        return $query->whereHas('product', function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
              ->orWhere('series', 'like', "%{$keyword}%");
        })->orWhere('notes', 'like', "%{$keyword}%");
    }
}
