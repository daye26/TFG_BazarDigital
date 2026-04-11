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
        @php
            $storeGlowClasses = request()->routeIs('orders.index')
                ? 'absolute inset-x-0 top-0 h-56 bg-gradient-to-b from-amber-100/70 via-amber-50/35 to-transparent'
                : 'absolute inset-x-0 top-0 h-80 bg-gradient-to-br from-amber-200 via-orange-100 to-stone-100';
        @endphp

        <div class="relative overflow-hidden">
            <div class="{{ $storeGlowClasses }}"></div>

            <header class="store-site-header relative z-50 border-b border-stone-200/80 bg-white/80 backdrop-blur">
                <div class="store-shell flex flex-col gap-4 py-4 lg:grid lg:grid-cols-[auto_1fr_auto] lg:items-center">
                    <div class="flex items-center justify-between gap-4">
                        <a href="{{ route('home') }}" class="flex items-center gap-3">
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-stone-900 text-sm font-black uppercase tracking-[0.2em] text-amber-200">BD</span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-stone-500">Bazar Digital</p>
                                <p class="text-sm text-stone-700">Catalogo y compra diaria</p>
                            </div>
                        </a>
                    </div>

                    <nav class="hidden items-center justify-center gap-6 text-sm font-medium text-stone-700 md:flex lg:flex">
                        <a href="{{ route('home') }}" class="transition hover:text-stone-950">Inicio</a>
                        <a href="{{ route('products.index') }}" class="transition hover:text-stone-950">Tienda</a>
                        <a href="{{ route('products.latest') }}" class="transition hover:text-stone-950">Novedades</a>
                    </nav>

                    <div class="flex flex-wrap items-center justify-end gap-3">
                            <div
                                class="store-search-shell"
                                data-global-search
                                data-suggestions-url="{{ route('search.suggestions') }}"
                                data-results-url="{{ route('products.index') }}"
                            >
                                <button
                                    type="button"
                                    class="{{ request()->filled('q') ? 'store-search-trigger store-search-trigger-active' : 'store-search-trigger' }}"
                                    aria-label="Abrir buscador global"
                                    aria-expanded="false"
                                    aria-controls="store-global-search"
                                    data-search-trigger
                                >
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                    </svg>
                                    <span class="sr-only">Buscar productos, categorias o codigo</span>
                                </button>

                                <div
                                    id="store-global-search"
                                    class="store-search-dropdown hidden"
                                    data-search-dropdown
                                >
                                    <form method="GET" action="{{ route('products.index') }}" class="store-search-form" role="search">
                                        <label for="store-global-search-input" class="sr-only">
                                            Buscar productos, categorias o codigo de barras
                                        </label>
                                        <svg class="h-5 w-5 shrink-0 text-stone-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                        </svg>
                                        <input
                                            id="store-global-search-input"
                                            type="search"
                                            name="q"
                                            value="{{ request('q') }}"
                                            class="store-search-input"
                                            placeholder="Busca productos, categorias o codigo"
                                            autocomplete="off"
                                            spellcheck="false"
                                            data-search-input
                                        >
                                        <button type="submit" class="store-button-primary shrink-0 px-4 py-2">
                                            Buscar
                                        </button>
                                    </form>

                                    <div class="store-search-panel hidden" data-search-panel></div>
                                </div>
                            </div>

                        @auth
                            @if (Auth::user()->isAdmin())
                                <a href="{{ route('admin.index') }}" class="rounded-full bg-amber-200 px-4 py-2 text-sm font-semibold text-stone-900 transition hover:bg-amber-300">
                                    Panel de control
                                </a>
                            @else
                                <a href="{{ route('orders.index') }}" class="rounded-full border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-800 transition hover:border-stone-900 hover:text-stone-950">
                                    Pedidos
                                </a>
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

            <footer class="store-site-footer relative mt-16 border-t border-stone-200 bg-white">
                <div class="store-shell flex flex-col gap-3 py-8 text-sm text-stone-600 lg:flex-row lg:items-center lg:justify-between">
                    <p>Bazar Digital. Base inicial del catalogo.</p>
                    <p>Laravel, catalogo publico y estructura lista para crecer.</p>
                </div>
            </footer>
        </div>
    </body>
</html>
