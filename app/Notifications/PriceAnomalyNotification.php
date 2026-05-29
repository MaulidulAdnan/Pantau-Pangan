<?php

namespace App\Notifications;

use App\Models\Price;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PriceAnomalyNotification extends Notification
{
    use Queueable;

    public function __construct(public Price $price, public float $avgPrice) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $product = $this->price->product->name;
        $market = $this->price->market->name;
        return (new MailMessage)
            ->subject("⚠️ Anomali Harga Terdeteksi - {$product}")
            ->greeting('Halo Admin!')
            ->line("Harga anomali terdeteksi untuk produk **{$product}** di **{$market}**.")
            ->line("Harga input: Rp " . number_format($this->price->price, 0, ',', '.'))
            ->line("Rata-rata normal: Rp " . number_format($this->avgPrice, 0, ',', '.'))
            ->action('Review Harga', url('/admin/prices'))
            ->line('Silakan review dan ambil tindakan yang diperlukan.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'price_anomaly',
            'message' => "Harga anomali: {$this->price->product->name} di {$this->price->market->name}",
            'price_id' => $this->price->id,
            'product_slug' => $this->price->product->slug ?? null,
            'input_price' => $this->price->price,
            'avg_price' => $this->avgPrice,
        ];
    }
}
