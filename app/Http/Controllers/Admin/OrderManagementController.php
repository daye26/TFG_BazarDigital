<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use RuntimeException;

class OrderManagementController extends Controller
{
    public function index(Request $request): View
    {
        $scope = $this->normalizeScope($request->string('scope')->toString());
        $searchQuery = trim($request->string('q')->toString());
        $selectedDate = $this->normalizeDateFilter(trim($request->string('date')->toString()));

        $orders = Order::query()
            ->with(['user', 'items.product'])
            ->when(
                $searchQuery !== '',
                fn ($query) => $query->where('order_number', 'like', '%' . $searchQuery . '%')
            )
            ->when(
                $selectedDate !== null,
                fn ($query) => $query->whereDate('created_at', $selectedDate)
            )
            ->when(
                $scope === 'pending',
                fn ($query) => $query->where('status', OrderStatus::PENDING)
            )
            ->when(
                $scope === 'preparable',
                fn ($query) => $query
                    ->where('status', OrderStatus::PENDING)
                    ->where(function ($nestedQuery) {
                        $nestedQuery
                            ->where('payment_method', PaymentMethod::STORE->value)
                            ->orWhere('payment_status', PaymentStatus::PAID->value);
                    })
            )
            ->when(
                $scope === 'ready',
                fn ($query) => $query->where('status', OrderStatus::READY)
            )
            ->when(
                $scope === 'cancelled',
                fn ($query) => $query->where('status', OrderStatus::CANCELLED)
            )
            ->latest()
            ->simplePaginate(10)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'scope' => $scope,
            'searchQuery' => $searchQuery,
            'selectedDate' => $selectedDate,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $scope = $this->normalizeScope($request->string('scope')->toString());
        $searchQuery = trim($request->string('q')->toString());
        $selectedDate = $this->normalizeDateFilter(trim($request->string('date')->toString()));
        $page = max($request->integer('page'), 1);

        return view('admin.orders.show', [
            'order' => $order->load(['user', 'items.product']),
            'backUrl' => route('admin.orders.index', $this->orderListParameters($scope, $searchQuery, $selectedDate, $page)),
            'returnScope' => $scope,
            'returnSearchQuery' => $searchQuery,
            'returnSelectedDate' => $selectedDate,
            'returnPage' => $page,
        ]);
    }

    public function ready(Request $request, Order $order, OrderService $orderService): RedirectResponse
    {
        try {
            $orderService->markReady($order);
        } catch (RuntimeException $exception) {
            return $this->redirectToRequestedLocation($request, $order)->withErrors([
                'order' => $exception->getMessage(),
            ]);
        }

        return $this->redirectToRequestedLocation($request, $order)
            ->with('status', 'El pedido ya figura como listo para recoger.');
    }

    public function complete(Request $request, Order $order, OrderService $orderService): RedirectResponse
    {
        try {
            $orderService->markCompleted($order);
        } catch (RuntimeException $exception) {
            return $this->redirectToRequestedLocation($request, $order)->withErrors([
                'order' => $exception->getMessage(),
            ]);
        }

        return $this->redirectToRequestedLocation($request, $order)
            ->with('status', 'El pedido se ha completado correctamente.');
    }

    private function redirectToRequestedLocation(Request $request, Order $order): RedirectResponse
    {
        $scope = $this->normalizeScope($request->string('return_scope')->toString());
        $searchQuery = trim($request->string('return_q')->toString());
        $selectedDate = $this->normalizeDateFilter(trim($request->string('return_date')->toString()));
        $page = max($request->integer('return_page'), 1);

        if ($request->string('return_context')->toString() === 'show') {
            return redirect()->route('admin.orders.show', array_merge(
                ['order' => $order->id],
                $this->orderListParameters($scope, $searchQuery, $selectedDate, $page),
            ));
        }

        return redirect()->route('admin.orders.index', $this->orderListParameters($scope, $searchQuery, $selectedDate, $page));
    }

    private function normalizeScope(string $scope): string
    {
        return in_array($scope, ['pending', 'preparable', 'ready', 'cancelled'], true) ? $scope : 'all';
    }

    private function normalizeDateFilter(string $selectedDate): ?string
    {
        if ($selectedDate === '') {
            return null;
        }

        try {
            $normalizedDate = Carbon::createFromFormat('Y-m-d', $selectedDate);
        } catch (\Throwable) {
            return null;
        }

        return $normalizedDate->format('Y-m-d') === $selectedDate
            ? $normalizedDate->toDateString()
            : null;
    }

    private function orderListParameters(string $scope, string $searchQuery, ?string $selectedDate, int $page = 1): array
    {
        return array_filter([
            'scope' => $scope !== 'all' ? $scope : null,
            'q' => $searchQuery !== '' ? $searchQuery : null,
            'date' => $selectedDate,
            'page' => $page > 1 ? $page : null,
        ]);
    }
}
