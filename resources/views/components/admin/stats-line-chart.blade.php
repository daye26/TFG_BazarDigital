@props([
    'title',
    'description' => null,
    'labels' => [],
    'fullLabels' => [],
    'values' => [],
    'formattedValues' => [],
    'accent' => '#1c1917',
    'fill' => 'rgba(28, 25, 23, 0.08)',
    'metricLabel' => 'Total',
    'metricValue' => '0',
    'emptyLabel' => 'Sin actividad en el periodo.',
])

@php
    $labels = array_values($labels);
    $fullLabels = array_values($fullLabels);
    $values = array_map(static fn ($value): float => (float) $value, array_values($values));
    $formattedValues = array_values($formattedValues);
    $hasActivity = collect($values)->contains(static fn (float $value): bool => $value > 0);

    $chartConfig = [
        'type' => 'line',
        'labels' => $labels,
        'fullLabels' => $fullLabels,
        'values' => $values,
        'formattedValues' => $formattedValues,
        'accent' => $accent,
        'fill' => $fill,
    ];
@endphp

<section class="app-card">
    <div class="app-chart-card-header">
        <div class="app-chart-card-copy">
            <p class="app-section-kicker">{{ $title }}</p>
            @if ($description)
                <p class="app-chart-card-description">{{ $description }}</p>
            @endif
        </div>

        <div class="app-chart-card-metric">
            <p class="app-stat-label">{{ $metricLabel }}</p>
            <p class="app-chart-card-metric-value">{{ $metricValue }}</p>
        </div>
    </div>

    <div class="app-chart-shell mt-6">
        <canvas class="app-chart-canvas" data-admin-chart></canvas>
        <script type="application/json" data-chart-config>@json($chartConfig)</script>
    </div>

    @if (! $hasActivity)
        <p class="app-chart-empty-copy mt-4">{{ $emptyLabel }}</p>
    @endif
</section>
