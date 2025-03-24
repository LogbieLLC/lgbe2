<?php

namespace App\Policies;

use App\Models\Community;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommunityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view communities
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Community $community): bool
    {
        return true; // Anyone can view a community
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create a community
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Community $community): bool
    {
        // Only moderators or the creator can update a community
        if ($community->created_by === $user->id) {
            return true;
        }

        return $community->moderators()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Community $community): bool
    {
        // Only the creator can delete a community
        return $community->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Community $community): bool
    {
        // Only the creator can restore a community
        return $community->created_by === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Community $community): bool
    {
        // Only the creator can permanently delete a community
        return $community->created_by === $user->id;
    }

    /**
     * Determine whether the user can moderate the community.
     */
    public function moderate(User $user, Community $community): bool
    {
        // Check if the user is a moderator or the creator
        if ($community->created_by === $user->id) {
            return true;
        }

        return $community->moderators()->where('user_id', $user->id)->exists();
    }
}
