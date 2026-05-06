<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderReadyForPickupNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public Order $order,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->success()
            ->subject('Tu pedido '.$this->order->order_number.' ya esta listo para recoger')
            ->greeting('Hola, '.$notifiable->name.'!')
            ->line('Tu pedido '.$this->order->order_number.' ya esta listo para recoger.')
            ->line('Recogida a nombre de: '.$this->order->pickup_name)
            ->line('Importe total: '.$this->formattedTotal())
            ->line('Puedes revisar el detalle completo del pedido desde tu cuenta.')
            ->action('Ver pedido', route('orders.show', $this->order));

        if ($this->order->usesStorePayment() && ! $this->order->isPaid()) {
            $message->line('Recuerda que el pago se realizara al recoger el pedido.');
        } else {
            $message->line('Tu pedido ya figura como pagado y solo queda recogerlo.');
        }

        return $message->salutation('Gracias por confiar en Bazar Digital.');
    }

    public function failed(Throwable $exception): void
    {
        Log::error('No se pudo enviar el correo de pedido listo para recoger.', [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'user_id' => $this->order->user_id,
            'exception' => $exception->getMessage(),
        ]);
    }

    private function formattedTotal(): string
    {
        return number_format((float) $this->order->total, 2, ',', '.').' EUR';
    }
}
