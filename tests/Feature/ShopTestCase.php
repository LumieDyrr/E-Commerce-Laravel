<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

abstract class ShopTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    /** actingAs() bersifat persisten; panggil ini untuk kembali menjadi tamu. */
    protected function asGuest(): static
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();

        return $this;
    }

    protected function customer(array $attrs = []): User
    {
        return User::factory()->create($attrs);
    }

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    protected function product(array $attrs = []): Product
    {
        $category = Category::first() ?? Category::create(['name' => 'Umum']);

        return Product::create(array_merge([
            'name' => 'Kaos ' . uniqid(),
            'description' => 'Kaos katun',
            'price' => 100000,
            'category_id' => $category->id,
            'stock_quantity' => 10,
            'discount' => 0,
            'image' => null,
            'slug' => 'kaos-' . uniqid(),
        ], $attrs));
    }

    protected function proof(): UploadedFile
    {
        return UploadedFile::fake()->image('bukti.jpg');
    }

    protected function addressPayload(array $extra = []): array
    {
        return array_merge([
            'alamat' => 'Jl. Merdeka 1',
            'kota' => 'Tangerang',
            'provinsi' => 'Banten',
            'kode-pos' => '15310',
            'bukti-pembayaran' => $this->proof(),
        ], $extra);
    }
}
