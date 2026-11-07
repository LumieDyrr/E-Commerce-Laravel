<section>
    <div class="max-w-screen-xl px-4 py-8 mx-auto sm:px-6 sm:py-12 lg:px-8">
        <header>
            <h2 class="text-xl font-bold text-gray-900 sm:text-3xl">Koleksi Produk</h2>
            <p class="max-w-md mt-4 text-gray-500">
                Temukan berbagai produk pilihan yang sesuai dengan kebutuhan dan gaya Anda.
            </p>
        </header>

        {{-- Pencarian, filter kategori, dan urutan --}}
        <form action="{{ route('products.index') }}" method="GET" class="grid gap-3 mt-8 sm:grid-cols-2 lg:grid-cols-[1fr_14rem_12rem_auto]">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari produk..."
                class="w-full h-11 px-4 bg-white border border-gray-300 sm:text-sm focus:border-gray-500 focus:ring-gray-500">

            <select name="kategori" class="w-full h-11 px-3 bg-white border border-gray-300 sm:text-sm focus:border-gray-500 focus:ring-gray-500">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) request('kategori') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>

            <select name="urut" class="w-full h-11 px-3 bg-white border border-gray-300 sm:text-sm focus:border-gray-500 focus:ring-gray-500">
                @foreach (['terbaru' => 'Terbaru', 'termurah' => 'Harga Terendah', 'termahal' => 'Harga Tertinggi', 'nama' => 'Nama A-Z'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('urut', 'terbaru') === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button type="submit" class="h-11 px-5 text-sm font-medium text-white bg-gray-900 hover:bg-gray-700">Terapkan</button>
                @if (request()->hasAny(['q', 'kategori', 'urut']))
                    <a href="{{ route('products.index') }}" class="inline-flex items-center h-11 px-4 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200">Reset</a>
                @endif
            </div>
        </form>

        <div class="mt-6">
            <p class="text-sm text-gray-500">
                @if ($products->total() > 0)
                    Menampilkan <span>{{ $products->firstItem() }}</span> hingga <span>{{ $products->lastItem() }}</span>
                    dari <span>{{ $products->total() }}</span> produk
                @else
                    Tidak ada produk yang cocok.
                @endif
            </p>
        </div>

        <ul class="grid gap-4 mt-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($products as $product)
                <li class="flex flex-col bg-white border border-gray-100">
                    <a href="{{ route('products.show', $product->slug) }}" class="relative block overflow-hidden group">
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy"
                            class="object-cover w-full h-64 transition duration-500 group-hover:scale-105 sm:h-72 {{ $product->in_stock ? '' : 'opacity-50' }}" />

                        @if (! $product->in_stock)
                            <span class="absolute top-2 left-2 px-2 py-1 text-xs font-medium text-white bg-gray-900">Stok Habis</span>
                        @elseif ($product->discount > 0)
                            <span class="absolute top-2 left-2 px-2 py-1 text-xs font-medium text-white bg-red-600">-{{ rtrim(rtrim(number_format($product->discount, 2, '.', ''), '0'), '.') }}%</span>
                        @endif
                    </a>

                    <div class="flex flex-col flex-1 p-6">
                        <p class="text-gray-700">
                            Rp{{ number_format($product->final_price, 0, ',', '.') }}
                            @if ($product->discount > 0)
                                <span class="text-gray-400 line-through">Rp{{ number_format($product->price, 0, ',', '.') }}</span>
                            @endif
                        </p>

                        <a href="{{ route('products.show', $product->slug) }}" class="mt-1.5 text-lg font-medium text-gray-900 hover:underline">{{ $product->name }}</a>

                        @if ($product->category)
                            <p class="mt-1 text-xs text-gray-400">{{ $product->category->name }}</p>
                        @endif

                        <p class="mt-1.5 line-clamp-3 text-gray-700">{{ $product->description }}</p>

                        <div class="flex flex-col-reverse gap-3 mt-auto pt-4">
                            @if ($product->in_stock)
                                <form action="{{ route('cart.store', $product->slug) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-3 text-sm font-medium text-gray-900 transition bg-gray-100 hover:scale-105">
                                        Tambah Ke Keranjang
                                    </button>
                                </form>
                                <a href="{{ route('checkout.buy_now', $product->slug) }}"
                                    class="block w-full px-4 py-3 text-sm font-medium text-center text-white transition bg-gray-900 hover:scale-105">
                                    Beli Sekarang
                                </a>
                            @else
                                <span class="block w-full px-4 py-3 text-sm font-medium text-center text-gray-500 bg-gray-100 cursor-not-allowed">Stok Habis</span>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        @if ($products->hasPages())
            <div class="mt-8">
                {{ $products->links('vendor.pagination.custom', ['item' => $products]) }}
            </div>
        @endif
    </div>
</section>
