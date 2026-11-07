<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Support\Facades\Storage;

class PaymentReceiptController extends Controller
{
    /** Bukti pembayaran hanya bisa dilihat admin atau pemilik pesanan. */
    public function show(Payment $payment)
    {
        $payment->loadMissing('order');
        $user = auth()->user();

        abort_unless($user->isAdmin() || $payment->order?->user_id === $user->id, 403);

        $path = $payment->receipt_image;

        // Bukti baru ada di disk privat; bukti lama (sebelum perbaikan) ada di disk publik.
        foreach (['local', 'public'] as $disk) {
            if ($path && Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->response($path);
            }
        }

        abort(404);
    }
}
