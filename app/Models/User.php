<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'karma',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    /**
     * Get the communities created by the user.
     */
    public function createdCommunities()
    {
        return $this->hasMany(Community::class, 'created_by');
    }
    
    /**
     * Get the communities the user is a member of.
     */
    public function communities()
    {
        return $this->belongsToMany(Community::class)
            ->withPivot('role')
            ->withTimestamps();
    }
    
    /**
     * Get the communities the user moderates.
     */
    public function moderatedCommunities()
    {
        return $this->belongsToMany(Community::class)
            ->wherePivot('role', 'moderator')
            ->withTimestamps();
    }
    
    /**
     * Get the posts created by the user.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    
    /**
     * Get the comments created by the user.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    /**
     * Get the votes cast by the user.
     */
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
    
    /**
     * Get the bans issued by the user.
     */
    public function issuedBans()
    {
        return $this->hasMany(Ban::class, 'banned_by');
    }
    
    /**
     * Get the bans received by the user.
     */
    public function receivedBans()
    {
        return $this->hasMany(Ban::class);
    }
}
