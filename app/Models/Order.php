<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const TYPE_PRODUCT = 'product';
    public const TYPE_SUBSCRIPTION = 'subscription';

    protected $fillable = [
        'user_id',
        'type',
        'subscription_plan',
        'total_price',
        'shipping_address',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shippings()
    {
        return $this->hasOne(Shipping::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function scopeProducts(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_PRODUCT);
    }

    public function scopeSubscriptions(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_SUBSCRIPTION);
    }

    public function isSubscription(): bool
    {
        return $this->type === self::TYPE_SUBSCRIPTION;
    }

    public function getPlanLabelAttribute(): ?string
    {
        return config("shop.plans.{$this->subscription_plan}.label");
    }
}
