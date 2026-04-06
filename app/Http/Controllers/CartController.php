<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CartController extends Controller
{
    public function show(Request $request): View
    {
        $this->ensureCustomer($request);

        $items = $request->user()
            ->cartItems()
            ->with('product.category')
            ->latest()
            ->get();

        $summary = $this->summarizeCartItems($items);
        $hasAvailabilityIssues = $items->contains(function (CartItem $item): bool {
            return ! $item->product->is_active || $item->product->qty < $item->quantity;
        });

        return view('cart.show', [
            'items' => $items,
            'itemsCount' => $summary['itemsCount'],
            'subtotal' => $summary['subtotalRaw'],
            'hasAvailabilityIssues' => $hasAvailabilityIssues,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->ensureCustomer($request);

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ], [
            'quantity.min' => 'Debes anadir al menos una unidad.',
        ]);

        $product = Product::query()->findOrFail($validated['product_id']);

        if (! $product->is_active) {
            return $this->cartErrorResponse($request, 'Este producto ya no esta disponible.');
        }

        if ($product->qty < 1) {
            return $this->cartErrorResponse($request, 'Este producto esta sin stock.');
        }

        $item = $request->user()
            ->cartItems()
            ->where('product_id', $product->id)
            ->first();

        $requestedQuantity = $validated['quantity'] + ($item?->quantity ?? 0);

        if ($requestedQuantity > $product->qty) {
            return $this->cartErrorResponse(
                $request,
                "Solo quedan {$product->qty} unidades disponibles para {$product->name}."
            );
        }

        if ($item) {
            $item->update([
                'quantity' => $requestedQuantity,
            ]);
        } else {
            $request->user()->cartItems()->create([
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
            ]);
        }

        if ($request->user()->cart_created_at === null) {
            $request->user()->forceFill([
                'cart_created_at' => now(),
            ])->save();
        }

        $message = "{$product->name} se ha anadido al carrito.";

        if ($request->expectsJson()) {
            return response()->json(array_merge([
                'message' => $message,
            ], $this->cartSummaryPayload($request)));
        }

        return back()->with('cart_status', $message);
    }

    public function update(Request $request, CartItem $item): RedirectResponse|JsonResponse
    {
        $this->ensureCustomer($request);
        $this->ensureCartItemBelongsToUser($request, $item);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ], [
            'quantity.min' => 'La cantidad minima es 1.',
        ]);

        $product = $item->product;

        if (! $product->is_active) {
            return $this->cartUpdateErrorResponse($request, 'Este producto ya no esta disponible.');
        }

        if ($validated['quantity'] > $product->qty) {
            return $this->cartUpdateErrorResponse(
                $request,
                "Solo quedan {$product->qty} unidades disponibles para {$product->name}."
            );
        }

        $item->update([
            'quantity' => $validated['quantity'],
        ]);

        if ($request->expectsJson()) {
            return response()->json(array_merge([
                'message' => 'La cantidad del producto se ha actualizado.',
                'itemId' => $item->id,
                'itemQuantity' => $item->quantity,
            ], $this->cartSummaryPayload($request)));
        }

        return to_route('cart.show')->with('cart_status', 'La cantidad del producto se ha actualizado.');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        $this->ensureCustomer($request);
        $this->ensureCartItemBelongsToUser($request, $item);

        $productName = $item->product->name;
        $item->delete();

        $this->clearCartCreatedAtIfEmpty($request);

        return to_route('cart.show')->with('cart_status', "{$productName} se ha eliminado del carrito.");
    }

    public function clear(Request $request): RedirectResponse
    {
        $this->ensureCustomer($request);

        $request->user()->cartItems()->delete();
        $request->user()->forceFill([
            'cart_created_at' => null,
        ])->save();

        return to_route('cart.show')->with('cart_status', 'Has vaciado el carrito.');
    }

    protected function ensureCartItemBelongsToUser(Request $request, CartItem $item): void
    {
        $item->loadMissing('product');

        abort_if($item->user_id !== $request->user()->id, 404);
    }

    protected function clearCartCreatedAtIfEmpty(Request $request): void
    {
        if ($request->user()->cartItems()->exists()) {
            return;
        }

        $request->user()->forceFill([
            'cart_created_at' => null,
        ])->save();
    }

    protected function cartErrorResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 422);
        }

        return back()->withErrors([
            'cart' => $message,
        ]);
    }

    protected function cartUpdateErrorResponse(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 422);
        }

        return to_route('cart.show')->withErrors([
            'cart' => $message,
        ]);
    }

    protected function ensureCustomer(Request $request): void
    {
        abort_if($request->user()->isAdmin(), 403);
    }

    /**
     * @return array{itemsCount:int, lineCount:int, subtotalRaw:string, subtotalFormatted:string}
     */
    protected function summarizeCartItems(Collection $items): array
    {
        $itemsCount = (int) $items->sum('quantity');
        $lineCount = $items->count();
        $subtotal = $items->sum(fn (CartItem $item): float => (float) $item->line_total);

        return [
            'itemsCount' => $itemsCount,
            'lineCount' => $lineCount,
            'subtotalRaw' => number_format($subtotal, 2, '.', ''),
            'subtotalFormatted' => number_format($subtotal, 2, ',', '.'),
        ];
    }

    /**
     * @return array{cartItemsCount:int, cartLineCount:int, cartSubtotal:string}
     */
    protected function cartSummaryPayload(Request $request): array
    {
        $summary = $this->summarizeCartItems(
            $request->user()
                ->cartItems()
                ->with('product')
                ->get()
        );

        return [
            'cartItemsCount' => $summary['itemsCount'],
            'cartLineCount' => $summary['lineCount'],
            'cartSubtotal' => $summary['subtotalFormatted'],
        ];
    }
}
