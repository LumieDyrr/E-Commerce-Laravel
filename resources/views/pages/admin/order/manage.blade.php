@extends('layouts.dashboard')

@section('title', 'Dashboard | Pesanan | E-Commerce')

@section('content')
<div class="max-w-screen-xl px-4 py-8 mx-auto sm:px-6 sm:py-12 lg:px-8">
    <div class="px-4 sm:px-0">
        <h3 class="text-lg font-semibold leading-7 text-gray-900">Detail Pesanan</h3>
        <p class="mt-1 text-sm text-gray-500">Informasi pelanggan dan detail pesanan.</p>
    </div>
    <div class="mt-6 border-t border-gray-100">
        <dl class="divide-y divide-gray-100">
            <!-- Nama Pelanggan -->
            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium text-gray-900">Nama Pelanggan</dt>
                <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $order->user->name }}</dd>
            </div>

            <!-- Alamat Pelanggan -->
            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium text-gray-900">Alamat Pelanggan</dt>
                <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">
                    {{ $order->shipping_address }}
                </dd>
            </div>

            <!-- List Produk -->
            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium text-gray-900">Produk yang Dibeli</dt>
                <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">
                    <ul role="list" class="space-y-2">
                        @foreach ($order->items as $item)
                        <li class="flex justify-between">
                            <div class="flex flex-col gap-2">
                                <span>{{ $item->product?->name ?? 'Produk sudah dihapus' }} <span class="text-gray-400">&times; {{ $item->quantity }}</span></span>
                                <img src="{{ $item->product?->image_url }}" alt="{{ $item->product?->name }}" class="object-cover w-12 h-12 rounded-lg">
                            </div>
                            <span>Rp{{ number_format($item->total, 0, ',', '.') }}</span>
                        </li>
                        @endforeach
                        <li class="flex justify-between font-semibold">
                            <p>
                                Total
                                @if($order->items->sum('total') > $order->total_price)
                                <span class="text-xs text-gray-400">(Sudah Dipotong Diskon Member)</span>
                                @endif
                            </p>
                            <span>Rp{{ number_format($order->total_price, 0, ',', '.') }}</span>
                        </li>
                    </ul>
                </dd>
            </div>

            <!-- Bukti Pembayaran -->
            <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                <dt class="text-sm font-medium text-gray-900">Bukti Pembayaran</dt>
                <dd class="mt-1 text-sm text-indigo-600 sm:col-span-2 sm:mt-0">
                    @if ($order->payment)
                        <a href="{{ route('payments.receipt', $order->payment->id) }}" target="_blank" class="hover:underline">Lihat Bukti Pembayaran</a>
                    @else
                        <span class="text-gray-500">Tidak ada bukti pembayaran</span>
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    @if ($order->status === 'pending')
    <!-- Form Input Nomor Resi -->
    <div class="mt-6">
        <form action="{{ route('dashboard.orders.send', $order->id) }}" method="POST">
            @csrf
            <div class="mb-4 flex flex-col gap-4 lg:flex-row items-center">
                <label for="tracking_number" class="block text-sm font-medium text-gray-900">Nomor Resi : </label>
                <input type="text" name="tracking_number" id="tracking_number" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border px-4 py-2 max-w-2xl">
                @if($errors->has('tracking_number'))
                    <span class="mt-2 text-sm text-red-600"> {{ $errors->first('tracking_number') }}</span>
                @endif
            </div>
            <div class="flex justify-end space-x-4">
                <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Kirim No Resi
                </button>
            </div>
        </form>
    </div>
    <!-- Form Input Nomor Resi -->
    <div class="mt-6">
        <form action="{{ route('dashboard.orders.reject', $order->id) }}" method="POST">
            @csrf
            <div class="flex justify-end space-x-4">
                <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Tolak Pemesanan
                </button>
            </div>
        </form>
    </div>
    @else
    <p class="mt-6 text-sm text-gray-500">Pesanan ini sudah diproses (status: {{ $order->status }}).</p>
    @endif
</div>
@endsection
