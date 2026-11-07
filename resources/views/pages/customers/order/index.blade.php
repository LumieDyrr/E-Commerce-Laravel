@extends('layouts.app')

@section('title', 'Pesanan | E-Commerce')

@section('content')
<section
  class="overflow-hidden bg-[url(https://images.unsplash.com/photo-1580828343064-fde4fc206bc6?q=80&w=2071&auto=format&fit=crop)] bg-cover bg-center bg-no-repeat pt-16"
>
  <div class="p-8 bg-black/50 md:p-12 lg:px-16 lg:py-24">
    <div class="text-center sm:text-left">
      <h2 class="text-2xl font-bold text-white sm:text-3xl md:text-5xl">Riwayat Pemesanan Anda</h2>

      <p class="hidden max-w-lg text-white/90 md:mt-6 md:block md:text-lg md:leading-relaxed">
        Lihat dan kelola status pesanan Anda, dari proses pembayaran hingga pengiriman. Dapatkan informasi lengkap tentang setiap langkah perjalanan pesanan Anda.
      </p>
    </div>
  </div>
</section>

<div class="min-h-screen bg-white">
    <main class="pt-8 pb-14 sm:px-6 sm:pb-20 sm:pt-16 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <div class="px-4 sm:px-0">
          <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Riwayat Pesanan</h1>
          <p class="mt-2 text-sm text-gray-500">Cek status pesanan terbaru, kelola pengembalian, dan unduh faktur.</p>
        </div>
  
        <section aria-labelledby="recent-heading" class="mt-16">
          <h2 id="recent-heading" class="sr-only">Pesanan terbaru</h2>
  
          <div class="space-y-16 sm:space-y-24">
            @forelse ($orders as $order)
                <div>
                    <h3 class="sr-only">Pesanan dibuat pada <time datetime="{{ $order->created_at->toDateString() }}">{{ $order->created_at->format('d M Y') }}</time></h3>
          
                    <div class="px-4 py-6 bg-gray-50 sm:p-6 md:flex md:items-center md:justify-between md:space-x-6 lg:space-x-8">
                        <dl class="flex-auto space-y-4 text-sm text-gray-600 divide-y divide-gray-200 md:grid md:grid-cols-3 md:gap-x-6 md:space-y-0 md:divide-y-0 lg:w-1/2 lg:flex-none lg:gap-x-8">
                            <div class="flex justify-between md:block">
                                <dt class="font-medium text-gray-900">Nomor Pesanan</dt>
                                <dd class="md:mt-1">#{{ $order->id }}</dd>
                            </div>
                            <div class="flex justify-between pt-4 md:block md:pt-0">
                                <dt class="font-medium text-gray-900">Tanggal Pesanan</dt>
                                <dd class="md:mt-1">
                                    <time datetime="{{ $order->created_at->toDateString() }}">{{ $order->created_at->format('d M Y') }}</time>
                                </dd>
                            </div>
                            <div class="flex justify-between pt-4 font-medium text-gray-900 md:block md:pt-0">
                                <dt>Total</dt>
                                <dd class="md:mt-1">Rp{{ number_format($order->total_price, 0, ',', '.') }}</dd>
                            </div>
                        </dl>
                        <div class="mt-6 space-y-4 sm:flex sm:space-x-4 sm:space-y-0 md:mt-0">
                            <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 md:w-auto">
                                Lihat Faktur
                                <span class="sr-only">untuk pesanan #{{ $order->id }}</span>
                            </a>
                            @if ($order->payment)
                                <a href="{{ route('payments.receipt', $order->payment->id) }}" target="_blank" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 shadow-sm hover:bg-gray-50 md:w-auto">
                                    Bukti Pembayaran
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-between gap-4 font-medium sm:items-end mt-4 bg-gray-50 py-2 px-4">
                      @php
                          $statusText = [
                              'pending' => 'Pembayaran Sedang Diperiksa',
                              'delivered' => 'Pesanan Telah Diterima Kurir',
                              'arrived' => 'Pesanan Telah Sampai',
                              'completed' => 'Langganan Anda Telah Disetujui',
                              'rejected' => 'Pesanan Telah Ditolak',
                          ];
                      @endphp
                      <div>
                        <p>{{ $statusText[$order->status] ?? 'Status Tidak Diketahui' }}</p>
                        @if ($order->status === 'delivered')
                          <p class="text-sm">No Resi: {{ $order->shippings?->tracking_number ?? '-' }}</p>
                        @endif
                      </div>
                      @if ($order->status === 'delivered')
                        <form action="{{ route('orders.received', $order->id) }}" method="POST">
                          @csrf
                          @method('PUT')
                          <button type="submit" class="px-4 py-2 bg-slate-950 text-slate-50">
                            Pesanan Diterima
                          </button>
                        </form>
                      @endif
                  </div>
        
                    <div class="flow-root px-4 mt-6 sm:mt-10 sm:px-0">
                        <div class="-my-6 divide-y divide-gray-200 sm:-my-10">
                          @if(! $order->isSubscription())
                            @foreach ($order->items as $item)
                                <div class="flex py-6 sm:py-10">
                                    <div class="flex-1 min-w-0 lg:flex lg:flex-col">
                                        <div class="lg:flex-1">
                                            <div class="justify-between sm:flex">
                                                <div>
                                                    <h4 class="font-medium text-gray-900">{{ $item->product?->name ?? 'Produk sudah dihapus' }}</h4>
                                                    <p class="hidden mt-2 text-sm text-gray-500 sm:block">{{ $item->product?->description }}</p>
                                                </div>
                                                <p class="mt-1 font-medium text-gray-900 sm:ml-6 sm:mt-0">Rp{{ number_format($item->price, 0, ',', '.') }}</p>
                                            </div>
                                            @if ($item->product && ! $item->product->trashed())
                                            <div class="flex gap-4 mt-2 text-sm font-medium sm:mt-4">
                                                @if ($item->product->in_stock)
                                                    <a href="{{ route('checkout.buy_now', $item->product->slug) }}" class="text-indigo-600 hover:text-indigo-500">Beli Lagi</a>
                                                @endif
                                                @if ($order->status === 'arrived')
                                                    <a href="{{ route('products.show', $item->product->slug) }}#reviews-heading" class="text-indigo-600 hover:text-indigo-500">Beri Ulasan</a>
                                                @endif
                                            </div>
                                            @if ($item->product->in_stock)
                                            <form action="{{ route('cart.store', $item->product->slug) }}" method="POST" class="mt-4">
                                              @csrf
                                                <button type="submit"
                                                class="block w-full px-4 py-3 text-sm font-medium text-gray-900 transition bg-gray-100 hover:scale-105"
                                                >
                                                Tambah Ke Keranjang
                                              </button>
                                            </form>
                                            @endif
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 ml-4 sm:order-first sm:m-0 sm:mr-6">
                                        <img src="{{ $item->product?->image_url }}" alt="{{ $item->product?->name }}" class="object-cover object-center w-20 h-20 col-start-2 col-end-3 sm:col-start-1 sm:row-span-2 sm:row-start-1 sm:h-40 sm:w-40 lg:h-52 lg:w-52">
                                    </div>
                                </div>
                            @endforeach
                          @else
                              <div class="flex py-6 sm:py-10">
                                <div class="flex-1 min-w-0 lg:flex lg:flex-col">
                                    <div class="lg:flex-1">
                                        <div class="justify-between sm:flex">
                                            <div>
                                                <h4 class="font-medium text-gray-900">
                                                  Langganan Paket {{ $order->plan_label ?? '' }}
                                                </h4>
                                                <p class="hidden mt-2 text-sm text-gray-500 sm:block">
                                                  Paket Berlaku Selama Satu Bulan
                                                </p>
                                            </div>
                                            <p class="mt-1 font-medium text-gray-900 sm:ml-6 sm:mt-0">Rp{{ number_format($order->total_price, 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500">Belum ada pesanan.</p>
            @endforelse
        </div>
        @if ($orders->hasPages())
          <div class="mt-10">{{ $orders->links('vendor.pagination.custom', ['item' => $orders]) }}</div>
        @endif
        </section>
      </div>
    </main>
  </div>
@endsection
