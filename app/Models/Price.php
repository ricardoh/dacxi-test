<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Price extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Scope a query to only include results for the pair (currency & coin).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Models\Pair  $pair
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePair(Builder $query, Pair $pair)
    {
        $query->where('currency', $pair->currency)
            ->where('coin', $pair->coin);
    }

    /**
     * Scope a query to only include results for the pair (currency & coin).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Support\Carbon  $pair
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeClosestToDate(Builder $query, Carbon $when)
    {
        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        $query->whereBetween('updated_at', [
            $when->copy()->subHours(3),
            $when->copy()->addHours(3),
        ]);

        if ($driver === 'sqlite') {
            $query->orderByRaw('ABS(ROUND((JULIANDAY(updated_at) - JULIANDAY(?)) * 86400)) ASC', [
                $when->format('Y-m-d H:i:s'),
            ]);
        } else {
            $query->orderByRaw('ABS(TIMESTAMPDIFF(SECOND, updated_at, ?)) ASC', [
                $when->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
