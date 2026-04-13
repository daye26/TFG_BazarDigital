<x-app-layout>
    <div class="app-page">
        <div class="app-shell-stack">
            <section class="app-surface">
                <x-admin.page-hero
                    kicker="Zona admin"
                    title="Estadisticas"
                    description="Analiza pedidos, cobros y altas de usuarios por rango de fechas. Hoy se aplica por defecto para que entres directamente con una foto clara del dia."
                />

                <div class="app-surface-body">
                    <section class="app-card-muted">
                        <div>
                            <p class="app-section-kicker">Periodo de analisis</p>
                            <h4 class="app-section-title">Ajusta el rango de fechas</h4>
                        </div>

                        <form method="GET" action="{{ route('admin.stats.index') }}" class="app-stats-filter-form">
                            <input type="hidden" name="preset" value="custom">

                            <div class="app-stats-filter-presets">
                                @foreach ($range['presets'] as $presetOption)
                                    <a
                                        href="{{ route('admin.stats.index', ['preset' => $presetOption['key']]) }}"
                                        class="{{ $range['preset'] === $presetOption['key'] ? 'app-button-primary' : 'app-button-secondary' }}"
                                    >
                                        {{ $presetOption['label'] }}
                                    </a>
                                @endforeach
                            </div>

                            <div class="app-stats-filter-fields">
                                <div class="app-stats-date-field">
                                    <label for="stats-from" class="app-stat-label">Desde</label>
                                    <input
                                        id="stats-from"
                                        name="from"
                                        type="date"
                                        value="{{ $range['from_input'] }}"
                                        class="app-filter-input app-filter-input-spacious mt-2"
                                    >
                                </div>

                                <div class="app-stats-date-field">
                                    <label for="stats-to" class="app-stat-label">Hasta</label>
                                    <input
                                        id="stats-to"
                                        name="to"
                                        type="date"
                                        value="{{ $range['to_input'] }}"
                                        class="app-filter-input app-filter-input-spacious mt-2"
                                    >
                                </div>

                                <div class="flex flex-wrap gap-3 sm:pb-[1px]">
                                    <button type="submit" class="app-button-primary">
                                        Aplicar rango
                                    </button>
                                </div>
                            </div>
                        </form>
                    </section>

                    <section class="mt-8">
                        <x-admin.section-header kicker="Resumen del periodo" :title="$range['label']">
                            <x-slot name="aside">
                                <p class="app-section-description">Ticket medio y cobrado usan solo pedidos ya marcados como pagados.</p>
                            </x-slot>
                        </x-admin.section-header>

                        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <article class="app-stat-card">
                                <p class="app-stat-label">Pedidos creados</p>
                                <p class="app-stat-value">{{ number_format($summary['orders_created'], 0, ',', '.') }}</p>
                            </article>

                            <article class="app-stat-card">
                                <p class="app-stat-label">Pedidos entregados</p>
                                <p class="app-stat-value">{{ number_format($summary['orders_completed'], 0, ',', '.') }}</p>
                            </article>

                            <article class="app-stat-card">
                                <p class="app-stat-label">Importe cobrado</p>
                                <p class="app-stat-value-success">{{ $summary['formatted_revenue'] }}</p>
                            </article>

                            <article class="app-stat-card">
                                <p class="app-stat-label">Ticket medio</p>
                                <p class="app-stat-value">{{ $summary['formatted_average_ticket'] }}</p>
                            </article>
                        </div>
                    </section>

                    <section class="mt-8 grid gap-6 xl:grid-cols-2">
                        <x-admin.stats-line-chart
                            title="Pedidos por dia"
                            description="Evolucion de pedidos creados dentro del rango seleccionado."
                            :labels="$charts['orders_by_day']['labels']"
                            :full-labels="$charts['orders_by_day']['full_labels']"
                            :values="$charts['orders_by_day']['values']"
                            :formatted-values="$charts['orders_by_day']['formatted_values']"
                            metric-label="Pedidos"
                            :metric-value="$charts['orders_by_day']['metric_value']"
                            accent="#1c1917"
                            fill="rgba(28, 25, 23, 0.08)"
                            empty-label="No se han creado pedidos en este periodo."
                        />

                        <x-admin.stats-line-chart
                            title="Cobrado por dia"
                            description="Importe realmente cobrado segun la fecha registrada en paid_at."
                            :labels="$charts['revenue_by_day']['labels']"
                            :full-labels="$charts['revenue_by_day']['full_labels']"
                            :values="$charts['revenue_by_day']['values']"
                            :formatted-values="$charts['revenue_by_day']['formatted_values']"
                            metric-label="Cobrado"
                            :metric-value="$charts['revenue_by_day']['metric_value']"
                            accent="#047857"
                            fill="rgba(4, 120, 87, 0.12)"
                            empty-label="Todavia no hay cobros registrados en este periodo."
                        />

                        <x-admin.stats-line-chart
                            title="Altas de usuarios por dia"
                            description="Registra las nuevas cuentas de cliente creadas dentro del rango."
                            :labels="$charts['user_signups_by_day']['labels']"
                            :full-labels="$charts['user_signups_by_day']['full_labels']"
                            :values="$charts['user_signups_by_day']['values']"
                            :formatted-values="$charts['user_signups_by_day']['formatted_values']"
                            metric-label="Altas"
                            :metric-value="$charts['user_signups_by_day']['metric_value']"
                            accent="#b45309"
                            fill="rgba(180, 83, 9, 0.12)"
                            empty-label="No hay nuevas cuentas de cliente en este periodo."
                        />

                        <x-admin.stats-bar-chart
                            title="Top productos del periodo"
                            description="Ranking de unidades movidas en pedidos no cancelados dentro del rango."
                            :labels="$charts['top_products']['labels']"
                            :values="$charts['top_products']['values']"
                            :formatted-values="$charts['top_products']['formatted_values']"
                            metric-label="Unidades"
                            :metric-value="$charts['top_products']['metric_value']"
                            accent="#d97706"
                            empty-label="Todavia no hay productos con movimiento en este periodo."
                        />
                    </section>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
