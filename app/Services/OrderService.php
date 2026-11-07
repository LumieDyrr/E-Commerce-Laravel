<?php

namespace App\Services;

use App\Exceptions\OrderException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderService
{
    /**
     * Hitung ringkasan harga: subtotal, diskon member, dan total (dibulatkan ke rupiah).
     *
     * @return array{subtotal: float, discount_percent: int, discount_amount: float, total: float}
     */
    public function quote(float $subtotal, User $user): array
    {
        $percent = $user->memberDiscountPercent();
        $discount = round($subtotal * $percent / 100);

        return [
            'subtotal' => round($subtotal),
            'discount_percent' => $percent,
            'discount_amount' => $discount,
            'total' => round($subtotal) - $discount,
        ];
    }

    /**
     * Buat pesanan produk dari daftar item [product_id => quantity].
     * Stok divalidasi di dalam transaksi (dengan row lock) agar tidak terjadi pesanan melebihi stok.
     *
     * @param  array<int,int>  $lines
     */
    public function placeProductOrder(User $user, array $lines, string $address, UploadedFile $proof): Order
    {
        if ($lines === []) {
            throw new OrderException('Tidak ada produk yang dipesan.');
        }

        $path = $proof->store('bukti-pembayaran', 'local');

        try {
            return DB::transaction(function () use ($user, $lines, $address, $path) {
                $products = Product::whereIn('id', array_keys($lines))->lockForUpdate()->get()->keyBy('id');

                $subtotal = 0;
                foreach ($lines as $productId => $qty) {
                    $product = $products->get($productId);

                    if (! $product) {
                        throw new OrderException('Salah satu produk sudah tidak tersedia.');
                    }
                    if ($qty < 1 || $product->stock_quantity < $qty) {
                        throw new OrderException("Stok \"{$product->name}\" tidak mencukupi (tersisa {$product->stock_quantity}).");
                    }

                    $subtotal += $product->final_price * $qty;
                }

                $quote = $this->quote($subtotal, $user);

                $order = Order::create([
                    'user_id' => $user->id,
                    'type' => Order::TYPE_PRODUCT,
                    'total_price' => $quote['total'],
                    'shipping_address' => $address,
                    'status' => 'pending',
                ]);

                foreach ($lines as $productId => $qty) {
                    $product = $products[$productId];
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'price' => $product->final_price,
                        'total' => $product->final_price * $qty,
                    ]);
                }

                $this->createPayment($order, $path);

                return $order;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }
    }

    public function placeSubscriptionOrder(User $user, string $plan, UploadedFile $proof): Order
    {
        $config = config("shop.plans.{$plan}");
        if (! $config) {
            throw new OrderException('Paket langganan tidak ditemukan.');
        }

        $hasPending = Order::subscriptions()->where('user_id', $user->id)->where('status', 'pending')->exists();
        if ($hasPending) {
            throw new OrderException('Anda masih memiliki pembayaran langganan yang sedang diperiksa.');
        }

        $path = $proof->store('bukti-pembayaran', 'local');

        try {
            return DB::transaction(function () use ($user, $plan, $config, $path) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'type' => Order::TYPE_SUBSCRIPTION,
                    'subscription_plan' => $plan,
                    'total_price' => $config['price'],
                    'shipping_address' => null,
                    'status' => 'pending',
                ]);

                $this->createPayment($order, $path);

                return $order;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }
    }

    /** Admin: kirim pesanan produk (cek stok ulang, kurangi stok, catat resi). */
    public function ship(Order $order, string $trackingNumber): void
    {
        DB::transaction(function () use ($order, $trackingNumber) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $this->assertPending($order, Order::TYPE_PRODUCT);

            foreach ($order->items as $item) {
                $product = Product::withTrashed()->whereKey($item->product_id)->lockForUpdate()->first();

                if (! $product || $product->stock_quantity < $item->quantity) {
                    $name = $product?->name ?? 'produk';
                    throw new OrderException("Stok {$name} tidak mencukupi untuk mengirim pesanan ini.");
                }

                $product->decrement('stock_quantity', $item->quantity);
            }

            Payment::where('order_id', $order->id)->update(['payment_status' => 'paid']);
            Shipping::updateOrCreate(['order_id' => $order->id], ['tracking_number' => $trackingNumber]);
            $order->update(['status' => 'delivered']);
        });
    }

    /** Admin: tolak pesanan produk maupun langganan yang masih pending. */
    public function reject(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $this->assertPending($order);

            Payment::where('order_id', $order->id)->update(['payment_status' => 'failed']);
            $order->update(['status' => 'rejected']);
        });
    }

    /** Admin: setujui pembayaran langganan lalu aktifkan keanggotaan. */
    public function approveSubscription(Order $order): Subscription
    {
        return DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $this->assertPending($order, Order::TYPE_SUBSCRIPTION);

            if (! config("shop.plans.{$order->subscription_plan}")) {
                throw new OrderException('Paket pada pesanan ini tidak dikenali.');
            }

            // Langganan aktif sebelumnya digantikan oleh yang baru.
            Subscription::where('user_id', $order->user_id)->where('status', 'active')->update(['status' => 'expired']);

            $start = today();
            $subscription = Subscription::create([
                'user_id' => $order->user_id,
                'subscriptions_type' => $order->subscription_plan,
                'status' => 'active',
                'start_date' => $start,
                'end_date' => $start->copy()->addDays((int) config('shop.subscription_days', 30)),
            ]);

            Payment::where('order_id', $order->id)->update(['payment_status' => 'paid']);
            $order->update(['status' => 'completed']);
            User::whereKey($order->user_id)->update(['subscription_status' => 1]);

            return $subscription;
        });
    }

    /** Pelanggan: konfirmasi pesanan telah diterima. */
    public function markReceived(Order $order): void
    {
        if ($order->type !== Order::TYPE_PRODUCT || $order->status !== 'delivered') {
            throw new OrderException('Pesanan ini belum dapat dikonfirmasi sebagai diterima.');
        }

        $order->update(['status' => 'arrived']);
    }

    private function createPayment(Order $order, string $path): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'payment_status' => 'pending',
            'receipt_image' => $path,
            'transactions_date' => now(),
        ]);
    }

    private function assertPending(Order $order, ?string $type = null): void
    {
        if ($type !== null && $order->type !== $type) {
            throw new OrderException('Jenis pesanan tidak sesuai untuk tindakan ini.');
        }
        if ($order->status !== 'pending') {
            throw new OrderException('Pesanan ini sudah diproses sebelumnya.');
        }
    }
}
