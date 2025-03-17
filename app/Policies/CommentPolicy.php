<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view comments
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Comment $comment): bool
    {
        return true; // Anyone can view a comment
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create a comment
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        // Only the comment author can update it
        return $comment->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // The comment author can delete it
        if ($comment->user_id === $user->id) {
            return true;
        }
        
        // Or post author
        if ($comment->post->user_id === $user->id) {
            return true;
        }
        
        // Or community moderators
        return $comment->post->community->moderators()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comment $comment): bool
    {
        // The comment author can restore it
        if ($comment->user_id === $user->id) {
            return true;
        }
        
        // Or community moderators
        return $comment->post->community->moderators()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        // Only community moderators can permanently delete a comment
        return $comment->post->community->moderators()->where('user_id', $user->id)->exists();
    }
}
