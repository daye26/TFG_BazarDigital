<nav x-data="{ open: false }" class="app-nav">
    @php
        $isAdmin = Auth::user()?->isAdmin();
        $adminLinks = [
            ['label' => 'Panel', 'href' => route('admin.index'), 'active' => request()->routeIs('admin.index')],
            ['label' => 'Estadisticas', 'href' => route('admin.stats.index'), 'active' => request()->routeIs('admin.stats.*')],
            ['label' => 'Pedidos', 'href' => route('admin.orders.index'), 'active' => request()->routeIs('admin.orders.*')],
            ['label' => 'Productos', 'href' => route('admin.products.manage'), 'active' => request()->routeIs('admin.products.*')],
            ['label' => 'Categorias', 'href' => route('admin.categories.manage'), 'active' => request()->routeIs('admin.categories.*')],
        ];
    @endphp

    <!-- Primary Navigation Menu -->
    <div class="app-nav-shell">
        <div class="app-nav-row">
            <div class="app-nav-brand-group">
                <!-- Logo -->
                <div class="app-nav-logo">
                    <a href="{{ route('home') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="app-nav-links">
                    @if ($isAdmin)
                        @foreach ($adminLinks as $link)
                            <x-nav-link :href="$link['href']" :active="$link['active']">
                                {{ __($link['label']) }}
                            </x-nav-link>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="app-nav-user">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="app-nav-user-trigger">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Perfil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="app-nav-toggle-shell">
                <button @click="open = ! open" class="app-nav-toggle">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

        <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="app-nav-mobile-links">
            @if ($isAdmin)
                @foreach ($adminLinks as $link)
                    <x-responsive-nav-link :href="$link['href']" :active="$link['active']">
                        {{ __($link['label']) }}
                    </x-responsive-nav-link>
                @endforeach
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="app-nav-mobile-user">
            <div>
                <div class="app-nav-mobile-user-name">{{ Auth::user()->name }}</div>
                <div class="app-nav-mobile-user-email">{{ Auth::user()->email }}</div>
            </div>

            <div class="app-nav-mobile-user-links">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Perfil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Cerrar sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
