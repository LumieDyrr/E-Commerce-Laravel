@extends('layouts.dashboard')

@section('title', 'Dashboard | Produk | E-Commerce')

@section('content')
{{-- Header --}}
<header class="bg-white">
  <div class="max-w-screen-xl px-4 py-8 mx-auto sm:px-6 sm:py-12 lg:px-8">
    <div class="flex flex-col items-start gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Produk</h1>

        <p class="mt-1.5 text-sm text-gray-500">
          Manajemen produk Anda dengan mudah dan efisien. Kelola semua informasi terkait produk dalam satu tempat.
        </p>
      </div>

      <div class="flex items-center gap-4">
        <a href="{{ route('dashboard.products.create') }}"
          class="inline-block px-5 py-3 text-sm font-medium text-white transition bg-indigo-600 rounded hover:bg-indigo-700 focus:outline-none focus:ring"
          type="button"
        >
          Tambah Produk
        </a>
      </div>
    </div>
  </div>
</header>

<section class="max-w-screen-xl px-4 py-8 mx-auto sm:px-6 sm:py-12 lg:px-8">
  <div class="border border-gray-200 rounded-lg">
    <div class="overflow-x-auto rounded-t-lg">
      <table class="min-w-full text-sm bg-white divide-y-2 divide-gray-200">
        <thead class="ltr:text-left rtl:text-right">
          <tr>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">No</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Nama Produk</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Deskripsi</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Harga</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Kategori</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Jumlah Stok</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Diskon</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Gambar</th>
            <th class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">Aksi</th> <!-- Kolom Aksi -->
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
          @foreach ($products as $product)
          <tr>
            <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">{{ $loop->iteration }}</td>
            <td class="px-4 py-2 font-medium text-center text-gray-900 whitespace-nowrap">{{ $product->name }}</td>
            <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">{{ $product->description }}</td>
            <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">Rp{{ number_format($product->price, 0, ',', '.') }}</td>
            <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">{{ $product->category->name }}</td>
            <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">{{ $product->stock_quantity }}</td>
            <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">{{ number_format($product->discount, 2) }}%</td>
            <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">
              <img src="{{ asset('./storage/products/' .$product->image) }}" alt="{{ $product->name }}" class="object-cover w-12 h-12 rounded-lg"/>
            </td>
            <td class="text-center text-gray-700 whitespace">
              <div class="items-center justify-center lg:flex">
                <!-- Tombol Edit dengan Ikon -->
                <a href="{{ route('dashboard.products.edit', $product->slug) }}" class="items-center inline-block p-2 bg-blue-600 rounded-t-md text-blue-50 lg:rounded-tr-none lg:rounded-l-md hover:bg-blue-800">
                  <i class="text-xl bx bx-edit-alt"></i>
                </a>
              
                <!-- Tombol Hapus dengan Ikon -->
                <form action="{{ route('dashboard.products.destroy', $product->id) }}" method="POST" class="inline-block">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="p-2 bg-red-600 hover:bg-red-800 rounded-b-md lg:rounded-bl-none lg:rounded-r-md text-red-50" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                    <i class="text-xl bx bx-trash"></i>
                  </button>
                </form>
              </div>
            </td>            
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center px-4 py-2 border-t border-gray-200 rounded-b-lg">
      {{ $products->links('vendor.pagination.custom', ['item' => $products]) }}
    </div>
  </div>
</section>

@endsection
