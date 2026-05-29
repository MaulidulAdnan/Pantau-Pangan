<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommentNotification extends Notification
{
    use Queueable;

    protected $comment;
    protected $action; // 'like', 'reply'
    protected $actorName;

    public function __construct(Comment $comment, string $action, string $actorName)
    {
        $this->comment = $comment;
        $this->action = $action;
        $this->actorName = $actorName;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $product = $this->comment->product;
        $actionText = $this->action === 'like'
            ? "menyukai komentar Anda"
            : "membalas komentar Anda";

        return [
            'type' => 'comment_' . $this->action,
            'message' => "{$this->actorName} {$actionText} di produk {$product->name}",
            'product_slug' => $product->slug ?? null,
            'product_id' => $product->id,
            'comment_id' => $this->comment->id,
        ];
    }
}
