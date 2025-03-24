<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CommunityUser extends Pivot
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'community_id',
        'role',
    ];

    /**
     * Get the user that belongs to the community.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the community that the user belongs to.
     */
    public function community()
    {
        return $this->belongsTo(Community::class);
    }
}
