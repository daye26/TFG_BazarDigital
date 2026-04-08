@props(['active'])

@php
$classes = ($active ?? false)
            ? 'app-nav-mobile-link app-nav-mobile-link-active'
            : 'app-nav-mobile-link';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
