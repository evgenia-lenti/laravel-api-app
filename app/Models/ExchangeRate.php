<?php

namespace App\Models;

use App\Http\Filters\V1\QueryBuilder;
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

    public function scopeFilter(Builder $builder, QueryBuilder $filters): Builder
    {
        return $filters->apply($builder);
    }
}
