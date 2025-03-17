<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view posts
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Post $post): bool
    {
        return true; // Anyone can view a post
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create a post
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        // Only the post author can update it
        if ($post->user_id === $user->id) {
            return true;
        }
        
        // Or community moderators
        return $post->community->moderators()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        // The post author can delete it
        if ($post->user_id === $user->id) {
            return true;
        }
        
        // Or community moderators
        return $post->community->moderators()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        // The post author can restore it
        if ($post->user_id === $user->id) {
            return true;
        }
        
        // Or community moderators
        return $post->community->moderators()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        // Only community moderators can permanently delete a post
        return $post->community->moderators()->where('user_id', $user->id)->exists();
    }
}
