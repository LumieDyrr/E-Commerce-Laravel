@extends('layouts.app')

@section('title', $product->name . ' | E-Commerce')

@section('content')
<div class="pt-16 bg-white">
  <div class="max-w-screen-xl px-4 py-10 mx-auto sm:px-6 lg:px-8">
    <nav class="text-sm text-gray-500" aria-label="Breadcrumb">
      <a href="{{ route('products.index') }}" class="hover:underline">Produk</a>
      @if ($product->category)
        <span class="mx-1">/</span>
        <a href="{{ route('products.index', ['kategori' => $product->category_id]) }}" class="hover:underline">{{ $product->category->name }}</a>
      @endif
    </nav>

    <div class="grid gap-10 mt-6 lg:grid-cols-2">
      <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="object-cover w-full bg-gray-100 aspect-square">

      <div>
        <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>

        <div class="flex items-center gap-2 mt-2 text-sm text-gray-600">
          @if ($rating)
            <span class="text-amber-500">{{ str_repeat('★', (int) round($rating)) }}{{ str_repeat('☆', 5 - (int) round($rating)) }}</span>
            <span>{{ number_format($rating, 1) }} ({{ $reviews->total() }} ulasan)</span>
          @else
            <span>Belum ada ulasan</span>
          @endif
        </div>

        <p class="mt-6 text-2xl font-semibold text-gray-900">
          Rp{{ number_format($product->final_price, 0, ',', '.') }}
          @if ($product->discount > 0)
            <span class="ml-2 text-base font-normal text-gray-400 line-through">Rp{{ number_format($product->price, 0, ',', '.') }}</span>
          @endif
        </p>

        @auth
          @if (auth()->user()->memberDiscountPercent() > 0)
            <p class="mt-1 text-sm text-green-700">Diskon member {{ auth()->user()->memberDiscountPercent() }}% diterapkan saat pembayaran.</p>
          @endif
        @endauth

        <p class="mt-4 text-sm {{ $product->in_stock ? 'text-green-700' : 'text-red-600' }}">
          {{ $product->in_stock ? 'Stok tersedia: ' . $product->stock_quantity : 'Stok habis' }}
        </p>

        <p class="mt-6 leading-relaxed text-gray-700 whitespace-pre-line">{{ $product->description }}</p>

        <div class="flex flex-col gap-3 mt-8 sm:flex-row">
          @if ($product->in_stock)
            <a href="{{ route('checkout.buy_now', $product->slug) }}" class="px-6 py-3 text-sm font-medium text-center text-white bg-gray-900 hover:bg-gray-700">Beli Sekarang</a>
            <form action="{{ route('cart.store', $product->slug) }}" method="POST">
              @csrf
              <button type="submit" class="w-full px-6 py-3 text-sm font-medium text-gray-900 bg-gray-100 hover:bg-gray-200">Tambah Ke Keranjang</button>
            </form>
          @else
            <span class="px-6 py-3 text-sm font-medium text-center text-gray-500 bg-gray-100 cursor-not-allowed">Stok Habis</span>
          @endif
        </div>
      </div>
    </div>

    {{-- Ulasan --}}
    <section class="mt-16" aria-labelledby="reviews-heading">
      <h2 id="reviews-heading" class="text-xl font-bold text-gray-900">Ulasan Pembeli</h2>

      @if ($canReview)
        <form action="{{ route('reviews.store', $product->slug) }}" method="POST" class="p-4 mt-4 space-y-3 bg-gray-50">
          @csrf
          <p class="text-sm font-medium text-gray-900">{{ $myReview ? 'Ubah ulasan Anda' : 'Tulis ulasan Anda' }}</p>
          <select name="rating" class="h-10 px-3 bg-white border border-gray-300 sm:text-sm" required>
            <option value="">Pilih rating</option>
            @for ($i = 5; $i >= 1; $i--)
              <option value="{{ $i }}" @selected((int) old('rating', $myReview->rating ?? 0) === $i)>{{ $i }} - {{ str_repeat('★', $i) }}</option>
            @endfor
          </select>
          @error('rating') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
          <textarea name="comment" rows="3" maxlength="1000" placeholder="Bagikan pengalaman Anda (opsional)"
            class="block w-full px-4 py-2 bg-white border border-gray-300 sm:text-sm">{{ old('comment', $myReview->comment ?? '') }}</textarea>
          @error('comment') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
          <button type="submit" class="px-5 py-2 text-sm font-medium text-white bg-gray-900 hover:bg-gray-700">Kirim Ulasan</button>
        </form>
      @endif

      <div class="mt-4 divide-y divide-gray-100">
        @forelse ($reviews as $review)
          <article class="py-4">
            <div class="flex items-center justify-between">
              <p class="font-medium text-gray-900">{{ $review->user->name ?? 'Pengguna' }}</p>
              <time class="text-xs text-gray-400" datetime="{{ $review->created_at->toDateString() }}">{{ $review->created_at->format('d M Y') }}</time>
            </div>
            <p class="text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</p>
            @if ($review->comment)
              <p class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $review->comment }}</p>
            @endif
          </article>
        @empty
          <p class="py-4 text-sm text-gray-500">Belum ada ulasan untuk produk ini.</p>
        @endforelse
      </div>

      @if ($reviews->hasPages())
        <div class="mt-4">{{ $reviews->links('vendor.pagination.custom', ['item' => $reviews]) }}</div>
      @endif
    </section>

    @if ($related->isNotEmpty())
      <section class="mt-16" aria-labelledby="related-heading">
        <h2 id="related-heading" class="text-xl font-bold text-gray-900">Produk Serupa</h2>
        <ul class="grid gap-4 mt-4 sm:grid-cols-2 lg:grid-cols-4">
          @foreach ($related as $item)
            <li>
              <a href="{{ route('products.show', $item->slug) }}" class="block group">
                <img src="{{ $item->image_url }}" alt="{{ $item->name }}" loading="lazy" class="object-cover w-full h-56 transition group-hover:opacity-90">
                <p class="mt-2 font-medium text-gray-900">{{ $item->name }}</p>
                <p class="text-sm text-gray-600">Rp{{ number_format($item->final_price, 0, ',', '.') }}</p>
              </a>
            </li>
          @endforeach
        </ul>
      </section>
    @endif
  </div>
</div>
@endsection
