<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminAndSubscriptionTest extends ShopTestCase
{
    private function placeOrder(User $user, Product $product, int $qty = 2): Order
    {
        Cart::create(['user_id' => $user->id, 'product_id' => $product->id, 'quantity' => $qty]);
        $this->actingAs($user)->post(route('checkout.store'), $this->addressPayload())->assertSessionHasNoErrors();

        return Order::latest('id')->first();
    }

    public function test_customer_cannot_access_admin_area(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->actingAs($this->customer())->get('/dashboard')->assertRedirect('/');
    }

    public function test_admin_dashboard_renders_stats(): void
    {
        $this->product(['stock_quantity' => 1, 'name' => 'Stok Tipis']);
        $this->actingAs($this->admin())->get('/dashboard')->assertOk()->assertSee('Stok Tipis')->assertSee('Ringkasan');
    }

    public function test_admin_ships_order_decrements_stock_once_and_blocks_double_ship(): void
    {
        $user = $this->customer();
        $product = $this->product(['stock_quantity' => 5]);
        $order = $this->placeOrder($user, $product, 2);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('dashboard.orders.manage', $order->id))->assertOk();
        $this->actingAs($admin)->post(route('dashboard.orders.send', $order->id), ['tracking_number' => 'JNE123'])
            ->assertSessionHas('success');

        $this->assertSame(3, $product->fresh()->stock_quantity);
        $this->assertSame('delivered', $order->fresh()->status);
        $this->assertSame('paid', $order->payment->fresh()->payment_status);
        $this->assertSame('JNE123', $order->shippings->tracking_number);

        $this->actingAs($admin)->post(route('dashboard.orders.send', $order->id), ['tracking_number' => 'JNE999'])
            ->assertSessionHas('error');
        $this->assertSame(3, $product->fresh()->stock_quantity);

        // Pelanggan menerima pesanan, hanya miliknya sendiri
        $other = $this->customer();
        $this->actingAs($other)->put(route('orders.received', $order->id))->assertNotFound();
        $this->actingAs($user)->put(route('orders.received', $order->id))->assertSessionHas('success');
        $this->assertSame('arrived', $order->fresh()->status);
    }

    public function test_ship_fails_when_stock_ran_out_meanwhile(): void
    {
        $product = $this->product(['stock_quantity' => 2]);
        $order = $this->placeOrder($this->customer(), $product, 2);
        $product->update(['stock_quantity' => 1]);

        $this->actingAs($this->admin())->post(route('dashboard.orders.send', $order->id), ['tracking_number' => 'X'])
            ->assertSessionHas('error');

        $this->assertSame('pending', $order->fresh()->status);
        $this->assertSame(1, $product->fresh()->stock_quantity);
    }

    public function test_reject_does_not_touch_stock_and_only_works_on_pending(): void
    {
        $product = $this->product(['stock_quantity' => 5]);
        $order = $this->placeOrder($this->customer(), $product, 1);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('dashboard.orders.reject', $order->id))->assertSessionHas('success');
        $this->assertSame('rejected', $order->fresh()->status);
        $this->assertSame('failed', $order->payment->fresh()->payment_status);
        $this->assertSame(5, $product->fresh()->stock_quantity);

        $this->actingAs($admin)->post(route('dashboard.orders.send', $order->id), ['tracking_number' => 'A'])
            ->assertSessionHas('error');
    }

    public function test_subscription_flow_creates_valid_period_and_allows_resubscribe(): void
    {
        $user = $this->customer();
        $admin = $this->admin();

        $this->actingAs($user)->get(route('subscription.index', 'paket-basic'))->assertOk()->assertSee('199.000');
        $this->actingAs($user)->get(route('subscription.index', 'paket-ngawur'))->assertRedirect(route('home.index'));

        $this->actingAs($user)->post(route('subscription.store', 'paket-ngawur'), ['bukti-pembayaran' => $this->proof()])->assertNotFound();
        $this->actingAs($user)->post(route('subscription.store', 'premium'), ['bukti-pembayaran' => $this->proof()])
            ->assertRedirect(route('orders.index'))->assertSessionHas('success');

        $order = Order::subscriptions()->first();
        $this->assertSame('premium', $order->subscription_plan);
        $this->assertEquals(299000, $order->total_price);

        // Tidak bisa dobel selama masih pending
        $this->actingAs($user)->post(route('subscription.store', 'basic'), ['bukti-pembayaran' => $this->proof()])
            ->assertSessionHas('error');
        $this->assertSame(1, Order::subscriptions()->count());

        $this->actingAs($admin)->get(route('dashboard.subscriptions.index'))->assertOk();
        $this->actingAs($admin)->post(route('dashboard.subscriptions.approve', $order->id))->assertSessionHas('success');

        $sub = Subscription::first();
        $this->assertSame('active', $sub->status);
        $this->assertTrue($sub->end_date->gt($sub->start_date), 'end_date harus setelah start_date');
        $this->assertEquals(30, $sub->start_date->diffInDays($sub->end_date));
        $this->assertSame(1, $user->fresh()->subscription_status);
        $this->assertSame(15, $user->fresh()->memberDiscountPercent());

        // Approve ulang tidak diizinkan
        $this->actingAs($admin)->post(route('dashboard.subscriptions.approve', $order->id))->assertSessionHas('error');
        $this->assertSame(1, Subscription::count());

        // Boleh berlangganan lagi setelah yang lama disetujui (bug lama: terblokir selamanya)
        $this->actingAs($user)->get(route('subscription.index', 'paket-eksklusif'))->assertOk();
    }

    public function test_rejected_subscription_does_not_block_a_new_one(): void
    {
        $user = $this->customer();
        $this->actingAs($user)->post(route('subscription.store', 'basic'), ['bukti-pembayaran' => $this->proof()]);
        $order = Order::subscriptions()->first();
        $this->actingAs($this->admin())->post(route('dashboard.subscriptions.reject', $order->id))->assertSessionHas('success');

        $this->actingAs($user)->get(route('subscription.index', 'paket-basic'))->assertOk();
        $this->assertSame(0, $user->fresh()->subscription_status);
    }

    public function test_product_order_cannot_be_approved_via_subscription_route(): void
    {
        $order = $this->placeOrder($this->customer(), $this->product(), 1);
        $this->actingAs($this->admin())->post(route('dashboard.subscriptions.approve', $order->id))->assertNotFound();
    }

    public function test_expire_command_deactivates_old_memberships(): void
    {
        $old = $this->customer(['subscription_status' => 1]);
        Subscription::create(['user_id' => $old->id, 'subscriptions_type' => 'basic', 'status' => 'active',
            'start_date' => today()->subDays(40), 'end_date' => today()->subDays(10)]);
        $ok = $this->customer(['subscription_status' => 1]);
        Subscription::create(['user_id' => $ok->id, 'subscriptions_type' => 'basic', 'status' => 'active',
            'start_date' => today(), 'end_date' => today()->addDays(30)]);

        $this->artisan('subscriptions:expire')->assertSuccessful();

        $this->assertSame(0, $old->fresh()->subscription_status);
        $this->assertSame('expired', $old->subscriptions()->first()->status);
        $this->assertSame(1, $ok->fresh()->subscription_status);
    }

    public function test_receipt_visible_only_to_owner_and_admin(): void
    {
        $owner = $this->customer();
        $order = $this->placeOrder($owner, $this->product(), 1);
        $url = route('payments.receipt', $order->payment->id);

        $this->asGuest()->get($url)->assertRedirect(route('login'));
        $this->actingAs($this->customer())->get($url)->assertForbidden();
        $this->actingAs($owner)->get($url)->assertOk();
        $this->actingAs($this->admin())->get($url)->assertOk();
    }

    public function test_invoice_only_for_owner(): void
    {
        $owner = $this->customer();
        $order = $this->placeOrder($owner, $this->product(), 1);

        $this->actingAs($this->customer())->get(route('orders.invoice', $order->id))->assertNotFound();
        $this->actingAs($owner)->get(route('orders.invoice', $order->id))->assertOk();
    }

    public function test_order_history_renders_for_product_and_subscription_orders(): void
    {
        $user = $this->customer();
        $product = $this->product(['name' => 'Jaket Uji']);
        $this->placeOrder($user, $product, 1);
        $this->actingAs($user)->post(route('subscription.store', 'eksklusif'), ['bukti-pembayaran' => $this->proof()]);

        $this->actingAs($user)->get('/pesanan')->assertOk()->assertSee('Jaket Uji')->assertSee('Langganan Paket Eksklusif');
    }

    public function test_deleted_product_does_not_break_order_history_or_admin_view(): void
    {
        $user = $this->customer();
        $product = $this->product(['name' => 'Produk Dihapus']);
        $order = $this->placeOrder($user, $product, 1);

        $this->actingAs($this->admin())->delete(route('dashboard.products.destroy', $product->id))->assertSessionHas('success');
        $this->assertSoftDeleted($product);

        $this->actingAs($user)->get('/pesanan')->assertOk()->assertSee('Produk Dihapus');
        $this->actingAs($this->admin())->get(route('dashboard.orders.manage', $order->id))->assertOk()->assertSee('Produk Dihapus');
        $this->get(route('products.show', $product->slug))->assertNotFound();
    }

    public function test_admin_product_crud_handles_defaults_slug_and_image(): void
    {
        $admin = $this->admin();
        $category = \App\Models\Category::create(['name' => 'Kaos']);
        $payload = ['name' => 'Kaos Baru', 'description' => 'x', 'price' => 75000, 'category_id' => $category->id, 'stock_quantity' => 4];

        // tanpa diskon & tanpa gambar (bug lama: NOT NULL error)
        $this->actingAs($admin)->post(route('dashboard.products.store'), $payload)->assertSessionHas('success');
        $this->actingAs($admin)->post(route('dashboard.products.store'), $payload + ['image' => UploadedFile::fake()->image('a.jpg')])
            ->assertSessionHas('success');

        $first = Product::where('slug', 'kaos-baru')->first();
        $second = Product::where('slug', 'kaos-baru-2')->first();
        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertEquals(0, $first->discount);
        Storage::disk('public')->assertExists('products/' . $second->image);

        $this->actingAs($admin)->post(route('dashboard.products.store'), $payload + ['discount' => 150])->assertSessionHasErrors('discount');
        $this->actingAs($admin)->post(route('dashboard.products.store'), array_merge($payload, ['price' => -5]))->assertSessionHasErrors('price');

        $this->actingAs($admin)->put(route('dashboard.products.update', $first->slug), array_merge($payload, ['name' => 'Nama Lain', 'discount' => 10]))
            ->assertSessionHas('success');
        $this->assertSame('kaos-baru', $first->fresh()->slug);
        $this->assertSame('Nama Lain', $first->fresh()->name);
    }
}
