<?php

namespace Tests\Feature;

use App\Models\Cart;

/** Memastikan seluruh halaman utama dapat dirender tanpa error. */
class SmokeTest extends ShopTestCase
{
    public function test_guest_pages_render(): void
    {
        foreach (['/', '/produk', '/masuk', '/daftar'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/masuk')->assertSee('Ingat saya');
    }

    public function test_customer_pages_render(): void
    {
        $user = $this->customer();
        $product = $this->product();
        Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        foreach (['/', '/produk', '/keranjang', '/pembayaran', '/pesanan', '/profile',
            '/pembayaran/' . $product->slug, '/produk/' . $product->slug,
            '/pembayaran/langganan/paket-basic', '/pembayaran/langganan/paket-premium', '/pembayaran/langganan/paket-eksklusif'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }

    public function test_admin_pages_render(): void
    {
        $admin = $this->admin();
        $product = $this->product();

        foreach (['/dashboard', '/dashboard/produk', '/dashboard/produk/tambah', '/dashboard/produk/edit/' . $product->slug,
            '/dashboard/pesanan', '/dashboard/langganan', '/dashboard/pengguna', '/dashboard/profile'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_member_sees_discount_and_badge(): void
    {
        $user = $this->customer(['subscription_status' => 1]);
        \App\Models\Subscription::create(['user_id' => $user->id, 'subscriptions_type' => 'eksklusif', 'status' => 'active',
            'start_date' => today(), 'end_date' => today()->addDays(30)]);
        $product = $this->product(['price' => 100000]);

        $this->actingAs($user)->get('/pembayaran/' . $product->slug)->assertOk()->assertSee('Diskon Member (20%)')->assertSee('80.000');
        $this->actingAs($user)->get('/produk')->assertSee('Member Eksklusif');
    }
}
