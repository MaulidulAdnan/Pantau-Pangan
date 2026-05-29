<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;

class CommentModerationController extends Controller
{
    public function flagged()
    {
        $comments = Comment::whereHas('reports')
            ->with('user', 'product', 'reports.user')
            ->latest()
            ->paginate(15);

        return response()->json($comments);
    }

    public function deleteComment($commentId)
    {
        $comment = Comment::findOrFail($commentId);
        $comment->update(['is_deleted' => true]);
        return response()->json(['message' => 'Komentar berhasil dihapus']);
    }

    public function warn($commentId)
    {
        $comment = Comment::with('user')->findOrFail($commentId);
        $user = $comment->user;

        $user->notify(new \App\Notifications\WarningNotification(
            "Komentar Anda melanggar ketentuan komunitas. Hindari penggunaan kata tidak pantas."
        ));

        return response()->json(['message' => "Warning dikirim ke {$user->name}"]);
    }
}
