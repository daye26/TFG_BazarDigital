@props([
    'kicker' => null,
    'title',
    'description' => null,
])

<div {{ $attributes->class('app-section-header') }}>
    <div class="app-section-header-copy">
        @if ($kicker)
            <p class="app-section-kicker">{{ $kicker }}</p>
        @endif

        <h4 class="app-section-heading">{{ $title }}</h4>
    </div>

    @if (isset($aside))
        <div class="app-section-header-meta">
            {{ $aside }}
        </div>
    @elseif ($description)
        <p class="app-section-description">{{ $description }}</p>
    @endif
</div>
