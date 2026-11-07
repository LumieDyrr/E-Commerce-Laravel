@extends('layouts.app')

@section('title', 'Produk | E-Commerce')

@section('content')
  <main>
    @include('components.customers.produk.hero')
  </main>

  <section>
    @include('components.customers.produk.collection', ['products' => $products])
  </section>
@endsection
