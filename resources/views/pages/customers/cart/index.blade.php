@extends('layouts.app')

@section('title', 'Keranjang | E-Commerce')

@section('content')
    <div class="min-h-screen bg-white">
        <div class="max-w-2xl px-4 py-16 mx-auto sm:px-6 sm:py-24 lg:px-0">
            <h1 class="text-3xl font-bold tracking-tight text-center text-gray-900 sm:text-4xl">Keranjang Belanja</h1>

            <section aria-labelledby="cart-heading" class="mt-12">
                <h2 id="cart-heading" class="sr-only">Items in your shopping cart</h2>

                <ul role="list" class="border-t border-b border-gray-200 divide-y divide-gray-200">
                    @forelse ($carts as $cart)
                        <li class="flex py-6">
                            <div class="flex-shrink-0">
                                <img src="{{ $cart->product->image_url }}" alt="Gambar Produk"
                                    class="object-cover object-center w-24 h-24 sm:h-32 sm:w-32">
                            </div>

                            <div class="flex flex-col flex-1 ml-4 sm:ml-6">
                                <div>
                                    <div class="flex justify-between">
                                        <h4 class="text-sm">
                                            <a href="{{ route('products.show', $cart->product->slug) }}"
                                                class="font-medium text-gray-700 hover:text-gray-800">{{ $cart->product->name }}</a>
                                        </h4>
                                        <p class="ml-4 text-sm font-medium text-gray-900">
                                            @if ($cart->product->discount > 0)
                                                Rp{{ number_format($cart->product->price - ($cart->product->price * $cart->product->discount) / 100, 0, ',', '.') }}
                                                <span class="text-gray-400 line-through">
                                                    Rp{{ number_format($cart->product->price, 0, ',', '.') }}
                                                </span>
                                            @else
                                                Rp{{ number_format($cart->product->price, 0, ',', '.') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex justify-end gap-4 mt-2">
                                        <form action="{{ route('cart.remove', $cart->id) }}" method="POST">
                                            @csrf
                                            <button type="submit">
                                                -
                                            </button>
                                        </form>
                                        <p>{{ $cart->quantity }}</p>
                                        <form action="{{ route('cart.add', $cart->id) }}" method="POST">
                                            @csrf
                                            <button type="submit">
                                                +
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="flex items-end justify-between flex-1 mt-4">
                                    <p class="flex items-center space-x-2 text-sm text-gray-700">
                                        <svg class="flex-shrink-0 w-5 h-5 text-green-500" viewBox="0 0 20 20"
                                            fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ $cart->quantity > $cart->product->stock_quantity ? 'Stok tersisa ' . $cart->product->stock_quantity : 'Tersedia' }}</span>
                                    </p>
                                    <div class="ml-4">
                                        <form action="{{ route('cart.destroy', $cart->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <p class="py-6 text-center text-gray-700">Keranjang Kosong</p>
                    @endforelse
                </ul>
            </section>

            <!-- Order summary -->
            @if ($carts->isNotEmpty())
                <section aria-labelledby="summary-heading" class="mt-10">
                    <h2 id="summary-heading" class="sr-only">Ringkasan Pesanan</h2>

                    <div>
                        <dl class="space-y-4">
                            <div class="flex items-center justify-between">
                                <dt class="text-base text-gray-600">Subtotal</dt>
                                <dd class="ml-4 text-base font-medium text-gray-900">Rp{{ number_format($summary['subtotal'], 0, ',', '.') }}</dd>
                            </div>
                            @if ($summary['discount_percent'] > 0)
                                <div class="flex items-center justify-between">
                                    <dt class="text-base text-gray-600">Diskon Member ({{ $summary['discount_percent'] }}%)</dt>
                                    <dd class="ml-4 text-base font-medium text-gray-900">-Rp{{ number_format($summary['discount_amount'], 0, ',', '.') }}</dd>
                                </div>
                            @endif
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <dt class="text-base font-medium text-gray-900">Total</dt>
                                <dd class="ml-4 text-base font-semibold text-gray-900">Rp{{ number_format($summary['total'], 0, ',', '.') }}</dd>
                            </div>
                        </dl>
                        <p class="mt-1 text-sm text-gray-500">Pembayaran dilakukan via transfer/QR, lalu unggah bukti pembayaran di halaman berikutnya.</p>
                    </div>

                    <div class="mt-10 flex justify-center">
                        <a href="{{ route('checkout.index') }}"
                            class="w-full px-4 py-3 text-base font-medium text-white border border-transparent shadow-sm bg-slate-950 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 focus:ring-offset-gray-50 text-center">
                            Checkout
                        </a>
                    </div>

                    <div class="mt-6 text-sm text-center">
                        <p>
                            atau
                            <a href="{{ route('products.index') }}"
                                class="font-medium text-indigo-600 hover:text-indigo-500">
                                Lanjutkan Belanja
                                <span aria-hidden="true"> &rarr;</span>
                            </a>
                        </p>
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection
