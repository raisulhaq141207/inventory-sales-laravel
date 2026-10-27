<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = ['user_id', 'sale_number', 'total', 'status', 'sold_at'];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'sold_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // hasMany: satu penjualan punya banyak item; FK (sale_id) ada di tabel sale_items.
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopeFilter(Builder $query, array $f): Builder
    {
        return $query
            ->when($f['q'] ?? null, fn (Builder $q, $v) => $q->where('sale_number', 'like', "%{$v}%"))
            ->when($f['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->when($f['from'] ?? null, fn (Builder $q, $v) => $q->whereDate('sold_at', '>=', $v))
            ->when($f['to'] ?? null, fn (Builder $q, $v) => $q->whereDate('sold_at', '<=', $v));
    }
}
