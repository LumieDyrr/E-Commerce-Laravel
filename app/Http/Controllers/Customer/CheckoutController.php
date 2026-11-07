<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\OrderException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
use App\Models\Product;
use App\Services\OrderService;

class CheckoutController extends Controller
{
    public function index(OrderService $orders)
    {
        $carts = Cart::where('user_id', auth()->id())->whereHas('product')->with('product')->get();

        if ($carts->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong.');
        }

        $subtotal = $carts->sum(fn ($cart) => $cart->product->final_price * $cart->quantity);
        $summary = $orders->quote($subtotal, auth()->user());

        return view('pages.customers.checkout.index', compact('carts', 'summary'));
    }

    public function store(CheckoutRequest $request, OrderService $orders)
    {
        $carts = Cart::where('user_id', auth()->id())->whereHas('product')->get();

        if ($carts->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong.');
        }

        $lines = $carts->pluck('quantity', 'product_id')->map(fn ($q) => (int) $q)->all();

        try {
            $orders->placeProductOrder(auth()->user(), $lines, $request->fullAddress(), $request->file('bukti-pembayaran'));
        } catch (OrderException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        Cart::where('user_id', auth()->id())->delete();

        return redirect()->route('orders.index')->with('success', 'Pembayaran berhasil dikirimkan.');
    }

    public function buy_now(string $slug, OrderService $orders)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        if (! $product->in_stock) {
            return redirect()->back()->with('error', 'Stok produk habis.');
        }

        $summary = $orders->quote($product->final_price, auth()->user());

        return view('pages.customers.checkout.buy-now', compact('product', 'summary'));
    }

    public function buy_now_store(CheckoutRequest $request, string $slug, OrderService $orders)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        try {
            $orders->placeProductOrder(auth()->user(), [$product->id => 1], $request->fullAddress(), $request->file('bukti-pembayaran'));
        } catch (OrderException $e) {
            return redirect()->route('products.index')->with('error', $e->getMessage());
        }

        return redirect()->route('orders.index')->with('success', 'Pembayaran berhasil dikirimkan.');
    }
}
