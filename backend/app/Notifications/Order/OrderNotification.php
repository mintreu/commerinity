<?php

namespace App\Notifications\Order;

use App\Casts\OrderStatusCast;
use App\Models\Order\Order;
use Filament\Facades\Filament;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;
    protected string $status;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->status = $order->status->value ?? (string) $order->status;
    }

    /**
     * Determine which channels to send the notification through
     */
    public function via(object $notifiable): array
    {
        return match ($this->status) {
            OrderStatusCast::PROCESSING->value => ['mail'],
            OrderStatusCast::PENDING->value => ['mail'],
            OrderStatusCast::PAYMENT_FAILED->value => ['mail', 'database'],
            OrderStatusCast::CONFIRM->value => ['mail', 'database'],
            OrderStatusCast::REVIEW->value => ['mail'],
            OrderStatusCast::ACCEPTED->value => ['mail', 'database'],
            OrderStatusCast::READY_TO_SHIP->value => ['mail', 'database'],
            OrderStatusCast::IN_TRANSIT->value => ['mail', 'database'],
            OrderStatusCast::COMPLETED->value => ['mail', 'database'],
            OrderStatusCast::CANCELLED->value => ['mail', 'database'],
            OrderStatusCast::REFUNDED->value => ['mail', 'database'],
            default => ['database'],
        };
    }

    /**
     * Build the mail message dynamically based on order status
     */
    public function toMail(object $notifiable): MailMessage
    {
        return match ($this->status) {
            OrderStatusCast::PROCESSING->value => $this->processingMail($notifiable),
            OrderStatusCast::PENDING->value => $this->pendingMail($notifiable),
            OrderStatusCast::PAYMENT_FAILED->value => $this->paymentFailedMail($notifiable),
            OrderStatusCast::CONFIRM->value => $this->confirmMail($notifiable),
            OrderStatusCast::REVIEW->value => $this->reviewMail($notifiable),
            OrderStatusCast::ACCEPTED->value => $this->acceptedMail($notifiable),
            OrderStatusCast::READY_TO_SHIP->value => $this->readyToShipMail($notifiable),
            OrderStatusCast::IN_TRANSIT->value => $this->inTransitMail($notifiable),
            OrderStatusCast::COMPLETED->value => $this->completedMail($notifiable),
            OrderStatusCast::CANCELLED->value => $this->cancelledMail($notifiable),
            OrderStatusCast::REFUNDED->value => $this->refundedMail($notifiable),
        };
    }

    /**
     * Build the database notification payload
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Order status updated')
            ->body("Your order #{$this->order->uuid} is now **{$this->order->status->getLabel()}**.")
            ->icon($this->order->status->getIcon())
            ->color($this->order->status->getColor())
            ->actions([
                \Filament\Notifications\Actions\Action::make('view_order')
                    ->label('View Order')
                    ->url(fn () => route('filament.resources.orders.view', ['record' => $this->order->uuid])),
            ])
            ->getDatabaseMessage();
    }

    /* --------------------------------------------------------------------------
        Below are private template methods for each order status
       -------------------------------------------------------------------------- */

    private function processingMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order #'.$this->order->uuid.' is Processing')
            ->greeting("Hi {$notifiable->name},")
            ->line('Your order is now being processed.')
            ->line('You’ll be notified once it’s confirmed or shipped.')
            ->salutation('Thank you for shopping with '.config('app.name'));
    }

    private function pendingMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order #'.$this->order->uuid.' is Pending Confirmation')
            ->greeting("Hi {$notifiable->name},")
            ->line('Your order is pending confirmation. We’ll notify you once it’s approved.')
            ->salutation('Thank you for your patience.');
    }

    private function paymentFailedMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Failed for Order #'.$this->order->uuid)
            ->greeting("Hi {$notifiable->name},")
            ->line('Unfortunately, your payment for this order was not successful.')
            ->line('Please try again or choose another payment method.')
            ->action('Retry Payment', route('orders.payment.retry', ['order' => $this->order->uuid]))
            ->salutation('Team '.config('app.name'));
    }

    private function confirmMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order #'.$this->order->uuid.' Confirmed')
            ->greeting("Hi {$notifiable->name},")
            ->line('Your order has been confirmed and will be prepared for shipping soon.')
            ->salutation('Team '.config('app.name'));
    }

    private function reviewMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Order #'.$this->order->uuid.' is Under Review')
            ->greeting("Hi {$notifiable->name},")
            ->line('Our team is reviewing your order for verification.')
            ->line('We’ll notify you once it’s accepted or needs action.')
            ->salutation('Team '.config('app.name'));
    }

    private function acceptedMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order #'.$this->order->uuid.' Accepted')
            ->greeting("Hi {$notifiable->name},")
            ->line('Your order has been accepted and will be shipped soon.')
            ->salutation('Thank you for your purchase!');
    }

    private function readyToShipMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order #'.$this->order->uuid.' Ready to Ship')
            ->greeting("Hi {$notifiable->name},")
            ->line('Your order is packed and ready to ship.')
            ->line('You’ll receive tracking information shortly.')
            ->salutation('Team '.config('app.name'));
    }

    private function inTransitMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order #'.$this->order->uuid.' is On the Way!')
            ->greeting("Hi {$notifiable->name},")
            ->line('Good news! Your order is now in transit.')
            ->line('Tracking ID: '.$this->order->tracking_id)
            ->salutation('Thank you for shopping with us.');
    }

    private function completedMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order #'.$this->order->uuid.' Delivered Successfully')
            ->greeting("Hi {$notifiable->name},")
            ->line('Your order has been successfully delivered.')
            ->line('We hope you enjoy your purchase!')
            ->action('Leave Feedback', route('orders.feedback', ['order' => $this->order->uuid]))
            ->salutation('Team '.config('app.name'));
    }

    private function cancelledMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order #'.$this->order->uuid.' Cancelled')
            ->greeting("Hi {$notifiable->name},")
            ->line('Your order has been cancelled as per your request or due to payment issues.')
            ->line('If this was unexpected, please contact support.')
            ->salutation('Team '.config('app.name'));
    }

    private function refundedMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Refund Processed for Order #'.$this->order->uuid)
            ->greeting("Hi {$notifiable->name},")
            ->line('Your refund for this order has been successfully processed.')
            ->line('The amount will appear in your account within 5–7 business days.')
            ->salutation('Team '.config('app.name'));
    }
}
