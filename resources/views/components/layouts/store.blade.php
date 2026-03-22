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

            <header class="relative border-b border-stone-200/80 bg-white/80 backdrop-blur">
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
                        @auth
                            <a href="{{ route('dashboard') }}" class="transition hover:text-stone-950">Mi cuenta</a>
                        @endauth
                    </nav>

                    <div class="flex items-center gap-3">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold text-stone-800 transition hover:border-stone-900 hover:text-stone-950">
                                {{ Auth::user()->name }}
                            </a>
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

            <main class="relative">
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
