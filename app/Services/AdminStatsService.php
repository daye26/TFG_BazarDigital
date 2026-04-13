<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminStatsService
{
    public const DEFAULT_PRESET = 'today';

    private const PRESET_LABELS = [
        'today' => 'Hoy',
        '7d' => '7 dias',
        '30d' => '30 dias',
        'month' => 'Este mes',
        '90d' => '90 dias',
    ];

    public function resolveRange(Request $request): array
    {
        $preset = $this->normalizePreset($request->string('preset')->toString());
        $fromDate = $this->normalizeDate($request->string('from')->toString());
        $toDate = $this->normalizeDate($request->string('to')->toString());

        if ($fromDate || $toDate) {
            $from = ($fromDate ?? $toDate ?? now())->copy()->startOfDay();
            $to = ($toDate ?? $fromDate ?? now())->copy()->endOfDay();

            if ($from->gt($to)) {
                [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
            }

            $preset = 'custom';
            $label = $this->customRangeLabel($from, $to);
        } else {
            [$from, $to] = $this->presetRange($preset);
            $label = self::PRESET_LABELS[$preset];
        }

        return [
            'preset' => $preset,
            'label' => $label,
            'from' => $from,
            'to' => $to,
            'from_input' => $from->toDateString(),
            'to_input' => $to->toDateString(),
            'presets' => collect(self::PRESET_LABELS)
                ->map(fn (string $presetLabel, string $presetKey) => [
                    'key' => $presetKey,
                    'label' => $presetLabel,
                ])
                ->values(),
        ];
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        $paidOrdersQuery = $this->paidOrdersQuery($from, $to);
        $revenue = round((float) (clone $paidOrdersQuery)->sum('total'), 2);
        $averageTicket = round((float) ((clone $paidOrdersQuery)->avg('total') ?? 0), 2);

        return [
            'orders_created' => (int) Order::query()
                ->whereBetween('created_at', [$from, $to])
                ->count(),
            'orders_completed' => (int) Order::query()
                ->where('status', OrderStatus::COMPLETED)
                ->whereBetween('updated_at', [$from, $to])
                ->count(),
            'revenue' => $revenue,
            'average_ticket' => $averageTicket,
            'formatted_revenue' => $this->formatCurrency($revenue),
            'formatted_average_ticket' => $this->formatCurrency($averageTicket),
        ];
    }

    public function charts(Carbon $from, Carbon $to): array
    {
        return [
            'orders_by_day' => $this->ordersByDay($from, $to),
            'revenue_by_day' => $this->revenueByDay($from, $to),
            'user_signups_by_day' => $this->userSignupsByDay($from, $to),
            'top_products' => $this->topProducts($from, $to),
        ];
    }

    protected function ordersByDay(Carbon $from, Carbon $to): array
    {
        $rows = Order::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as aggregate')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->mapWithKeys(fn ($row) => [(string) $row->day => (int) $row->aggregate]);

        return $this->buildContinuousDateSeries(
            $from,
            $to,
            $rows,
            fn (int $value) => $value . ' pedido' . ($value === 1 ? '' : 's'),
            fn (array $values) => number_format(array_sum($values), 0, ',', '.'),
        );
    }

    protected function revenueByDay(Carbon $from, Carbon $to): array
    {
        $rows = Order::query()
            ->selectRaw('DATE(paid_at) as day, SUM(total) as aggregate')
            ->where('payment_status', PaymentStatus::PAID)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$from, $to])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->mapWithKeys(fn ($row) => [(string) $row->day => round((float) $row->aggregate, 2)]);

        return $this->buildContinuousDateSeries(
            $from,
            $to,
            $rows,
            fn (float $value) => $this->formatCurrency($value),
            fn (array $values) => $this->formatCurrency(array_sum($values)),
        );
    }

    protected function userSignupsByDay(Carbon $from, Carbon $to): array
    {
        $rows = User::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as aggregate')
            ->where('role', UserRole::USER)
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->mapWithKeys(fn ($row) => [(string) $row->day => (int) $row->aggregate]);

        return $this->buildContinuousDateSeries(
            $from,
            $to,
            $rows,
            fn (int $value) => $value . ' alta' . ($value === 1 ? '' : 's'),
            fn (array $values) => number_format(array_sum($values), 0, ',', '.'),
        );
    }

    protected function topProducts(Carbon $from, Carbon $to): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw('order_items.product_name as label, SUM(order_items.quantity) as aggregate')
            ->where('orders.status', '!=', OrderStatus::CANCELLED->value)
            ->whereBetween('orders.created_at', [$from, $to])
            ->groupBy('order_items.product_name')
            ->orderByDesc('aggregate')
            ->orderBy('order_items.product_name')
            ->limit(5)
            ->get();

        $labels = $rows->pluck('label')->all();
        $values = $rows->map(fn ($row) => (int) $row->aggregate)->all();

        return [
            'labels' => $labels,
            'values' => $values,
            'formatted_values' => array_map(
                fn (int $value): string => $value . ' uds',
                $values
            ),
            'metric_value' => number_format(array_sum($values), 0, ',', '.'),
        ];
    }

    protected function paidOrdersQuery(Carbon $from, Carbon $to)
    {
        return Order::query()
            ->where('payment_status', PaymentStatus::PAID)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [$from, $to]);
    }

    protected function buildContinuousDateSeries(
        Carbon $from,
        Carbon $to,
        Collection $aggregatesByDay,
        callable $valueFormatter,
        callable $metricFormatter,
    ): array {
        $labels = [];
        $fullLabels = [];
        $values = [];
        $formattedValues = [];

        for ($cursor = $from->copy()->startOfDay(); $cursor->lte($to->copy()->startOfDay()); $cursor->addDay()) {
            $dayKey = $cursor->toDateString();
            $value = $aggregatesByDay->get($dayKey, 0);

            $labels[] = $cursor->format('d/m');
            $fullLabels[] = $cursor->format('d/m/Y');
            $values[] = is_numeric($value) ? (float) $value : 0;
            $formattedValues[] = $valueFormatter($value);
        }

        return [
            'labels' => $labels,
            'full_labels' => $fullLabels,
            'values' => $values,
            'formatted_values' => $formattedValues,
            'metric_value' => $metricFormatter($values),
        ];
    }

    protected function normalizePreset(string $preset): string
    {
        return array_key_exists($preset, self::PRESET_LABELS)
            ? $preset
            : self::DEFAULT_PRESET;
    }

    protected function normalizeDate(string $date): ?Carbon
    {
        if ($date === '') {
            return null;
        }

        try {
            $parsed = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Throwable) {
            return null;
        }

        return $parsed->format('Y-m-d') === $date
            ? $parsed
            : null;
    }

    protected function presetRange(string $preset): array
    {
        $today = now();

        return match ($preset) {
            '7d' => [$today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()],
            '30d' => [$today->copy()->subDays(29)->startOfDay(), $today->copy()->endOfDay()],
            'month' => [$today->copy()->startOfMonth(), $today->copy()->endOfDay()],
            '90d' => [$today->copy()->subDays(89)->startOfDay(), $today->copy()->endOfDay()],
            default => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
        };
    }

    protected function customRangeLabel(Carbon $from, Carbon $to): string
    {
        if ($from->isSameDay($to)) {
            return $from->format('d/m/Y');
        }

        return $from->format('d/m/Y') . ' - ' . $to->format('d/m/Y');
    }

    protected function formatCurrency(float|int $amount): string
    {
        return number_format((float) $amount, 2, ',', '.') . ' €';
    }
}
