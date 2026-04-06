<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Bazar Digital') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-stone-100 text-stone-900 antialiased">
        <div class="relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-80 bg-gradient-to-br from-amber-200 via-orange-100 to-stone-100"></div>

            <header class="relative z-50 border-b border-stone-200/80 bg-white/80 backdrop-blur">
                <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-stone-900 text-sm font-black uppercase tracking-[0.2em] text-amber-200">BD</span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500">Bazar Digital</p>
                            <p class="text-sm text-stone-700">Catalogo y compra diaria</p>
                        </div>
                    </a>

                    <nav class="hidden items-center gap-6 text-sm font-medium text-stone-700 md:flex">
                        <a href="{{ route('home') }}" class="transition hover:text-stone-950">Inicio</a>
                        <a href="{{ route('products.index') }}" class="transition hover:text-stone-950">Tienda</a>
                        <a href="{{ route('products.latest') }}" class="transition hover:text-stone-950">Novedades</a>
                    </nav>

                    <div class="flex items-center gap-3">
                        @auth
                            @if (Auth::user()->isAdmin())
                                <a href="{{ route('admin.index') }}" class="rounded-full bg-amber-200 px-4 py-2 text-sm font-semibold text-stone-900 transition hover:bg-amber-300">
                                    Panel de control
                                </a>
                            @else
                                <a href="{{ route('cart.show') }}" class="rounded-full border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-800 transition hover:border-stone-900 hover:text-stone-950">
                                    Carrito (<span data-cart-count>{{ $storeCartItemsCount ?? 0 }}</span>)
                                </a>
                            @endif
                            <x-dropdown align="right" width="48" contentClasses="py-2 bg-white">
                                <x-slot name="trigger">
                                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-800 transition hover:border-stone-900 hover:text-stone-950">
                                        <span>{{ Auth::user()->name }}</span>
                                        <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        Ajustes
                                    </x-dropdown-link>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            Cerrar sesion
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        @else
                            <a href="{{ route('login') }}" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-800 transition hover:border-stone-900 hover:text-stone-950">
                                Iniciar sesion
                            </a>
                            <a href="{{ route('register') }}" class="rounded-full bg-stone-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-stone-700">
                                Crear cuenta
                            </a>
                        @endauth
                    </div>
                </div>
            </header>

            @if (session('cart_status'))
                <div
                    data-cart-toast-source
                    data-placement="{{ request()->routeIs('products.show') ? 'inline' : 'floating' }}"
                    data-anchor-id="{{ request()->routeIs('products.show') ? 'product-cart-toast-anchor' : '' }}"
                    hidden
                >
                    {{ session('cart_status') }}
                </div>
            @endif

            <main class="relative">
                @if ($errors->any())
                    <div class="store-shell pt-6">
                        <div class="app-alert-error">
                            {{ $errors->first('cart') ?: $errors->first() }}
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>

            <footer class="relative mt-16 border-t border-stone-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-8 text-sm text-stone-600 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <p>Bazar Digital. Base inicial del catalogo.</p>
                    <p>Laravel, catalogo publico y estructura lista para crecer.</p>
                </div>
            </footer>
        </div>
    </body>
</html>
