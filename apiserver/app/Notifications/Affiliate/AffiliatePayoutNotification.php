<?php

declare(strict_types=1);

namespace App\Notifications\Affiliate;

use App\Models\Affiliate\AffiliatePayout;
use App\Services\MoneyService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AffiliatePayoutNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly AffiliatePayout $payout
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pdf = Pdf::loadView('invoices.affiliate_payout_invoice', [
            'payout' => $this->payout,
        ])->setPaper('a4')->setWarnings(false);

        return (new MailMessage)
            ->subject('Affiliate Disbursement Ready')
            ->line('Your affiliate disbursement has been processed.')
            ->line('Period: '.$this->payout->period_start.' to '.$this->payout->period_end)
            ->line('Net Amount: '.MoneyService::format($this->payout->net_amount))
            ->line('Thank you for growing with us.')
            ->attachData($pdf->output(), 'affiliate-payout-'.$this->payout->uuid.'.pdf', [
                'mime' => 'application/pdf',
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'affiliate_payout',
            'payout_uuid' => $this->payout->uuid,
            'message' => 'Affiliate disbursement processed. Net: '.MoneyService::format($this->payout->net_amount),
            'period_start' => $this->payout->period_start,
            'period_end' => $this->payout->period_end,
        ];
    }
}
