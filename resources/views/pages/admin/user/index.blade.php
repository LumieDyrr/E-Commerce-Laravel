@extends('layouts.dashboard')

@section('title', 'Dashboard | Pengguna | E-Commerce')

@section('content')
{{-- Header --}}
<header class="bg-white">
  <div class="max-w-screen-xl px-4 py-8 mx-auto sm:px-6 sm:py-12 lg:px-8">
    <div class="flex flex-col items-start gap-4 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Pengguna</h1>

        <p class="mt-1.5 text-sm text-gray-500">
          Manajemen pengguna dan akun pelanggan dengan mudah. Kelola semua informasi terkait pengguna dalam satu tempat.
        </p>
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
            <th class="px-6 py-4 font-medium text-center text-gray-900 whitespace-nowrap">No</th>
            <th class="px-6 py-4 font-medium text-center text-gray-900 whitespace-nowrap">Nama Pengguna</th>
            <th class="px-6 py-4 font-medium text-center text-gray-900 whitespace-nowrap">Email</th>
            <th class="px-6 py-4 font-medium text-center text-gray-900 whitespace-nowrap">No. Telephone</th>
            <th class="px-6 py-4 font-medium text-center text-gray-900 whitespace-nowrap">Status Berlangganan</th>
            {{-- <th class="px-6 py-4 font-medium text-center text-gray-900 whitespace-nowrap">Aksi</th> --}}
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200">
          @forelse ($users as $user)
            <tr>
              <td class="px-6 py-4 text-center text-gray-700 whitespace-nowrap">{{ $loop->iteration }}</td>
              <td class="px-6 py-4 font-medium text-center text-gray-900 whitespace-nowrap">{{ $user->name }}</td>
              <td class="px-6 py-4 text-center text-gray-700 whitespace-nowrap">{{ $user->email }}</td>
              <td class="px-6 py-4 text-center text-gray-700 whitespace-nowrap">{{ $user->phone }}</td>
              <td class="px-6 py-4 text-center text-gray-700 whitespace-nowrap">
                {{ ($user->subscription_status == 1) ? 'Aktif' : 'Tidak Aktif' }}
              </td>
              {{-- <td class="text-center text-gray-700 whitespace-nowrap">
                <div class="items-center justify-center lg:flex">
                  <a href="#" class="items-center inline-block p-2 bg-blue-600 rounded-t-md text-blue-50 lg:rounded-tr-none lg:rounded-l-md hover:bg-blue-800">
                    <i class="text-lg bx bx-edit-alt"></i>  <!-- Ukuran ikon sedikit lebih kecil -->
                  </a>
                  <button type="button" class="p-2 bg-red-600 hover:bg-red-800 rounded-b-md lg:rounded-bl-none lg:rounded-r-md text-red-50">
                    <i class="text-lg bx bx-trash"></i>  <!-- Ukuran ikon sedikit lebih kecil -->
                  </button>
                </div>
              </td> --}}
            </tr>    
          @empty
            <tr>
              <td class="px-4 py-2 text-center text-gray-700 whitespace-nowrap">
                Belum ada data customer saat ini. 
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center px-4 py-2 border-t border-gray-200 rounded-b-lg">
      {{ $users->links('vendor.pagination.custom', ['item' => $users]) }}
    </div>
  </div>
</section>

@endsection
