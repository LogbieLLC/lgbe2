<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Post $post)
    {
        $comments = $post->comments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_comment_id')
            ->withCount(['votes', 'replies'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $comments
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Post $post)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        $validated = $request->validate([
            'content' => 'required|string|max:9999',
            'parent_comment_id' => 'nullable|exists:comments,id',
        ]);

        // Check if the parent comment belongs to the same post
        if (isset($validated['parent_comment_id'])) {
            $parentComment = Comment::findOrFail($validated['parent_comment_id']);
            if ($parentComment->post_id !== $post->id) {
                return response()->json(['message' => 'Parent comment does not belong to this post'], 400);
            }
        }

        $comment = $post->comments()->create([
            'content' => $validated['content'],
            'user_id' => Auth::id(),
            'parent_comment_id' => $validated['parent_comment_id'] ?? null,
        ]);

        $comment->load('user');

        return response()->json($comment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        $comment->load(['user', 'post']);

        $replies = $comment->replies()
            ->with(['user'])
            ->withCount('votes')
            ->orderByDesc('created_at')
            ->paginate(15);

        return response()->json([
            'comment' => $comment,
            'replies' => $replies,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        if (Auth::id() !== $comment->user_id) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        $comment->update($validated);

        return response()->json($comment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        if (Auth::id() !== $comment->user_id) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
