<div class="flex pt-16 overflow-hidden lg:pt-4 xl:pt-12 max-lg:flex-col-reverse sm:items-center">
  <div class="p-8 md:p-12 lg:px-16 lg:py-24">
    <div class="max-w-xl mx-auto text-center">
      <h2 class="text-2xl font-bold text-gray-900 md:text-3xl">
        Selamat Datang di E-Commerce Kami
      </h2>
      
      <p class="hidden text-gray-500 md:mt-4 md:block">
        Temukan produk yang Anda butuhkan dengan harga terbaik dan kualitas yang baik di E-Commerce kami.
      </p>

      <div class="mt-4 md:mt-8">
        <a
          href="{{ route('products.index') }}"
          class="inline-block px-12 py-3 text-sm font-medium text-white transition bg-slate-950 hover:bg-slate-900 focus:outline-none focus:ring focus:ring-yellow-400"
        >
          Mulai Berbelanja Sekarang
        </a>
      </div>
    </div>
  </div>

  <img
    alt=""
    src="{{ asset('images/beranda/hero.webp') }}"
    class="object-cover w-full h-full min-h-[400px] lg:max-h-[400px] brightness-75 hover:scale-110 duration-150"
  />
</div>