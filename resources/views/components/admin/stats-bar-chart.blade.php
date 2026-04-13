@props([
    'title',
    'description' => null,
    'labels' => [],
    'values' => [],
    'formattedValues' => [],
    'accent' => '#d97706',
    'metricLabel' => 'Total',
    'metricValue' => '0',
    'emptyLabel' => 'Todavia no hay registros en este periodo.',
])

@php
    $labels = array_values($labels);
    $values = array_map(static fn ($value): float => (float) $value, array_values($values));
    $formattedValues = array_values($formattedValues);
    $hasRows = $labels !== [];

    $chartConfig = [
        'type' => 'bar',
        'labels' => $labels,
        'fullLabels' => $labels,
        'values' => $values,
        'formattedValues' => $formattedValues,
        'accent' => $accent,
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

    @if ($hasRows)
        <div class="app-chart-shell mt-6">
            <canvas class="app-chart-canvas" data-admin-chart></canvas>
            <script type="application/json" data-chart-config>@json($chartConfig)</script>
        </div>
    @else
        <p class="app-chart-empty-copy mt-6">{{ $emptyLabel }}</p>
    @endif
</section>
