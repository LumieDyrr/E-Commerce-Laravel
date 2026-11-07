@extends('layouts.dashboard')

@section('title', 'Dashboard | Pesanan | E-Commerce')

@section('content')
{{-- Header --}}
<header class="bg-white">
  <div class="max-w-screen-xl px-4 py-8 mx-auto sm:px-6 sm:py-12 lg:px-8">
    <div class="flex flex-col items-start gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Pesanan Produk</h1>

        <p class="mt-1.5 text-sm text-gray-500">
          Manajemen pemesanan produk  dengan mudah dan efisien. Kelola semua informasi terkait produk dalam satu tempat.
        </p>
      </div>
    </div>
  </div>
</header>

<section class="max-w-screen-xl px-4 py-8 mx-auto sm:px-6 sm:py-12 lg:px-8">
  <div class="mb-4">
    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Pesanan Pending</h1>
  </div>

  <div class="border border-gray-200 rounded-lg">
    <div class="overflow-x-auto rounded-t-lg">
      <table class="min-w-full text-sm bg-white divide-y-2 divide-gray-200">
        <thead class="ltr:text-left rtl:text-right">
          <tr>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">No</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Nama Pelanggan</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">No. Order</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Harga</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Status</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
          @forelse ( $pendingOrders as $order )
            <tr>
              <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">
                {{ $loop->iteration }}
              </td>
              <td class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">
                {{ $order->user->name }}
              </td>
              <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">
                #{{ $order->id }}
              </td>
              <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">
                {{ number_format($order->total_price, 0, ',', '.') }}
              </td>
              <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">
                @php
                    $statusText = [
                        'pending' => 'Segera verifikasi pembayaran pelanggan.',
                    ];
                @endphp
                {{ $statusText[$order->status] ?? 'Tindakan untuk status pesanan ini belum ditentukan.' }}
              </td>
              <td class="text-center text-gray-700 whitespace-nowrap">
                <div class="items-center justify-center lg:flex py-2">
                  <a href="{{ route('dashboard.orders.manage', $order->id) }}" class="items-center inline-block p-2 bg-blue-600 text-blue-50 rounded-md hover:bg-blue-800">
                    <i class='bx bx-package text-xl'></i>
                  </a>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td>
                Kosong
              </td>
            </tr>
          @endforelse
          <!-- Tambahkan lebih banyak contoh produk di sini jika diperlukan -->
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center px-4 py-2 border-t border-gray-200 rounded-b-lg">
      {{ $pendingOrders->links('vendor.pagination.custom', ['item' => $pendingOrders]) }}
    </div>
  </div>
</section>

<section class="max-w-screen-xl px-4 py-8 mx-auto sm:px-6 sm:py-12 lg:px-8">
  <div class="mb-4">
    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Semua Pesanan</h1>
  </div>
  <div class="border border-gray-200 rounded-lg">
    <div class="overflow-x-auto rounded-t-lg">
      <table class="min-w-full text-sm bg-white divide-y-2 divide-gray-200">
        <thead class="ltr:text-left rtl:text-right">
          <tr>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">No</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Nama Pelanggan</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Harga</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Status</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
          @forelse ( $orders as $order )
            <tr>
              <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">
                {{ $loop->iteration }}
              </td>
              <td class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">
                {{ $order->user->name }}
              </td>
              <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">
                {{ number_format($order->total_price, 0, ',', '.') }}
              </td>
              <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">
                @php
                    $statusText = [
                        'delivered' => 'Pesanan telah diterima oleh kurir untuk pengantaran.',
                        'arrived' => 'Pesanan telah sampai di alamat tujuan.',
                        'completed' => 'Langganan Anda telah disetujui.',
                        'rejected' => 'Pesanan telah ditolak.',
                    ];
                @endphp
                {{ $statusText[$order->status] ?? 'Tindakan untuk status pesanan ini belum ditentukan.' }}
              </td>
            </tr>
          @empty
            <tr>
              <td>
                Kosong
              </td>
            </tr>
          @endforelse
          <!-- Tambahkan lebih banyak contoh produk di sini jika diperlukan -->
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center px-4 py-2 border-t border-gray-200 rounded-b-lg">
      {{ $orders->links('vendor.pagination.custom', ['item' => $orders]) }}
    </div>
  </div>
</section>

@endsection