<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'subscription_status',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    private ?Subscription $activeSubscriptionCache = null;
    private bool $activeSubscriptionLoaded = false;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Langganan aktif terbaru (null jika tidak ada / sudah berakhir). */
    public function activeSubscription(): ?Subscription
    {
        if (! $this->activeSubscriptionLoaded) {
            $this->activeSubscriptionCache = $this->subscriptions()->active()->latest('end_date')->first();
            $this->activeSubscriptionLoaded = true;
        }

        return $this->activeSubscriptionCache;
    }

    public function forgetActiveSubscription(): void
    {
        $this->activeSubscriptionLoaded = false;
        $this->activeSubscriptionCache = null;
    }

    /** Persentase diskon member saat ini (0 jika bukan member aktif). */
    public function memberDiscountPercent(): int
    {
        return $this->activeSubscription()?->discount_percent ?? 0;
    }
}
