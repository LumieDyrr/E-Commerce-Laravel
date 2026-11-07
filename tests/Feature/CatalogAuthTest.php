<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Review;

class CatalogAuthTest extends ShopTestCase
{
    public function test_home_and_404_pages(): void
    {
        $this->get('/')->assertOk();
        $this->get('/halaman-tidak-ada')->assertNotFound();
    }

    public function test_catalog_search_filter_and_sort(): void
    {
        $shirts = Category::create(['name' => 'Kemeja']);
        $pants = Category::create(['name' => 'Celana']);
        $this->product(['name' => 'Kemeja Putih', 'category_id' => $shirts->id, 'price' => 300000]);
        $this->product(['name' => 'Celana Jeans', 'category_id' => $pants->id, 'price' => 200000]);
        $this->product(['name' => 'Celana Chino', 'category_id' => $pants->id, 'price' => 150000]);

        $this->get('/produk')->assertOk()->assertSee('Kemeja Putih')->assertSee('Celana Jeans');
        $this->get('/produk?q=jeans')->assertOk()->assertSee('Celana Jeans')->assertDontSee('Kemeja Putih');
        $this->get('/produk?kategori=' . $shirts->id)->assertOk()->assertSee('Kemeja Putih')->assertDontSee('Celana Jeans');
        $this->get('/produk?urut=termurah')->assertOk()->assertSeeInOrder(['Celana Chino', 'Celana Jeans', 'Kemeja Putih']);
        $this->get('/produk?q=zzz')->assertOk()->assertSee('Tidak ada produk');
        $this->get('/produk?urut=hack')->assertSessionHasErrors('urut');
    }

    public function test_catalog_paginates_by_config(): void
    {
        config(['shop.per_page' => 2]);
        foreach (range(1, 5) as $i) {
            $this->product(['name' => "Produk $i"]);
        }
        $this->get('/produk')->assertOk()->assertSee('dari')->assertSee('5');
        $this->get('/produk?page=3')->assertOk();
    }

    public function test_product_detail_page_and_review_rules(): void
    {
        $product = $this->product(['name' => 'Sepatu Lari']);
        $buyer = $this->customer();
        $stranger = $this->customer();

        $this->get(route('products.show', $product->slug))->assertOk()->assertSee('Sepatu Lari')->assertSee('Belum ada ulasan');

        // Belum membeli -> tidak boleh mengulas
        $this->actingAs($buyer)->post(route('reviews.store', $product->slug), ['rating' => 5])->assertForbidden();

        Cart::create(['user_id' => $buyer->id, 'product_id' => $product->id, 'quantity' => 1]);
        $this->actingAs($buyer)->post(route('checkout.store'), $this->addressPayload());
        $order = Order::first();

        // Pesanan belum sampai -> masih ditolak
        $this->actingAs($buyer)->post(route('reviews.store', $product->slug), ['rating' => 5])->assertForbidden();

        $order->update(['status' => 'arrived']);
        $this->actingAs($buyer)->post(route('reviews.store', $product->slug), ['rating' => 9])->assertSessionHasErrors('rating');
        $this->actingAs($buyer)->post(route('reviews.store', $product->slug), ['rating' => 4, 'comment' => 'Nyaman'])->assertSessionHas('success');
        $this->actingAs($buyer)->post(route('reviews.store', $product->slug), ['rating' => 5, 'comment' => 'Sangat nyaman'])->assertSessionHas('success');

        $this->assertSame(1, Review::count()); // satu ulasan per pembeli per produk
        $this->actingAs($stranger)->post(route('reviews.store', $product->slug), ['rating' => 1])->assertForbidden();

        $this->get(route('products.show', $product->slug))->assertOk()->assertSee('Sangat nyaman');
    }

    public function test_logout_requires_post(): void
    {
        $user = $this->customer();
        // Logout lewat GET (rawan CSRF) tidak lagi berfungsi dan pengguna tetap login
        $this->actingAs($user)->get('/keluar')->assertNotFound();
        $this->assertAuthenticated();
        $this->actingAs($user)->post('/keluar')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_pages_render_logout_form_in_navbar(): void
    {
        $this->actingAs($this->customer())->get('/produk')->assertOk()->assertSee(route('logout'), false);
        $this->actingAs($this->admin())->get('/dashboard')->assertOk()->assertSee(route('logout'), false);
    }

    public function test_login_is_rate_limited(): void
    {
        $user = $this->customer(['email' => 'a@b.com']);

        foreach (range(1, 5) as $i) {
            $this->post('/masuk', ['email' => 'a@b.com', 'password' => 'salah'])->assertSessionHasErrors('email');
        }

        // percobaan ke-6 diblokir meski password benar
        $this->post('/masuk', ['email' => 'a@b.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_and_registration_work(): void
    {
        $this->post('/daftar', ['name' => 'Budi', 'email' => 'budi@x.com', 'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123', 'phone' => '081234500000'])->assertRedirect(route('home.index'));
        $this->assertAuthenticated();
        $this->post('/keluar');
        $this->asGuest();

        $this->post('/masuk', ['email' => 'budi@x.com', 'password' => 'rahasia123'])->assertRedirect(route('home.index'));
        $this->asGuest()->post('/daftar', ['name' => 'X', 'email' => 'y@x.com', 'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123', 'phone' => 'abc'])->assertSessionHasErrors('phone');
    }

    public function test_profile_update_and_password_change(): void
    {
        $user = $this->customer();
        $this->actingAs($user)->put('/profile', ['name' => 'Baru', 'email' => $user->email, 'phone' => '081111111111'])->assertSessionHas('success');
        $this->assertSame('Baru', $user->fresh()->name);

        $this->actingAs($user)->put('/profile/ubah-password', ['old-password' => 'password', 'new-password' => 'passwordbaru1',
            'new-password_confirmation' => 'passwordbaru1'])->assertSessionHas('success');
        $this->assertTrue(\Hash::check('passwordbaru1', $user->fresh()->password));
    }
}
