<?php

use App\Models\User;
use App\Models\Community;
use App\Models\Post;
use App\Models\Vote;
use Illuminate\Support\Facades\Log;

test('ranking formula correctly applies time decay and vote weighting', function () {
    // Create a community
    $community = Community::factory()->create();
    
    // Create posts with different timestamps and vote counts
    $posts = collect();
    
    // Generate 5 test posts with controlled vote counts and timestamps
    for ($i = 0; $i < 5; $i++) {
        // Create posts with different ages
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'created_at' => now()->subDays($i) // 0 to 4 days old
        ]);
        
        // Add controlled number of upvotes and downvotes
        $upvotes = ($i % 2 === 0) ? 10 : 5; // Alternating high/low upvotes
        $downvotes = 2;
        
        // Create upvotes with different users
        for ($j = 0; $j < $upvotes; $j++) {
            $voteUser = User::factory()->create();
            Vote::factory()->create([
                'user_id' => $voteUser->id,
                'votable_id' => $post->id,
                'votable_type' => Post::class,
                'vote_type' => 'up'
            ]);
        }
        
        // Create downvotes with different users
        for ($j = 0; $j < $downvotes; $j++) {
            $voteUser = User::factory()->create();
            Vote::factory()->create([
                'user_id' => $voteUser->id,
                'votable_id' => $post->id,
                'votable_type' => Post::class,
                'vote_type' => 'down'
            ]);
        }
        
        // Refresh the post to ensure vote counts are accurate
        $post->refresh();
        $posts->push($post);
    }
    
    // Get posts sorted by weighted score (using the model's attribute)
    $sortedPosts = $posts->sortByDesc(function ($post) {
        return $post->weightedScore;
    })->values();
    
    // Verify ranking properties
    $errors = collect();
    
    // 1. Check that newer posts with equal scores rank higher than older posts
    $newerPost = $posts->first(); // The newest post (0 days old)
    $olderPost = $posts->last();  // The oldest post (4 days old)
    
    // Force them to have the same raw score for testing time decay
    $newerPostScore = $newerPost->score;
    $olderPostScore = $olderPost->score;
    
    // If the newer post has a higher weighted score despite having the same raw score,
    // then time decay is working correctly
    if ($newerPost->weightedScore <= $olderPost->weightedScore && $newerPostScore === $olderPostScore) {
        $errors->push("Time decay not working: newer post with same score should rank higher");
    }
    
    // 2. Check that posts with higher vote counts rank higher when age is similar
    $highScorePost = $posts->first(function ($post) {
        return $post->score > 7; // A post with high score (10 upvotes - 2 downvotes = 8)
    });
    
    $lowScorePost = $posts->first(function ($post) use ($highScorePost) {
        if (!$highScorePost) return false;
        
        // Find a post with similar age but lower score
        return $post->score < 5 && // (5 upvotes - 2 downvotes = 3)
               abs($post->created_at->diffInDays($highScorePost->created_at)) <= 1;
    });
    
    if ($highScorePost && $lowScorePost) {
        $highScoreIndex = $sortedPosts->search(function ($post) use ($highScorePost) {
            return $post->id === $highScorePost->id;
        });
        
        $lowScoreIndex = $sortedPosts->search(function ($post) use ($lowScorePost) {
            return $post->id === $lowScorePost->id;
        });
        
        if ($highScoreIndex > $lowScoreIndex) {
            $errors->push("Vote weighting not working: high-score post ranked lower than low-score post of similar age");
        }
    }
    
    // 3. Verify the ranking is consistent (no anomalies)
    $previousWeightedScore = null;
    $sortedPosts->each(function ($post) use (&$previousWeightedScore, &$errors) {
        $currentWeightedScore = $post->weightedScore;
        
        if ($previousWeightedScore !== null && $currentWeightedScore > $previousWeightedScore) {
            $errors->push("Ranking anomaly: Post {$post->id} has higher weighted score than previous post in sorted list");
        }
        
        $previousWeightedScore = $currentWeightedScore;
    });
    
    // Log any errors
    if ($errors->isNotEmpty()) {
        Log::error('Ranking formula test errors:', [
            'errors' => $errors->toArray()
        ]);
    }
    
    // Assert that no errors were found
    expect($errors)->toBeEmpty('Ranking formula test failed with errors: ' . $errors->implode(', '));
    
    // Additional assertions
    expect($sortedPosts->count())->toBeGreaterThan(0);
    expect($sortedPosts->count())->toBeLessThanOrEqual(5);
    
    // Verify all posts have reasonable scores
    $sortedPosts->each(function ($post) {
        expect($post->weightedScore)->toBeNumeric();
    });
});
