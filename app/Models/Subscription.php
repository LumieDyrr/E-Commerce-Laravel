<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'subscriptions_type',
        'status',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Langganan yang masih berlaku (status aktif dan belum lewat tanggal berakhir). */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->whereDate('end_date', '>=', today());
    }

    public function getDiscountPercentAttribute(): int
    {
        return (int) config("shop.plans.{$this->subscriptions_type}.discount", 0);
    }

    public function getLabelAttribute(): string
    {
        return config("shop.plans.{$this->subscriptions_type}.label", ucfirst((string) $this->subscriptions_type));
    }
}
