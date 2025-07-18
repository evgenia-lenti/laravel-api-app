<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency_from',
        'currency_to',
        'rate',
        'retrieved_at',
    ];

    public function scopeFilter($query, $filters): Builder
    {
        return $query
            ->when($filters['currency_to'] ?? null, fn($q, $value) => $q->where('currency_to', $value))
            ->when($filters['currency_from'] ?? null, fn($q, $value) => $q->where('currency_from', $value))
            ->when($filters['retrieved_at'] ?? null, fn($q, $value) => $q->where('retrieved_at', $value))
            ->when($filters['rate'] ?? null, fn($q, $value) => $q->where('rate', $value));
    }
}
