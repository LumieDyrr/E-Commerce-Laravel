<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Services\OrderService;

class CartController extends Controller
{
    public function index(OrderService $orders)
    {
        $carts = $this->userCarts()->with('product')->get();

        $subtotal = $carts->sum(fn ($cart) => $cart->product->final_price * $cart->quantity);
        $summary = $orders->quote($subtotal, auth()->user());

        return view('pages.customers.cart.index', compact('carts', 'summary'));
    }

    public function store(string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        if (! $product->in_stock) {
            return redirect()->back()->with('error', 'Stok produk habis.');
        }

        $cart = $this->userCarts()->firstOrNew(['product_id' => $product->id], ['quantity' => 0]);

        if ($cart->quantity + 1 > $product->stock_quantity) {
            return redirect()->back()->with('error', "Stok hanya tersisa {$product->stock_quantity}.");
        }

        $cart->user_id = auth()->id();
        $cart->quantity += 1;
        $cart->save();

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang.');
    }

    public function add($id)
    {
        $cart = $this->findOwnCart($id);

        if ($cart->quantity + 1 > $cart->product->stock_quantity) {
            return redirect()->back()->with('error', "Stok hanya tersisa {$cart->product->stock_quantity}.");
        }

        $cart->increment('quantity');

        return redirect()->back()->with('success', 'Jumlah produk berhasil ditambahkan.');
    }

    public function remove($id)
    {
        $cart = $this->findOwnCart($id);

        if ($cart->quantity <= 1) {
            return redirect()->back()->with('error', 'Produk tidak bisa dikurangi lagi. Minimum jumlah adalah 1.');
        }

        $cart->decrement('quantity');

        return redirect()->back()->with('success', 'Jumlah produk berhasil dikurangi.');
    }

    public function destroy($id)
    {
        $this->findOwnCart($id)->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus dari keranjang.');
    }

    /** Keranjang milik pengguna saat ini, hanya untuk produk yang masih ada. */
    private function userCarts()
    {
        return Cart::where('user_id', auth()->id())->whereHas('product');
    }

    /** Pelanggan hanya boleh mengubah keranjangnya sendiri (404 jika milik orang lain). */
    private function findOwnCart($id): Cart
    {
        return $this->userCarts()->with('product')->findOrFail($id);
    }
}
