<?php

declare(strict_types=1);

namespace App\Notifications\Mlm;

use App\Services\MoneyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification sent to admins after batch commission processing
 */
final class CommissionProcessingCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $stats
     */
    public function __construct(
        public readonly array $stats,
        public readonly bool $failed = false,
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->failed
            ? "[FAILED] Commission Batch Processing - {$this->stats['process_type']}"
            : "[SUCCESS] Commission Batch Processing - {$this->stats['process_type']}";

        $mail = (new MailMessage)
            ->subject($subject);

        if ($this->failed) {
            $mail->error()
                ->line("Commission batch processing ({$this->stats['process_type']}) has FAILED.")
                ->line('Error: '.($this->stats['error'] ?? 'Unknown error'))
                ->line("Started at: {$this->stats['started_at']}")
                ->line('Failed at: '.($this->stats['failed_at'] ?? 'N/A'));
        } else {
            $mail->success()
                ->line("Commission batch processing ({$this->stats['process_type']}) completed successfully.")
                ->line('**Summary:**')
                ->line("- Items Processed: {$this->stats['items_processed']}")
                ->line("- Commissions Created: {$this->stats['commissions_created']}")
                ->line('- Total Amount: '.MoneyService::format($this->stats['total_amount']))
                ->line("- Duration: {$this->stats['duration_seconds']} seconds");

            if (! empty($this->stats['errors'])) {
                $errorCount = count($this->stats['errors']);
                $mail->line('')
                    ->line("**Warnings:** {$errorCount} items had errors during processing.");
            }
        }

        return $mail
            ->action('View Dashboard', url('/admin/mlm/commissions'))
            ->line('This is an automated notification from the MLM Commission System.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'mlm_commission_batch',
            'process_type' => $this->stats['process_type'],
            'failed' => $this->failed,
            'items_processed' => $this->stats['items_processed'] ?? 0,
            'commissions_created' => $this->stats['commissions_created'] ?? 0,
            'total_amount' => $this->stats['total_amount'] ?? 0,
            'duration_seconds' => $this->stats['duration_seconds'] ?? null,
            'error_count' => count($this->stats['errors'] ?? []),
            'error_message' => $this->stats['error'] ?? null,
            'started_at' => $this->stats['started_at'] ?? null,
            'completed_at' => $this->stats['completed_at'] ?? null,
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'mlm_commission_batch';
    }
}
