<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'series',
        'unit',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productionLogs(): HasMany
    {
        return $this->hasMany(ProductionLog::class);
    }

    // Total produksi bulan ini
    public function monthlyTotal(): int
    {
        return $this->productionLogs()
            ->whereMonth('production_date', now()->month)
            ->whereYear('production_date', now()->year)
            ->sum('total_qty');
    }

    // Total produksi hari ini
    public function todayTotal(): int
    {
        return $this->productionLogs()
            ->whereDate('production_date', today())
            ->sum('total_qty');
    }

    public function getFullNameAttribute(): string
    {
        return $this->series ? "{$this->name} - {$this->series}" : $this->name;
    }
}
