<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'community_id',
        'banned_by',
        'reason',
        'expires_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
    ];
    
    /**
     * Get the user who is banned.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the community the user is banned from.
     */
    public function community()
    {
        return $this->belongsTo(Community::class);
    }
    
    /**
     * Get the moderator who issued the ban.
     */
    public function moderator()
    {
        return $this->belongsTo(User::class, 'banned_by');
    }
    
    /**
     * Determine if the ban is active.
     */
    public function isActive()
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }
}
