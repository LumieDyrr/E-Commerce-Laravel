<header class="" x-data="{ open: false, profileOpen: false }">
    <div class="fixed z-50 w-full px-4 shadow-sm bg-slate-50 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a class="block text-teal-600 md:mr-8" href="#">
                <span class="sr-only">Home</span>
                TokoPelia
            </a>

            <div class="flex items-center justify-end flex-1 md:justify-between">
                <!-- Menu untuk Desktop -->
                <nav aria-label="Global" class="hidden md:block">
                    <ul class="flex items-center gap-6">
                        <li>
                            <a class="text-gray-500 transition hover:text-gray-500/75 {{ request()->routeIs('home.index') ? 'text-gray-900 font-semibold border-b-2 border-gray-900' : '' }}"
                                href="{{ route('home.index') }}"> Beranda </a>
                        </li>
                        <li>
                            <a class="text-gray-500 transition hover:text-gray-500/75 {{ request()->routeIs('products.index') ? 'text-gray-900 font-semibold border-b-2 border-gray-900' : '' }}"
                                href="{{ route('products.index') }}"> Produk </a>
                        </li>
                        @auth
                            <li>
                                <a class="text-gray-500 transition hover:text-gray-500/75 {{ request()->routeIs('orders.index') ? 'text-gray-900 font-semibold border-b-2 border-gray-900' : '' }}"
                                    href="{{ route('orders.index') }}"> Pesanan </a>
                            </li>
                        @endauth
                    </ul>
                </nav>

                <div class="flex items-center gap-4">
                    @if (!Auth::check())
                        <div class="sm:flex sm:gap-4">
                            <a class="bg-slate-950 px-5 py-2.5 font-medium text-white shadow"
                                href="{{ route('login') }}"> Masuk </a>
                            <div class="hidden sm:flex">
                                <a class="border border-slate-950 px-5 py-2.5 font-medium text-slate-950"
                                    href="{{ route('register') }}"> Daftar </a>
                            </div>
                        </div>
                    @else
                        <div class="relative hidden md:block" @click.away="profileOpen = false">
                            <button @click="profileOpen = !profileOpen"
                                class="flex items-center text-gray-500 transition hover:text-gray-500/75">
                                <img src="{{ asset('storage/profiles/customer.jpg') }}" alt="Profile"
                                    class="w-8 h-8 rounded-full" />
                            </button>

                            <div x-show="profileOpen" x-transition
                                class="absolute right-0 z-10 w-48 mt-2 border-gray-200 shadow-lg bg-slate-50">
                                <p class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Halo, {{ explode(' ', Auth::user()->name)[0] }}!
                                </p>
                                @if ($activeSub = Auth::user()->activeSubscription())
                                    <p class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100 font-bold">
                                        <i class='bx bx-badge-check'></i> Member {{ $activeSub->label }}
                                    </p>
                                @endif
                                <hr>
                                @if (Auth::user()->role == 'admin')
                                    <a href="{{ route('dashboard.index') }}"
                                        class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <i class='bx bxs-dashboard'></i> Dashboard
                                    </a>
                                    <hr>
                                @endif
                                @if (Auth::user()->role == 'customer')
                                    <a href="{{ route('cart.index') }}"
                                        class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <i class='bx bx-cart'></i> Keranjang
                                        @if (($cartCount = \App\Models\Cart::where('user_id', Auth::id())->sum('quantity')) > 0)
                                            <span class="px-1.5 text-xs text-white bg-red-600 rounded-full">{{ $cartCount }}</span>
                                        @endif
                                    </a>
                                    <a href="{{ route('profile.index') }}"
                                        class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <i class='bx bx-user'></i> Profil
                                    </a>
                                    <hr>
                                @endif
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-left text-red-600 hover:bg-red-100 hover:text-red-700">
                                        <i class='bx bx-log-out'></i> Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- Tombol untuk membuka menu mobile -->
                    <div class="block md:hidden">
                        <button @click="open = !open"
                            class="p-2 text-gray-600 transition bg-gray-100 hover:text-gray-600/75">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu responsif untuk tampilan mobile dengan animasi transisi -->
        <nav x-show="open" x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-4"
            @click.away="open = false" aria-label="Global" class="md:hidden">
            <ul class="px-4 pb-4 mt-2 space-y-2 text-sm">
                <li><a class="block py-2 text-gray-700 transition hover:text-gray-500"
                        href="{{ route('home.index') }}"> Beranda </a></li>
                <li><a class="block py-2 text-gray-700 transition hover:text-gray-500"
                        href="{{ route('products.index') }}"> Produk </a></li>
                <li><a class="block py-2 text-gray-700 transition hover:text-gray-500"
                        href="{{ route('orders.index') }}"> Pesanan </a></li>
                @guest
                    <li><a class="block bg-slate-950 px-5 py-2.5 text-sm font-medium text-white text-center"
                            href="{{ route('login') }}"> Masuk </a></li>
                    <li><a class="block bg-gray-100 px-5 py-2.5 text-sm font-medium text-slate-950 text-center"
                            href="{{ route('register') }}"> Daftar </a></li>
                @endguest
                @auth
                    <li><a class="block py-2 text-gray-700 transition hover:text-gray-500"
                            href="{{ route('cart.index') }}"> Keranjang </a></li>
                    <li><a class="block py-2 text-gray-700 transition hover:text-gray-500"
                            href="{{ route('profile.index') }}"> Profil </a></li>
                    <li><form action="{{ route('logout') }}" method="POST">@csrf<button type="submit" class="block w-full bg-slate-950 px-5 py-2.5 text-sm font-medium text-white text-center">Keluar</button></form></li>
                @endauth
            </ul>
        </nav>
    </div>
</header>
