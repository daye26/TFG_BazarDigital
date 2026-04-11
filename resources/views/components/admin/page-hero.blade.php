@props([
    'kicker',
    'title',
    'description' => null,
    'backHref' => null,
    'backLabel' => 'Volver',
])

<div class="app-hero">
    <div class="app-hero-header">
        <p class="app-hero-kicker">{{ $kicker }}</p>

        @if ($backHref)
            <a href="{{ $backHref }}" class="app-button-secondary-light">
                {{ $backLabel }}
            </a>
        @endif
    </div>

    <h3 class="app-hero-title">{{ $title }}</h3>

    @if ($description)
        <p class="app-hero-copy">
            {{ $description }}
        </p>
    @endif
</div>
