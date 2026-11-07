<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_status',
        'receipt_image',
        'transactions_date',
    ];

    protected function casts(): array
    {
        return ['transactions_date' => 'datetime'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
