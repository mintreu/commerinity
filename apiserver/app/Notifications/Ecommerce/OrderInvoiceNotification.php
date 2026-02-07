<?php

declare(strict_types=1);

namespace App\Notifications\Ecommerce;

use App\Models\Ecommerce\Order;
use App\Services\Ecommerce\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class OrderInvoiceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if ($notifiable->email) {
            $channels[] = 'mail';
        }

        if (method_exists($notifiable, 'pushSubscriptions') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invoiceService = app(InvoiceService::class);
        $pdf = $invoiceService->pdf($this->order);

        return (new MailMessage)
            ->subject('Invoice for Order #'.$this->order->order_number)
            ->greeting("Hi {$notifiable->name},")
            ->line('Your order has been confirmed and the invoice is attached.')
            ->action('View Order', $this->orderUrl())
            ->attachData($pdf->output(), $this->filename(), [
                'mime' => 'application/pdf',
            ])
            ->salutation('Regards,'."\n".'Team '.config('app.name'));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'order_invoice',
            'title' => 'Order confirmed',
            'message' => 'Order #'.$this->order->order_number.' confirmed. Invoice is ready.',
            'action_url' => $this->orderUrl(),
            'action_text' => 'View Order',
        ];
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('Order confirmed')
            ->icon('/icon-192x192.png')
            ->body('Order #'.$this->order->order_number.' confirmed. Invoice is ready.')
            ->badge('/badge-72x72.png')
            ->options(['TTL' => 3600])
            ->data(['url' => $this->orderUrl()]);
    }

    private function orderUrl(): string
    {
        return rtrim((string) config('app.client_url'), '/').'/order/'.$this->order->uuid;
    }

    private function filename(): string
    {
        return 'invoice-'.$this->order->order_number.'.pdf';
    }
}
