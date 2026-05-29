<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WarningNotification extends Notification
{
    use Queueable;

    public function __construct(public string $warningMessage) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Peringatan dari Pantau Pangan')
            ->greeting("Halo {$notifiable->name},")
            ->line($this->warningMessage)
            ->line('Pelanggaran berulang dapat mengakibatkan akun di-mute atau di-suspend.')
            ->action('Baca Ketentuan', url('/'))
            ->line('Terima kasih atas pengertiannya.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'warning',
            'message' => $this->warningMessage,
        ];
    }
}
