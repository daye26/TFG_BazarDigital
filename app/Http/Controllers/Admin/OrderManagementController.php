<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class OrderManagementController extends Controller
{
    public function index(Request $request): View
    {
        $scope = $request->string('scope')->toString();

        $orders = Order::query()
            ->with(['user', 'items'])
            ->when(
                $scope === 'pending',
                fn ($query) => $query->where('status', OrderStatus::PENDING)
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
            ->get();

        return view('admin.orders.index', [
            'orders' => $orders,
            'scope' => in_array($scope, ['pending', 'ready', 'cancelled'], true) ? $scope : 'all',
        ]);
    }

    public function ready(Order $order, OrderService $orderService): RedirectResponse
    {
        try {
            $orderService->markReady($order);
        } catch (RuntimeException $exception) {
            return to_route('admin.orders.index')->withErrors([
                'order' => $exception->getMessage(),
            ]);
        }

        return to_route('admin.orders.index')->with('status', 'El pedido ya figura como listo para recoger.');
    }

    public function complete(Order $order, OrderService $orderService): RedirectResponse
    {
        try {
            $orderService->markCompleted($order);
        } catch (RuntimeException $exception) {
            return to_route('admin.orders.index')->withErrors([
                'order' => $exception->getMessage(),
            ]);
        }

        return to_route('admin.orders.index')->with('status', 'El pedido se ha completado correctamente.');
    }
}
