<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display the specified user.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        // Calculate karma from post votes
        $postKarma = Post::where('user_id', $user->id)
            ->withCount(['votes as upvotes_count' => function ($query) {
                $query->where('vote_type', 'up');
            }])
            ->withCount(['votes as downvotes_count' => function ($query) {
                $query->where('vote_type', 'down');
            }])
            ->get()
            ->sum(function ($post) {
                return $post->upvotes_count - $post->downvotes_count;
            });
        
        // Calculate karma from comment votes
        $commentKarma = Comment::where('user_id', $user->id)
            ->withCount(['votes as upvotes_count' => function ($query) {
                $query->where('vote_type', 'up');
            }])
            ->withCount(['votes as downvotes_count' => function ($query) {
                $query->where('vote_type', 'down');
            }])
            ->get()
            ->sum(function ($comment) {
                return $comment->upvotes_count - $comment->downvotes_count;
            });
        
        // Add karma to user data
        $userData = $user->toArray();
        $userData['karma'] = $postKarma + $commentKarma;
        
        // If username is not set, use name as a fallback
        if (empty($userData['username'])) {
            $userData['username'] = $userData['name'];
        }
        
        return response()->json($userData);
    }
    
    /**
     * Update the specified user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        // Check if the authenticated user is the same as the user being updated
        if (Auth::id() !== $user->id) {
            return response()->json(['message' => 'Unauthorized action'], 403);
        }
        
        $validated = $request->validate([
            'username' => ['sometimes', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
        ]);
        
        // Hash password if it's being updated
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        
        // Sync name and username fields
        if (isset($validated['username'])) {
            $validated['name'] = $validated['username'];
        }
        
        $user->update($validated);
        
        return response()->json(['message' => 'Profile updated successfully']);
    }
    
    /**
     * Get the user's posts.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function posts(User $user)
    {
        $posts = Post::where('user_id', $user->id)
            ->with(['community', 'user'])
            ->withCount(['comments', 'votes as upvotes_count' => function ($query) {
                $query->where('vote_type', 'up');
            }, 'votes as downvotes_count' => function ($query) {
                $query->where('vote_type', 'down');
            }])
            ->paginate(15);
        
        return response()->json($posts);
    }
    
    /**
     * Get the user's comments.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function comments(User $user)
    {
        $comments = Comment::where('user_id', $user->id)
            ->with(['post', 'user'])
            ->withCount(['votes as upvotes_count' => function ($query) {
                $query->where('vote_type', 'up');
            }, 'votes as downvotes_count' => function ($query) {
                $query->where('vote_type', 'down');
            }])
            ->paginate(15);
        
        return response()->json($comments);
    }
}
