<?php

namespace App\Services;

use App\Enums\OrderDocumentFormat;
use App\Models\Order;
use App\Models\OrderItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class OrderDocumentService
{
    public function downloadForCustomer(Order $order, OrderDocumentFormat $format): Response
    {
        return $this->download($order, $format, false);
    }

    public function downloadForAdmin(Order $order, OrderDocumentFormat $format): Response
    {
        return $this->download($order, $format, true);
    }

    protected function download(Order $order, OrderDocumentFormat $format, bool $showCustomerMetadata): Response
    {
        $order->loadMissing(['user', 'items.product']);

        $pdf = Pdf::loadView($this->viewFor($format), [
            'order' => $order,
            'generatedAt' => now(),
            'showCustomerMetadata' => $showCustomerMetadata,
        ]);

        $this->configurePaper($pdf, $order, $format, $showCustomerMetadata);

        return $pdf->download($this->filenameFor($order, $format));
    }

    protected function viewFor(OrderDocumentFormat $format): string
    {
        return match ($format) {
            OrderDocumentFormat::TICKET => 'orders.documents.ticket',
        };
    }

    protected function configurePaper(
        DomPdfWrapper $pdf,
        Order $order,
        OrderDocumentFormat $format,
        bool $showCustomerMetadata,
    ): void {
        $pdf->setPaper([
            0,
            0,
            $this->millimetersToPoints(80),
            $this->ticketHeightFor($order, $showCustomerMetadata),
        ]);
    }

    protected function filenameFor(Order $order, OrderDocumentFormat $format): string
    {
        $baseName = Str::of($order->order_number)
            ->lower()
            ->replaceMatches('/[^a-z0-9\-]+/', '-')
            ->trim('-')
            ->toString();

        return $format->filePrefix().'-'.$baseName.'.pdf';
    }

    protected function ticketHeightFor(Order $order, bool $showCustomerMetadata): float
    {
        $itemsHeight = $order->items->sum(function (OrderItem $item): int {
            $titleLines = max(1, (int) ceil(Str::length($item->product_name) / 28));

            return 58 + (($titleLines - 1) * 12);
        });

        $metaHeight = 360;

        if ($order->hasDiscount()) {
            $metaHeight += 18;
        }

        if ($showCustomerMetadata) {
            $metaHeight += 54;
        }

        if ($order->payment_reference) {
            $metaHeight += 24;
        }

        if ($order->notes) {
            $metaHeight += 16 + (max(1, (int) ceil(Str::length($order->notes) / 30)) * 12);
        }

        if ($order->status->value === 'cancelled' && $order->cancel_reason) {
            $metaHeight += 18 + (max(1, (int) ceil(Str::length($order->cancel_reason) / 30)) * 12);
        }

        return max(640.0, (float) ($metaHeight + $itemsHeight));
    }

    protected function millimetersToPoints(float $millimeters): float
    {
        return $millimeters * 2.8346456693;
    }
}
