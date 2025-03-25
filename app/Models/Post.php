<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'content',
        'type',
        'community_id',
        'user_id',
    ];

    /**
     * Get the user who created the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the community the post belongs to.
     */
    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * Get the comments on the post.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the votes on the post.
     */
    public function votes()
    {
        return $this->morphMany(Vote::class, 'votable');
    }

    /**
     * Calculate the score of the post.
     */
    public function getScoreAttribute()
    {
        $upVotes = $this->votes()->where('vote_type', 'up')->count();
        $downVotes = $this->votes()->where('vote_type', 'down')->count();

        return $upVotes - $downVotes;
    }

    /**
     * Calculate the weighted score of the post based on time decay.
     */
    public function getWeightedScoreAttribute()
    {
        $score = $this->score;
        $ageInDays = $this->created_at->diffInSeconds(now()) / 86400; // Convert seconds to days
        $decayFactor = 0.1; // Lambda value controlling decay rate

        return $score * exp(-$decayFactor * $ageInDays);
    }
}
