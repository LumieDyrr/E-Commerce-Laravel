<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Storage;

class CartAndCheckoutTest extends ShopTestCase
{
    public function test_guest_is_redirected_to_login_for_cart(): void
    {
        $this->get('/keranjang')->assertRedirect(route('login'));
    }

    public function test_add_to_cart_merges_quantity_and_respects_stock(): void
    {
        $user = $this->customer();
        $product = $this->product(['stock_quantity' => 2]);

        $this->actingAs($user)->post(route('cart.store', $product->slug))->assertSessionHas('success');
        $this->actingAs($user)->post(route('cart.store', $product->slug))->assertSessionHas('success');
        $this->actingAs($user)->post(route('cart.store', $product->slug))->assertSessionHas('error');

        $this->assertEquals(2, Cart::where('user_id', $user->id)->sum('quantity'));
        $this->assertSame(1, Cart::count());
    }

    public function test_out_of_stock_product_cannot_be_added(): void
    {
        $product = $this->product(['stock_quantity' => 0]);

        $this->actingAs($this->customer())->post(route('cart.store', $product->slug))->assertSessionHas('error');
        $this->assertSame(0, Cart::count());
    }

    public function test_user_cannot_modify_someone_elses_cart(): void
    {
        $owner = $this->customer();
        $attacker = $this->customer();
        $cart = Cart::create(['user_id' => $owner->id, 'product_id' => $this->product()->id, 'quantity' => 1]);

        $this->actingAs($attacker)->post(route('cart.add', $cart->id))->assertNotFound();
        $this->actingAs($attacker)->post(route('cart.remove', $cart->id))->assertNotFound();
        $this->actingAs($attacker)->delete(route('cart.destroy', $cart->id))->assertNotFound();

        $this->assertSame(1, $cart->fresh()->quantity);
    }

    public function test_cart_page_renders_totals(): void
    {
        $user = $this->customer();
        $product = $this->product(['price' => 100000, 'discount' => 10]);
        Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 2]);

        $this->actingAs($user)->get('/keranjang')->assertOk()->assertSee('180.000');
    }

    public function test_checkout_creates_order_items_and_private_receipt(): void
    {
        $user = $this->customer();
        $p1 = $this->product(['price' => 100000, 'discount' => 10]);
        $p2 = $this->product(['price' => 50000]);
        Cart::create(['user_id' => $user->id, 'product_id' => $p1->id, 'quantity' => 2]);
        Cart::create(['user_id' => $user->id, 'product_id' => $p2->id, 'quantity' => 1]);

        $this->actingAs($user)->post(route('checkout.store'), $this->addressPayload())
            ->assertRedirect(route('orders.index'))->assertSessionHasNoErrors();

        $order = Order::first();
        $this->assertSame('product', $order->type);
        $this->assertSame('pending', $order->status);
        $this->assertEquals(230000, $order->total_price);
        $this->assertCount(2, $order->items);
        $this->assertStringContainsString('Jl. Merdeka 1, Tangerang, Banten, 15310', $order->shipping_address);
        $this->assertSame(0, Cart::count());

        $payment = $order->payment;
        Storage::disk('local')->assertExists($payment->receipt_image);
        Storage::disk('public')->assertMissing($payment->receipt_image);

        // Stok baru berkurang saat admin mengirim
        $this->assertSame(10, $p1->fresh()->stock_quantity);
    }

    public function test_checkout_applies_only_active_member_discount(): void
    {
        $member = $this->customer(['subscription_status' => 1]);
        Subscription::create(['user_id' => $member->id, 'subscriptions_type' => 'premium', 'status' => 'active',
            'start_date' => today()->subDays(5), 'end_date' => today()->addDays(25)]);
        $expired = $this->customer(['subscription_status' => 1]);
        Subscription::create(['user_id' => $expired->id, 'subscriptions_type' => 'premium', 'status' => 'active',
            'start_date' => today()->subDays(40), 'end_date' => today()->subDays(10)]);

        $product = $this->product(['price' => 200000]);

        $this->actingAs($member)->post(route('checkout.buy_now_store', $product->slug), $this->addressPayload());
        $this->actingAs($expired)->post(route('checkout.buy_now_store', $product->slug), $this->addressPayload());

        $this->assertEquals(170000, Order::where('user_id', $member->id)->value('total_price')); // diskon 15%
        $this->assertEquals(200000, Order::where('user_id', $expired->id)->value('total_price')); // sudah kedaluwarsa
    }

    public function test_checkout_rejected_when_stock_insufficient_and_nothing_is_saved(): void
    {
        $user = $this->customer();
        $product = $this->product(['stock_quantity' => 1]);
        Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 3]);

        $this->actingAs($user)->post(route('checkout.store'), $this->addressPayload())->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertSame(0, Payment::count());
        $this->assertSame(1, Cart::count());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_checkout_with_empty_cart_is_rejected(): void
    {
        $this->actingAs($this->customer())->post(route('checkout.store'), $this->addressPayload())
            ->assertRedirect(route('cart.index'))->assertSessionHas('error');
        $this->assertSame(0, Order::count());
    }

    public function test_checkout_validates_address_and_receipt(): void
    {
        $user = $this->customer();
        $product = $this->product();
        Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user)->post(route('checkout.store'), ['kode-pos' => 'abc'])
            ->assertSessionHasErrors(['alamat', 'kota', 'provinsi', 'kode-pos', 'bukti-pembayaran']);
    }

    public function test_buy_now_page_for_out_of_stock_product_is_blocked(): void
    {
        $product = $this->product(['stock_quantity' => 0]);
        $this->actingAs($this->customer())->get(route('checkout.buy_now', $product->slug))->assertSessionHas('error');
    }
}
