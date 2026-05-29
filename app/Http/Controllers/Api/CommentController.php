<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Notifications\CommentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function index(Request $request, $productId)
    {
        $query = Comment::where('product_id', $productId)
            ->whereNull('parent_id')
            ->where('is_deleted', false);

        if ($request->has('region') && $request->region) {
            $query->whereHas('region', function ($q) use ($request) {
                $q->where('slug', $request->region);
            });
        }

        $comments = $query->with(['user', 'region', 'replies' => function ($q) {
                $q->where('is_deleted', false)->with('user')->latest();
            }])
            ->latest()
            ->paginate(15);

        return response()->json($comments);
    }

    public function store(Request $request, $productId)
    {
        $user = auth('api')->user();
        if ($user->status === 'muted') {
            return response()->json(['message' => 'Akun Anda di-mute, tidak dapat berkomentar'], 403);
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:2000',
            'region_slug' => 'nullable|string|exists:regions,slug',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $regionId = null;
        if ($request->filled('region_slug')) {
            $region = \App\Models\Region::where('slug', $request->region_slug)->first();
            $regionId = $region ? $region->id : null;
        }

        $comment = Comment::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'region_id' => $regionId,
            'body' => $request->body,
        ]);

        return response()->json([
            'message' => 'Komentar berhasil ditambahkan',
            'comment' => $comment->load('user', 'region'),
        ], 201);
    }

    public function reply(Request $request, $commentId)
    {
        $user = auth('api')->user();
        if ($user->status === 'muted') {
            return response()->json(['message' => 'Akun Anda di-mute, tidak dapat berkomentar'], 403);
        }

        $parent = Comment::findOrFail($commentId);

        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $comment = Comment::create([
            'user_id' => $user->id,
            'product_id' => $parent->product_id,
            'parent_id' => $parent->id,
            'body' => $request->body,
        ]);

        // Notify comment owner about the reply
        if ($parent->user_id !== $user->id) {
            $parent->user->notify(new CommentNotification($parent, 'reply', $user->name));
        }

        return response()->json([
            'message' => 'Reply berhasil ditambahkan',
            'comment' => $comment->load('user'),
        ], 201);
    }

    public function destroy($commentId)
    {
        $user = auth('api')->user();
        $comment = Comment::findOrFail($commentId);

        if ($comment->user_id !== $user->id && !$user->isAdmin()) {
            return response()->json(['message' => 'Anda hanya dapat menghapus komentar sendiri'], 403);
        }

        $comment->update(['is_deleted' => true]);
        return response()->json(['message' => 'Komentar berhasil dihapus']);
    }

    public function like($commentId)
    {
        $user = auth('api')->user();
        $comment = Comment::findOrFail($commentId);

        $existing = CommentLike::where('user_id', $user->id)->where('comment_id', $commentId)->first();

        if ($existing) {
            $existing->delete();
            $comment->decrement('likes_count');
            return response()->json(['message' => 'Like dihapus', 'liked' => false, 'likes_count' => $comment->fresh()->likes_count]);
        }

        CommentLike::create(['user_id' => $user->id, 'comment_id' => $commentId]);
        $comment->increment('likes_count');

        // Notify comment owner about the like
        if ($comment->user_id !== $user->id) {
            $comment->user->notify(new CommentNotification($comment, 'like', $user->name));
        }

        return response()->json(['message' => 'Komentar dilike', 'liked' => true, 'likes_count' => $comment->fresh()->likes_count]);
    }
}
