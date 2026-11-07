@extends('layouts.dashboard')

@section('title', 'Dashboard | Pelanggan | E-Commerce')

@section('content')
{{-- Header --}}
<header class="bg-white">
  <div class="max-w-screen-xl px-4 py-8 mx-auto sm:px-6 sm:py-12 lg:px-8">
    <div class="flex flex-col items-start gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Langganan</h1>

        <p class="mt-1.5 text-sm text-gray-500">
          Manajemen data langganan dengan mudah. Kelola informasi terkait langganan Anda dalam satu tempat.
        </p>
      
      </div>
<!-- 
      <div class="flex items-center gap-4">
        <a href="#"
          class="inline-block px-5 py-3 text-sm font-medium text-white transition bg-indigo-600 rounded hover:bg-indigo-700 focus:outline-none focus:ring"
          type="button"
        >
          Tambah Pelanggan
        </a>
      </div> -->
    </div>
  </div>
</header>

<section class="max-w-screen-xl px-4 py-8 mx-auto sm:px-6 sm:py-12 lg:px-8">
  <div class="border border-gray-200 rounded-lg">
    <div class="overflow-x-auto rounded-t-lg">
      <table class="min-w-full text-sm bg-white divide-y-2 divide-gray-200">
        <thead class="ltr:text-left rtl:text-right">
          <tr>
            <th class="px-6 py-4 font-medium text-center text-gray-900 whitespace-nowrap">No</th>
            <th class="px-6 py-4 font-medium text-center text-gray-900 whitespace-nowrap">Nama Pelanggan</th>
            <th class="px-6 py-4 font-medium text-center text-gray-900 whitespace-nowrap">Email</th>
            <th class="px-6 py-4 font-medium text-center text-gray-900 whitespace-nowrap">Bukti Pembayaran</th>
            <th class="px-6 py-4 font-medium text-center text-gray-900 whitespace-nowrap">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
          @forelse ( $pendingOrders as $order )
          <tr>
            <td class="px-6 py-4 text-center text-gray-700 whitespace-nowrap">{{ $pendingOrders->firstItem() + $loop->index }}</td>
            <td class="px-6 py-4 font-medium text-center text-gray-900 whitespace-nowrap">
              {{ $order->user->name }}
            </td>
            <td class="px-6 py-4 text-center text-gray-700 whitespace-nowrap">
              {{ $order->user->email }}
            </td>
            <td class="px-6 py-4 text-center text-gray-700 whitespace-nowrap">
              @if ($order->payment && $order->payment->receipt_image)
                <a href="{{ route('payments.receipt', $order->payment->id) }}" 
                  class="text-blue-500 hover:underline" 
                  target="_blank">
                  Lihat Bukti Pembayaran
                </a>
              @else
                  <span class="text-gray-500">Tidak ada bukti pembayaran</span>
              @endif
            </td>          
            <td class="text-center text-gray-700 whitespace-nowrap">
              <div class="items-center justify-center lg:flex">
                @if($order->status === 'pending')
                  <form action="{{ route('dashboard.subscriptions.approve', $order->id) }}" method="POST">
                    @csrf
                    <button type="" class="items-center inline-block p-2 bg-blue-600 rounded-t-md text-blue-50 lg:rounded-tr-none lg:rounded-l-md hover:bg-blue-800">
                      <i class="text-lg bx bx-check" style="color:#ffffff"></i>
                    </button>
                  </form>
                  <form action="{{ route('dashboard.subscriptions.reject', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 bg-red-600 hover:bg-red-800 rounded-b-md lg:rounded-bl-none lg:rounded-r-md text-red-50">
                      <i class="text-lg bx bx-x" style="color:#ffffff"></i>
                    </button>
                  </form>
                @elseif ($order->status === 'completed')
                  <div>
                    Disetujui
                  </div>
                @elseif ($order->status === 'rejected')
                  <div>
                    DItolak
                  </div>
                @endif
              </div>
            </td>
            
          </tr>   
          @empty
          <tr>
              <td colspan="5" class="py-4 text-center">
                  <div class="flex flex-col items-center justify-center">
                      <svg class="w-16 h-16 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <circle cx="12" cy="12" r="10"></circle>
                          <line x1="12" y1="8" x2="12" y2="12"></line>
                          <line x1="12" y1="16" x2="12" y2="16"></line>
                      </svg>
                      <p class="mt-2 text-gray-500">Belum ada pelanggan yang melakukan pemesanan saat ini.</p>
                  </div>
              </td>
          </tr>
          @endempty

        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center px-4 py-2 border-t border-gray-200 rounded-b-lg">
      {{ $pendingOrders->links('vendor.pagination.custom', ['item' => $pendingOrders]) }}
    </div>
  </div>
</section>

@endsection
