<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    
    /**
     * Vote on a post.
     */
    public function votePost(Request $request, Post $post)
    {
        $validated = $request->validate([
            'vote_type' => 'required|in:up,down',
        ]);
        
        return $this->handleVote($post, $validated['vote_type']);
    }
    
    /**
     * Vote on a comment.
     */
    public function voteComment(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'vote_type' => 'required|in:up,down',
        ]);
        
        return $this->handleVote($comment, $validated['vote_type']);
    }
    
    /**
     * Handle the vote for a votable model.
     */
    private function handleVote($votable, $voteType)
    {
        $user = Auth::user();
        
        // Check if the user has already voted on this item
        $existingVote = $votable->votes()->where('user_id', $user->id)->first();
        
        DB::beginTransaction();
        
        try {
            if ($existingVote) {
                if ($existingVote->vote_type === $voteType) {
                    // If voting the same way, remove the vote
                    $existingVote->delete();
                    $this->updateKarma($votable->user, $voteType, true);
                    $message = 'Vote removed successfully';
                } else {
                    // If voting differently, update the vote
                    $existingVote->update(['vote_type' => $voteType]);
                    
                    // Update karma: remove old vote effect and add new vote effect
                    $this->updateKarma($votable->user, $existingVote->vote_type, true);
                    $this->updateKarma($votable->user, $voteType, false);
                    
                    $message = 'Vote updated successfully';
                }
            } else {
                // Create a new vote
                $votable->votes()->create([
                    'user_id' => $user->id,
                    'vote_type' => $voteType,
                ]);
                
                $this->updateKarma($votable->user, $voteType, false);
                $message = 'Vote added successfully';
            }
            
            DB::commit();
            
            // Get updated vote counts
            $upVotes = $votable->votes()->where('vote_type', 'up')->count();
            $downVotes = $votable->votes()->where('vote_type', 'down')->count();
            
            return response()->json([
                'message' => $message,
                'up_votes' => $upVotes,
                'down_votes' => $downVotes,
                'score' => $upVotes - $downVotes,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error processing vote: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Update the karma of a user based on a vote.
     */
    private function updateKarma(User $user, $voteType, $isRemoval)
    {
        $karmaChange = $voteType === 'up' ? 1 : -1;
        
        // If removing a vote, reverse the karma change
        if ($isRemoval) {
            $karmaChange = -$karmaChange;
        }
        
        $user->increment('karma', $karmaChange);
    }
}
