<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class StripeCheckoutController extends Controller
{
    public function pay(Request $request, Order $order, StripeCheckoutService $stripeCheckoutService): RedirectResponse
    {
        $this->ensureCustomer($request);
        $this->ensureOrderBelongsToUser($request, $order);

        abort_unless($order->payment_method === PaymentMethod::ONLINE, 404);
        abort_if($order->payment_status === PaymentStatus::PAID, 403);
        abort_if(in_array($order->status, [OrderStatus::COMPLETED, OrderStatus::CANCELLED], true), 403);

        try {
            $session = $stripeCheckoutService->createSession($order->loadMissing('items.product', 'user'));

            return redirect()->away($session->url);
        } catch (RuntimeException $exception) {
            return to_route('orders.show', $order)->withErrors([
                'order' => $exception->getMessage(),
            ]);
        } catch (\Throwable $throwable) {
            report($throwable);

            return to_route('orders.show', $order)->withErrors([
                'order' => 'No se ha podido iniciar el pago online en este momento.',
            ]);
        }
    }

    public function success(Request $request, Order $order, StripeCheckoutService $stripeCheckoutService): RedirectResponse
    {
        $this->ensureCustomer($request);
        $this->ensureOrderBelongsToUser($request, $order);

        $sessionId = $request->string('session_id')->toString();

        if ($sessionId === '') {
            return to_route('orders.show', $order)->withErrors([
                'order' => 'Stripe no ha devuelto una sesión válida para confirmar el pago.',
            ]);
        }

        try {
            $stripeCheckoutService->syncSuccessfulCheckout($order, $sessionId);
        } catch (RuntimeException $exception) {
            return to_route('orders.show', $order)->withErrors([
                'order' => $exception->getMessage(),
            ]);
        } catch (\Throwable $throwable) {
            report($throwable);

            return to_route('orders.show', $order)->withErrors([
                'order' => 'No se ha podido confirmar el pago online en este momento.',
            ]);
        }

        return to_route('orders.show', $order)->with('order_status', 'Pago confirmado correctamente.');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->ensureCustomer($request);
        $this->ensureOrderBelongsToUser($request, $order);

        return to_route('orders.show', $order)->withErrors([
            'order' => 'Has cancelado el proceso de pago online. Puedes reintentarlo cuando quieras.',
        ]);
    }

    protected function ensureCustomer(Request $request): void
    {
        abort_if($request->user()->isAdmin(), 403);
    }

    protected function ensureOrderBelongsToUser(Request $request, Order $order): void
    {
        abort_if($order->user_id !== $request->user()->id, 404);
    }
}
