<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Community extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'rules',
        'created_by',
    ];
    
    /**
     * Get the user who created the community.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the members of the community.
     */
    public function members()
    {
        return $this->belongsToMany(User::class)
            ->using(CommunityUser::class)
            ->withPivot('role')
            ->withTimestamps();
    }
    
    /**
     * Get the moderators of the community.
     */
    public function moderators()
    {
        return $this->belongsToMany(User::class)
            ->using(CommunityUser::class)
            ->wherePivot('role', 'moderator')
            ->withTimestamps();
    }
    
    /**
     * Get the posts in the community.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    
    /**
     * Get the bans in the community.
     */
    public function bans()
    {
        return $this->hasMany(Ban::class);
    }
}
