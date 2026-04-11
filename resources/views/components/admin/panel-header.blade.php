@props([
    'title',
    'active' => 'dashboard',
    'backHref' => null,
    'backLabel' => 'Volver',
])

<div class="app-page-header app-page-header-admin">
    <div>
        <h2 class="app-page-title">{{ $title }}</h2>
    </div>

    @if ($backHref)
        <a href="{{ $backHref }}" class="app-button-secondary">
            {{ $backLabel }}
        </a>
    @endif
</div>
