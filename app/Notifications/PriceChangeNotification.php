<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PriceChangeNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $productName,
        public string $direction,
        public float $percentage,
        public float $currentPrice
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $icon = $this->direction === 'naik' ? '📈' : '📉';
        return (new MailMessage)
            ->subject("{$icon} Harga {$this->productName} {$this->direction} {$this->percentage}%")
            ->greeting("Halo {$notifiable->name}!")
            ->line("Harga **{$this->productName}** {$this->direction} **{$this->percentage}%** minggu ini.")
            ->line("Harga saat ini: Rp " . number_format($this->currentPrice, 0, ',', '.'))
            ->action('Lihat Detail', url('/products'))
            ->line('Pantau terus harga pangan favoritmu!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'price_change',
            'message' => "Harga {$this->productName} {$this->direction} {$this->percentage}% minggu ini.",
            'product_name' => $this->productName,
            'direction' => $this->direction,
            'percentage' => $this->percentage,
            'current_price' => $this->currentPrice,
        ];
    }
}
