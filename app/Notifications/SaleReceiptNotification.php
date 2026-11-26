<?php

namespace App\Notifications;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SaleReceiptNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Sale $sale) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $items = $this->sale->receiptItems();
        $sale = $items->first();
        $total = $items->sum(fn (Sale $item) => (float) $item->total_price);

        $message = (new MailMessage)
            ->subject('Your CaféFlow receipt '.$sale->transactionReference())
            ->greeting('Hello '.$notifiable->full_name.',')
            ->line('Thank you. Your purchase was recorded successfully.')
            ->line('Receipt reference: '.$sale->transactionReference());

        foreach ($items as $item) {
            $message->line($item->product->name.' × '.number_format($item->quantity).' — PHP '.number_format((float) $item->total_price, 2));
        }

        return $message
            ->line('Total: PHP '.number_format($total, 2))
            ->line('Cashier: '.$sale->staff->full_name)
            ->line('Purchased on '.$sale->created_at->timezone(config('counterly.display_timezone'))->format('F j, Y, g:i A').' PHT.')
            ->salutation('CaféFlow');
    }
}
