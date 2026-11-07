@extends('layouts.app')

@section('title', 'Pembayaran | E-Commerce')

@section('content')
<div class="pt-12 bg-white">
  <!-- Background color split screen for large screens -->
  <div class="fixed top-0 left-0 hidden w-1/2 h-full bg-white lg:block" aria-hidden="true"></div>
  <div class="fixed top-0 right-0 hidden w-1/2 h-full bg-gray-100 lg:block" aria-hidden="true"></div>

  <main class="relative grid grid-cols-1 mx-auto max-w-7xl gap-x-16 lg:grid-cols-2 lg:px-8 xl:gap-x-48">
    <h1 class="sr-only">Informasi Pesanan</h1>

    <section aria-labelledby="summary-heading" class="px-4 pt-16 pb-10 bg-gray-50 sm:px-6 lg:col-start-2 lg:row-start-1 lg:bg-transparent lg:px-0 lg:pb-16">
      <div class="max-w-lg mx-auto lg:max-w-none">
        <h2 id="summary-heading" class="text-lg font-medium text-gray-900">Pesanan</h2>

        <ul role="list" class="text-sm font-medium text-gray-900 divide-y divide-gray-200">
          @foreach ($carts as $cart)
            <li class="flex items-start py-6 space-x-4">
              <img src="{{ $cart->product->image_url }}" alt="Gambar produk" class="flex-none object-cover object-center w-20 h-20 rounded-md">
              <div class="flex-auto space-y-1">
                <h3>{{ $cart->product->name }}</h3>
              </div>
              <div>
                <p class="flex-none text-base font-medium">
                  @if ($cart->product->discount > 0)
                    Rp{{ number_format($cart->product->price - ($cart->product->price * $cart->product->discount / 100), 0, ',', '.') }}
                    <span class="text-gray-400 line-through">
                      Rp{{ number_format($cart->product->price, 0, ',', '.') }}
                    </span>
                  @else
                    Rp{{ number_format($cart->product->price, 0, ',', '.') }}
                  @endif
                </p>
                <div class="flex gap-4 mt-2 p-2 justify-end">
                  <form action="{{ route('cart.remove', $cart->id) }}" method="POST">
                      @csrf
                      <button type="submit" class="items-center h-full">
                      -
                      </button>
                  </form>
                  <p class="text-lg ">{{ $cart->quantity }}</p>
                  <form action="{{ route('cart.add', $cart->id) }}" method="POST">
                      @csrf
                      <button type="submit" class="items-center h-full ">
                      +
                      </button>
                  </form>
                </div>
                <div class="flex justify-end mt-4">
                  <form action="{{ route('cart.destroy', $cart->id) }}" method="POST">
                      @csrf
                      @method('DELETE')
                      <button type="submit" 
                              class="text-sm font-medium text-slate-50 hover:text-slate-300 bg-slate-950 py-2 px-4"
                              onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                          Hapus
                      </button>
                  </form>
                </div>
              </div>
            </li>    
          @endforeach
        </ul>

        <dl class="pt-6 space-y-4 text-sm font-medium text-gray-900 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <dt class="text-gray-600">Subtotal</dt>
            <dd>Rp{{ number_format($summary['subtotal'], 0, ',', '.') }}</dd>
          </div>
          @if ($summary['discount_percent'] > 0)
            <div class="flex items-center justify-between">
              <dt class="text-gray-600">Diskon Member ({{ $summary['discount_percent'] }}%)</dt>
              <dd>-Rp{{ number_format($summary['discount_amount'], 0, ',', '.') }}</dd>
            </div>
          @endif
          <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <dt class="text-base">Total Pembayaran</dt>
            <dd class="text-base">Rp{{ number_format($summary['total'], 0, ',', '.') }}</dd>
          </div>
        </dl>
      </div>
    </section>

    <form action="{{ route('checkout.store') }}" method="POST" enctype="multipart/form-data" class="px-4 pt-16 pb-36 sm:px-6 lg:col-start-1 lg:row-start-1 lg:px-0 lg:pb-16">
      @csrf

      <div class="max-w-lg mx-auto lg:max-w-none">
        <section aria-labelledby="contact-info-heading">
          <h2 id="contact-info-heading" class="text-lg font-medium text-gray-900">Informasi Pesanan</h2>
    
          <div class="mt-6">
            <label for="email-address" class="block text-sm font-medium text-gray-700">Email</label>
            <div class="mt-1">
              <input type="email" id="email-address" name="email-address" autocomplete="email" class="block w-full h-12 px-4 bg-gray-100 border border-gray-400 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm"
              value="{{ auth()->user()->email }}"
              disabled>
            </div>
          </div>
        </section>

            
        <section aria-labelledby="shipping-heading" class="mt-10">
          <h2 id="shipping-heading" class="text-lg font-medium text-gray-900">Informasi Pengiriman</h2>
        
          <div class="grid grid-cols-1 mt-6 gap-x-4 gap-y-6 sm:grid-cols-3">
        
            <div class="sm:col-span-3"> 
              <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
              <div class="mt-1">
                <textarea id="alamat" name="alamat" autocomplete="street-address" class="block w-full h-24 px-4 py-2 border border-gray-400 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm">{{ old('alamat') }}</textarea>
              </div>
              @if ($errors->has('alamat'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->first('alamat') }}</p>
              @endif
            </div>
        
            <div class="sm:col-span-3">
              <label for="detail-alamat" class="block text-sm font-medium text-gray-700">Detail Alamat (Opsional)</label>
              <div class="mt-1">
                <input type="text" id="detail-alamat" name="detail-alamat" class="block w-full h-12 px-4 border border-gray-400 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm" value="{{ old('detail-alamat') }}">
              </div>
            </div>            
        
            <div>
              <label for="kota" class="block text-sm font-medium text-gray-700">Kota</label>
              <div class="mt-1">
                <input type="text" id="kota" name="kota" autocomplete="address-level2" class="block w-full h-12 px-4 border border-gray-400 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm" value="{{ old('kota') }}">
              </div>
              @if ($errors->has('kota'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->first('kota') }}</p>
              @endif
            </div>
        
            <div>
              <label for="provinsi" class="block text-sm font-medium text-gray-700">Provinsi</label>
              <div class="mt-1">
                <input type="text" id="provinsi" name="provinsi" autocomplete="address-level1" class="block w-full h-12 px-4 border border-gray-400 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm" value="{{ old('provinsi') }}">
              </div>
              @if ($errors->has('provinsi'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->first('provinsi') }}</p>
              @endif
            </div>
        
            <div>
              <label for="kode-pos" class="block text-sm font-medium text-gray-700">Kode Pos</label>
              <div class="mt-1">
                <input type="text" id="kode-pos" name="kode-pos" autocomplete="postal-code" class="block w-full h-12 px-4 border border-gray-400 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm" value="{{ old('kode-pos') }}">
              </div>
              @if ($errors->has('kode-pos'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->first('kode-pos') }}</p>
              @endif
            </div>
          </div>
        </section>        
    
        <section aria-labelledby="payment-heading" class="mt-10">
          <h2 id="payment-heading" class="text-lg font-medium text-gray-900">Detail Pembayaran</h2>
    
          <div class="grid grid-cols-3 mt-6 gap-x-4 gap-y-6 sm:grid-cols-4">
            <div class="col-span-3 sm:col-span-4">
              <img src="{{ asset('storage/qr/code.png') }}" alt="" class="mx-auto">
            </div>
    
            <div class="col-span-3 sm:col-span-4">
              <label for="bukti-pembayaran" class="block text-sm font-medium text-gray-700">Bukti Pembayaran</label>
              <div class="mt-1">
                <input type="file" id="bukti-pembayaran" name="bukti-pembayaran" autocomplete="cc-number" class="block w-full px-4 py-2 border border-gray-400 shadow-sm focus:border-gray-500 focus:ring-gray-500 sm:text-sm" value="{{ old('alamat') }}">
              </div>
              @if ($errors->has('bukti-pembayaran'))
                <p class="mt-2 text-sm text-red-600">{{ $errors->first('bukti-pembayaran') }}</p>
              @endif
            </div>
        </section>

        <div class="pt-6 mt-10 border-t border-gray-200 sm:flex sm:items-center sm:justify-between">
          <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:ring-offset-gray-50 sm:order-last sm:ml-6 sm:w-auto">Bayar</button>
          <p class="mt-4 text-sm text-center text-gray-500 sm:mt-0 sm:text-left">Lakukan pembayaran </p>
        </div>
      </div>
    </form>
    
    
  </main>
</div>
@endsection
