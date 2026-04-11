<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;
use RuntimeException;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureCustomer($request);

        $readyOrders = $request->user()
            ->orders()
            ->withCount('items')
            ->where('status', OrderStatus::READY)
            ->latest()
            ->get();

        $otherOrders = $request->user()
            ->orders()
            ->withCount('items')
            ->where('status', '!=', OrderStatus::READY)
            ->latest()
            ->simplePaginate(10)
            ->withQueryString();

        return view('orders.index', [
            'readyOrders' => $readyOrders,
            'otherOrders' => $otherOrders,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->ensureCustomer($request);
        $this->ensureOrderBelongsToUser($request, $order);

        return view('orders.show', [
            'order' => $order->load(['items.product']),
        ]);
    }

    public function store(
        Request $request,
        OrderService $orderService,
        StripeCheckoutService $stripeCheckoutService,
    ): RedirectResponse {
        $this->ensureCustomer($request);

        $validated = $request->validate([
            'pickup_name' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', new Enum(PaymentMethod::class)],
        ]);

        $paymentMethod = PaymentMethod::from($validated['payment_method']);

        try {
            $order = $orderService->placeFromCart($request->user(), $validated['pickup_name'], $paymentMethod);
        } catch (RuntimeException $exception) {
            return to_route('cart.show')->withErrors([
                'order' => $exception->getMessage(),
            ])->withInput();
        }

        if ($paymentMethod === PaymentMethod::STORE) {
            $orderService->clearCart($request->user());

            return to_route('orders.show', $order)
                ->with('order_status', 'Pedido creado correctamente. Queda pendiente de pago en tienda.');
        }

        try {
            $session = $stripeCheckoutService->createSession($order);
            $orderService->clearCart($request->user());

            return redirect()->away($session->url);
        } catch (RuntimeException $exception) {
            $orderService->rollbackDraftOnlineOrder($order);

            return to_route('cart.show')->withErrors([
                'order' => $exception->getMessage(),
            ]);
        } catch (\Throwable $throwable) {
            $orderService->rollbackDraftOnlineOrder($order);

            report($throwable);

            return to_route('cart.show')->withErrors([
                'order' => 'No se ha podido iniciar el pago online en este momento.',
            ]);
        }
    }

    public function cancel(Request $request, Order $order, OrderService $orderService): RedirectResponse
    {
        $this->ensureCustomer($request);
        $this->ensureOrderBelongsToUser($request, $order);

        $validated = $request->validate([
            'cancel_reason' => ['required', 'string', 'max:1000'],
        ], [
            'cancel_reason.required' => 'Indica el motivo de la cancelacion.',
        ]);

        try {
            $orderService->cancel($order, $validated['cancel_reason']);
        } catch (RuntimeException $exception) {
            return to_route('orders.show', $order)->withErrors([
                'order' => $exception->getMessage(),
            ]);
        }

        return to_route('orders.show', $order)->with('order_status', 'El pedido se ha cancelado correctamente.');
    }

    public function switchToStorePayment(Request $request, Order $order, OrderService $orderService): RedirectResponse
    {
        $this->ensureCustomer($request);
        $this->ensureOrderBelongsToUser($request, $order);

        try {
            $orderService->switchToStorePayment($order);
        } catch (RuntimeException $exception) {
            return $this->redirectToOrderContext($request, $order)->withErrors([
                'order' => $exception->getMessage(),
            ]);
        }

        return $this->redirectToOrderContext($request, $order)
            ->with('order_status', 'El pedido queda pendiente de pago en tienda.');
    }

    protected function ensureCustomer(Request $request): void
    {
        abort_if($request->user()->isAdmin(), 403);
    }

    protected function ensureOrderBelongsToUser(Request $request, Order $order): void
    {
        abort_if($order->user_id !== $request->user()->id, 404);
    }

    protected function redirectToOrderContext(Request $request, Order $order): RedirectResponse
    {
        $redirectTo = $request->string('redirect_to')->toString();

        if ($redirectTo !== '' && str_starts_with($redirectTo, '/') && ! str_starts_with($redirectTo, '//')) {
            return redirect()->to($redirectTo);
        }

        return to_route('orders.show', $order);
    }
}
