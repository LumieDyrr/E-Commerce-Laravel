<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\OrderException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(string $slug)
    {
        $paket = $this->planFromSlug($slug);

        if (! $paket) {
            return redirect()->route('home.index')->with('error', 'Paket tidak ditemukan.');
        }

        $hasPending = Order::subscriptions()->where('user_id', auth()->id())->where('status', 'pending')->exists();

        if ($hasPending) {
            return redirect()->route('orders.index')->with('error', 'Anda masih memiliki pembayaran langganan yang sedang diperiksa.');
        }

        return view('pages.customers.subscription.index', [
            'paket' => $paket,
            'plan' => config("shop.plans.{$paket}"),
        ]);
    }

    public function store(Request $request, string $paket, OrderService $orders)
    {
        abort_unless(config("shop.plans.{$paket}"), 404);

        $request->validate([
            'bukti-pembayaran' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ], [
            'bukti-pembayaran.required' => 'Bukti pembayaran harus diupload.',
            'bukti-pembayaran.file' => 'Bukti pembayaran harus berupa file.',
            'bukti-pembayaran.mimes' => 'Bukti pembayaran harus berupa file JPEG, PNG, JPG, atau PDF.',
            'bukti-pembayaran.max' => 'Bukti pembayaran tidak boleh lebih dari 10 MB.',
        ]);

        try {
            $orders->placeSubscriptionOrder(auth()->user(), $paket, $request->file('bukti-pembayaran'));
        } catch (OrderException $e) {
            return redirect()->route('orders.index')->with('error', $e->getMessage());
        }

        return redirect()->route('orders.index')->with('success', 'Pembayaran berhasil dikirimkan.');
    }

    /** "paket-basic" -> "basic" (hanya jika paketnya ada di config). */
    private function planFromSlug(string $slug): ?string
    {
        $plan = str_starts_with($slug, 'paket-') ? substr($slug, 6) : null;

        return $plan && config("shop.plans.{$plan}") ? $plan : null;
    }
}
