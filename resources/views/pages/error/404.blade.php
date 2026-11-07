@extends('layouts.errors')

@section('title', 'Halaman tidak ditemukan | E-Commerce')

@section('content')
  <div class="flex h-screen flex-col bg-white">
    <img
      src="https://images.unsplash.com/photo-1558769132-cb1aea458c5e?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1548&q=80"
      alt=""
      class="h-64 w-full object-cover"
    />

    <div class="flex flex-1 items-center justify-center">
      <div class="mx-auto max-w-xl px-4 py-8 text-center">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-4xl">
          Halaman tidak ditemukan.
        </h1>
        
        <p class="mt-4 text-slate-500">
          Coba cari lagi, atau kembali ke beranda untuk memulai dari awal.
        </p>
        
        <a
          href="{{ route('home.index') }}"
          class="mt-6 inline-block bg-slate-950 px-5 py-3 text-sm font-medium text-white hover:bg-slate-800 focus:outline-none focus:ring duration-150"
        >
          Kembali Ke Beranda
        </a>
      </div>
    </div>
  </div>
@endsection
