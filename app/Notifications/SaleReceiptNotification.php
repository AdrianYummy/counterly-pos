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
        $sale = $this->sale->loadMissing(['product', 'staff']);

        return (new MailMessage)
            ->subject('Your CaféFlow receipt '.$sale->publicReference())
            ->greeting('Hello '.$notifiable->full_name.',')
            ->line('Thank you. Your purchase was recorded successfully.')
            ->line('Receipt reference: '.$sale->publicReference())
            ->line('Product: '.$sale->product->name)
            ->line('Quantity: '.number_format($sale->quantity))
            ->line('Total: PHP '.number_format((float) $sale->total_price, 2))
            ->line('Cashier: '.$sale->staff->full_name)
            ->line('Purchased on '.$sale->created_at->timezone(config('counterly.display_timezone'))->format('F j, Y, g:i A').' PHT.')
            ->salutation('CaféFlow');
    }
}
