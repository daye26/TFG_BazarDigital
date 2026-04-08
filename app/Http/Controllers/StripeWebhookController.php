<?php

namespace App\Http\Controllers;

use App\Services\StripeWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeWebhookService $stripeWebhookService): JsonResponse
    {
        try {
            $stripeWebhookService->handle(
                $request->getContent(),
                $request->header('Stripe-Signature'),
            );
        } catch (InvalidArgumentException|JsonException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 400);
        } catch (RuntimeException $exception) {
            report($exception);

            return response()->json([
                'message' => $exception->getMessage(),
            ], 500);
        } catch (\Throwable $throwable) {
            report($throwable);

            return response()->json([
                'message' => 'No se ha podido procesar el webhook de Stripe.',
            ], 500);
        }

        return response()->json([
            'received' => true,
        ]);
    }
}
