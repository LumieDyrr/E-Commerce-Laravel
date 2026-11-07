@extends('layouts.dashboard')

@section('title', 'Dashboard | E-Commerce')

@section('content')
<div class="max-w-screen-xl px-6 py-12 mx-auto sm:px-8 lg:px-12">
  {{-- Hero Banner --}}
  <div class="relative w-full h-56 bg-gradient-to-r from-indigo-500 to-indigo-800 rounded-lg shadow-lg">
    <div class="absolute inset-0 flex flex-col justify-center items-center text-center text-white p-6">
      <h2 class="text-4xl font-bold sm:text-5xl">Dashboard</h2>
      <p class="mt-4 text-lg sm:text-xl">Selamat datang di halaman dashboard! Kelola bisnis Anda dengan mudah dan pantau performa secara menyeluruh.</p>
    </div>
  </div>

  {{-- Ringkasan --}}
  <div class="mt-12">
    <h3 class="text-3xl font-semibold text-gray-800">Ringkasan</h3>
    <div class="grid grid-cols-1 gap-4 mt-6 sm:grid-cols-2 lg:grid-cols-3">
      <div class="p-6 bg-white border border-gray-200 rounded-lg">
        <p class="text-sm text-gray-500">Pendapatan (pesanan disetujui)</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900">Rp{{ number_format($stats['revenue'], 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-gray-400">Bulan ini: Rp{{ number_format($stats['revenue_month'], 0, ',', '.') }}</p>
      </div>
      <a href="{{ route('dashboard.orders.index') }}" class="p-6 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        <p class="text-sm text-gray-500">Pesanan produk menunggu</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $stats['pending_orders'] }}</p>
      </a>
      <a href="{{ route('dashboard.subscriptions.index') }}" class="p-6 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        <p class="text-sm text-gray-500">Langganan menunggu</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $stats['pending_subscriptions'] }}</p>
      </a>
      <a href="{{ route('dashboard.users.index') }}" class="p-6 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        <p class="text-sm text-gray-500">Pelanggan</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $stats['customers'] }}</p>
      </a>
      <a href="{{ route('dashboard.products.index') }}" class="p-6 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
        <p class="text-sm text-gray-500">Produk</p>
        <p class="mt-2 text-2xl font-semibold text-gray-900">{{ $stats['products'] }}</p>
      </a>
    </div>

    <div class="grid gap-6 mt-8 lg:grid-cols-2">
      <div class="bg-white border border-gray-200 rounded-lg">
        <h4 class="px-6 py-4 font-medium text-gray-900 border-b border-gray-200">Stok Menipis (&le; {{ $threshold }})</h4>
        <ul class="divide-y divide-gray-100">
          @forelse ($lowStock as $item)
            <li class="flex items-center justify-between px-6 py-3 text-sm">
              <a href="{{ route('dashboard.products.edit', $item->slug) }}" class="text-gray-800 hover:underline">{{ $item->name }}</a>
              <span class="{{ $item->stock_quantity === 0 ? 'text-red-600 font-semibold' : 'text-amber-600' }}">{{ $item->stock_quantity }}</span>
            </li>
          @empty
            <li class="px-6 py-4 text-sm text-gray-500">Semua stok aman.</li>
          @endforelse
        </ul>
      </div>

      <div class="bg-white border border-gray-200 rounded-lg">
        <h4 class="px-6 py-4 font-medium text-gray-900 border-b border-gray-200">Pesanan Terbaru</h4>
        <ul class="divide-y divide-gray-100">
          @forelse ($recentOrders as $order)
            <li class="flex items-center justify-between px-6 py-3 text-sm">
              <span class="text-gray-800">#{{ $order->id }} &middot; {{ $order->user->name ?? '-' }}
                <span class="text-xs text-gray-400">({{ $order->isSubscription() ? 'langganan' : 'produk' }})</span></span>
              <span class="text-gray-600">Rp{{ number_format($order->total_price, 0, ',', '.') }} &middot; {{ $order->status }}</span>
            </li>
          @empty
            <li class="px-6 py-4 text-sm text-gray-500">Belum ada pesanan.</li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>

  {{-- Fitur --}}
<div class="mt-16">
  <h3 class="text-3xl font-semibold text-gray-800">Fitur</h3>
  <p class="mt-2 text-gray-600">Akses cepat untuk mengelola berbagai aspek bisnis Anda.</p>
  <div class="grid grid-cols-1 gap-6 mt-8 sm:grid-cols-2 lg:grid-cols-4">
    <a href="{{ route('dashboard.orders.index') }}" class="flex items-center justify-center px-6 py-8 text-center rounded-lg shadow-xl bg-blue-600 text-white hover:opacity-90">
      <div>
        <i class="text-5xl bx bx-cart"></i>
        <p class="mt-4 text-lg font-medium">Kelola Pesanan</p>
      </div>
    </a>
    <a href="{{ route('dashboard.products.index') }}" class="flex items-center justify-center px-6 py-8 text-center rounded-lg shadow-xl bg-blue-600 text-white hover:opacity-90">
      <div>
        <i class="text-5xl bx bx-package"></i>
        <p class="mt-4 text-lg font-medium">Kelola Produk</p>
      </div>
    </a>
    <a href="{{ route('dashboard.users.index') }}" class="flex items-center justify-center px-6 py-8 text-center rounded-lg shadow-xl bg-blue-600 text-white hover:opacity-90">
      <div>
        <i class="text-5xl bx bx-user"></i>
        <p class="mt-4 text-lg font-medium">Kelola Pelanggan</p>
      </div>
    </a>
    <a href="{{ route('dashboard.subscriptions.index') }}" class="flex items-center justify-center px-6 py-8 text-center rounded-lg shadow-xl bg-blue-600 text-white hover:opacity-90">
      <div>
        <i class="text-5xl bx bx-heart"></i>
        <p class="mt-4 text-lg font-medium">Kelola Langganan</p>
      </div>
    </a>
  </div>
</div>

  
</div>
@endsection
