<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\CommentReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // GET /api/comments
    public function index(Request $request)
    {
        $perPage = min((int)$request->get('per_page', 20), 100);
 
        $comments = Comment::whereNull('parent_id')
            ->with([
                'user:id,name',
                'likes',
                'reports',
                'replies' => fn($q) => $q->with(['user:id,name', 'likes']),
            ])
            ->latest()
            ->paginate($perPage);
 
        $userId = Auth::id();
 
        $comments->getCollection()->transform(function ($c) use ($userId) {
            return $this->formatComment($c, $userId);
        });
 
        return response()->json($comments);
    }

    // POST /api/comments
   public function store(Request $request)
    {
        $request->validate([
            'body'      => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);
 
        $comment = Comment::create([
            'user_id'   => Auth::id(),
            'body'      => $request->body,
            'parent_id' => $request->parent_id,
        ]);
 
        $comment->load(['user:id,name', 'likes', 'reports']);
 
        return response()->json([
            'success' => true,
            'comment' => $this->formatComment($comment, Auth::id()),
        ]);
    }

    // DELETE /api/comments/{id}
    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);
        $user    = Auth::user();
 
        if ($comment->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
 
        $comment->delete();
 
        return response()->json(['success' => true]);
    }
 

    // POST /api/comments/{id}/react
      public function react(Request $request, $id)
    {
        $request->validate(['value' => 'required|in:1,-1']);
 
        $comment = Comment::findOrFail($id);
        $userId  = Auth::id();
 
        $existing = CommentLike::where('user_id', $userId)
            ->where('comment_id', $id)
            ->first();
 
        if ($existing) {
            if ($existing->value === (int)$request->value) {
                $existing->delete();
            } else {
                $existing->update(['value' => $request->value]);
            }
        } else {
            CommentLike::create([
                'user_id'    => $userId,
                'comment_id' => $id,
                'value'      => $request->value,
            ]);
        }
 
        $comment->refresh();
        $comment->load('likes');
 
        $userLike = $comment->likes()->where('user_id', $userId)->first();
 
        return response()->json([
            'success'        => true,
            'likes_count'    => $comment->likes()->where('value', 1)->count(),
            'dislikes_count' => $comment->likes()->where('value', -1)->count(),
            'user_reaction'  => $userLike ? $userLike->value : null,
        ]);
    }
    // POST /api/comments/{id}/report
    public function report(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);
        $userId  = Auth::id();
 
        $exists = CommentReport::where('user_id', $userId)
            ->where('comment_id', $id)
            ->exists();
 
        if ($exists) {
            return response()->json(['message' => 'Déjà signalé'], 422);
        }
 
        CommentReport::create([
            'user_id'    => $userId,
            'comment_id' => $id,
            'reason'     => $request->reason,
        ]);
 
        return response()->json(['success' => true]);
    }



     private function formatComment(Comment $c, ?int $userId): array
    {
        $userLike = $userId
            ? $c->likes->firstWhere('user_id', $userId)
            : null;
 
        $replies = $c->replies->map(function ($r) use ($userId) {
            $rLike = $userId ? $r->likes->firstWhere('user_id', $userId) : null;
            return [
                'id'             => $r->id,
                'body'           => $r->body,
                'parent_id'      => $r->parent_id,
                'user_id'        => $r->user_id,
                'user_name'      => $r->user?->name ?? '?',
                'created_at'     => $r->created_at,
                'likes_count'    => $r->likes->where('value', 1)->count(),
                'dislikes_count' => $r->likes->where('value', -1)->count(),
                'user_reaction'  => $rLike?->value,
                'reports_count'  => 0,
                'replies'        => [],
            ];
        })->values()->toArray();
 
        return [
            'id'             => $c->id,
            'body'           => $c->body,
            'parent_id'      => $c->parent_id,
            'user_id'        => $c->user_id,
            'user_name'      => $c->user?->name ?? '?',
            'created_at'     => $c->created_at,
            'likes_count'    => $c->likes->where('value', 1)->count(),
            'dislikes_count' => $c->likes->where('value', -1)->count(),
            'user_reaction'  => $userLike?->value,
            'reports_count'  => $c->reports->count(),
            'replies_count'  => count($replies),
            'replies'        => $replies,
        ];
    }

}