<?php

use App\Models\User;
use App\Models\Community;
use App\Models\Post;
use App\Models\Vote;
use Illuminate\Support\Facades\Log;

test('ranking formula correctly applies time decay and vote weighting', function () {
    echo "\nStarting ranking formula test...\n";
    $user = User::factory()->create();
    $community = Community::factory()->create();
    
    // Create posts with different timestamps and vote counts
    $posts = collect();
    
    // Generate 1,000 test cases
    echo "Generating 1,000 test posts with votes...\n";
    $progressStep = 100;
    for ($i = 0; $i < 1000; $i++) {
        if ($i % $progressStep === 0) {
            echo "Created {$i} posts...\n";
        }
        $post = Post::factory()->create([
            'community_id' => $community->id,
            'created_at' => now()->subHours(rand(0, 168)) // Random time up to 7 days ago
        ]);
        
        // Add random number of upvotes and downvotes
        $upvotes = rand(0, 1000);
        $downvotes = rand(0, 1000);
        
        // Create votes
        for ($j = 0; $j < $upvotes; $j++) {
            Vote::create([
                'user_id' => User::factory()->create()->id,
                'votable_id' => $post->id,
                'votable_type' => Post::class,
                'vote_type' => 'up'
            ]);
        }
        
        for ($j = 0; $j < $downvotes; $j++) {
            Vote::create([
                'user_id' => User::factory()->create()->id,
                'votable_id' => $post->id,
                'votable_type' => Post::class,
                'vote_type' => 'down'
            ]);
        }
        
        $posts->push($post);
    }
    
    echo "All posts created. Sorting posts by score...\n";
    // Get posts sorted by score
    $sortedPosts = $posts->sortByDesc(function ($post) {
        $score = $post->votes()->where('vote_type', 'up')->count() - 
                 $post->votes()->where('vote_type', 'down')->count();
        $timeElapsed = now()->diffInHours($post->created_at);
        $decayFactor = exp(-0.1 * $timeElapsed); // λ = 0.1 for 10% decay per hour
        
        return $score * $decayFactor;
    });
    
    echo "Posts sorted. Verifying ranking properties...\n";
    // Verify ranking properties
    $errors = collect();
    
    echo "1. Checking that newer posts with high scores rank higher than older posts with same score...\n";
    // 1. Check that newer posts with high scores rank higher than older posts with same score
    $newerHighScore = $sortedPosts->first(function ($post) {
        $score = $post->votes()->where('vote_type', 'up')->count() - 
                 $post->votes()->where('vote_type', 'down')->count();
        return $score > 500;
    });
    
    $olderHighScore = $sortedPosts->first(function ($post) use ($newerHighScore) {
        if (!$newerHighScore) return false;
        
        $score = $post->votes()->where('vote_type', 'up')->count() - 
                 $post->votes()->where('vote_type', 'down')->count();
        return $score > 500 &&
               $post->created_at < $newerHighScore->created_at;
    });
    
    if ($newerHighScore && $olderHighScore) {
        $newerIndex = $sortedPosts->search(function ($post) use ($newerHighScore) {
            return $post->id === $newerHighScore->id;
        });
        $olderIndex = $sortedPosts->search(function ($post) use ($olderHighScore) {
            return $post->id === $olderHighScore->id;
        });
        
        if ($newerIndex > $olderIndex) {
            $errors->push("Newer high-score post ranked lower than older high-score post");
        }
    }
    
    echo "2. Checking that posts with higher vote counts rank higher than older posts...\n";
    // 2. Check that posts with higher vote counts rank higher than older posts
    $highVotePost = $sortedPosts->first(function ($post) {
        $score = $post->votes()->where('vote_type', 'up')->count() - 
                 $post->votes()->where('vote_type', 'down')->count();
        return $score > 800;
    });
    
    $lowVotePost = $sortedPosts->first(function ($post) use ($highVotePost) {
        if (!$highVotePost) return false;
        
        $score = $post->votes()->where('vote_type', 'up')->count() - 
                 $post->votes()->where('vote_type', 'down')->count();
        return $score < 200 &&
               $post->created_at < $highVotePost->created_at;
    });
    
    if ($highVotePost && $lowVotePost) {
        $highVoteIndex = $sortedPosts->search(function ($post) use ($highVotePost) {
            return $post->id === $highVotePost->id;
        });
        $lowVoteIndex = $sortedPosts->search(function ($post) use ($lowVotePost) {
            return $post->id === $lowVotePost->id;
        });
        
        if ($highVoteIndex > $lowVoteIndex) {
            $errors->push("High-vote post ranked lower than low-vote post");
        }
    }
    
    echo "3. Verifying time decay is working correctly...\n";
    // 3. Verify time decay is working correctly
    $recentPosts = $sortedPosts->take(100);
    $oldPosts = $sortedPosts->slice(-100);
    
    $recentAvgScore = $recentPosts->avg(function ($post) {
        return $post->votes()->where('vote_type', 'up')->count() -
               $post->votes()->where('vote_type', 'down')->count();
    });
    
    $oldAvgScore = $oldPosts->avg(function ($post) {
        return $post->votes()->where('vote_type', 'up')->count() -
               $post->votes()->where('vote_type', 'down')->count();
    });
    
    if ($recentAvgScore < $oldAvgScore) {
        $errors->push("Recent posts have lower average score than old posts");
    }
    
    echo "4. Checking for any anomalies in the ranking...\n";
    // 4. Check for any anomalies in the ranking
    $previousScore = null;
    $sortedPosts->each(function ($post) use (&$previousScore, &$errors) {
        $score = $post->votes()->where('vote_type', 'up')->count() -
                 $post->votes()->where('vote_type', 'down')->count();
        $timeElapsed = now()->diffInHours($post->created_at);
        $decayFactor = exp(-0.1 * $timeElapsed);
        $currentScore = $score * $decayFactor;
        
        if ($previousScore !== null && $currentScore > $previousScore) {
            $errors->push("Score anomaly detected: Post {$post->id} has higher score than previous post");
        }
        
        $previousScore = $currentScore;
    });
    
    echo "Checking for errors...\n";
    // Log any errors
    if ($errors->isNotEmpty()) {
        echo "Found " . $errors->count() . " errors in ranking test.\n";
        Log::error('Ranking formula test errors:', [
            'errors' => $errors->toArray()
        ]);
    }
    
    // Assert that no errors were found
    $this->assertTrue($errors->isEmpty(), 'Ranking formula test failed with errors: ' . $errors->implode(', '));
    echo "No errors found in ranking test.\n";
    
    // Additional assertions for specific cases
    $this->assertGreaterThan(0, $sortedPosts->count(), 'No posts were sorted');
    $this->assertLessThanOrEqual(1000, $sortedPosts->count(), 'Too many posts were sorted');
    
    echo "Verifying the top 10 posts have reasonable scores...\n";
    // Verify the top 10 posts have reasonable scores
    $topPosts = $sortedPosts->take(10);
    $topPosts->each(function ($post) {
        $score = $post->votes()->where('vote_type', 'up')->count() -
                 $post->votes()->where('vote_type', 'down')->count();
        $timeElapsed = now()->diffInHours($post->created_at);
        $decayFactor = exp(-0.1 * $timeElapsed);
        $finalScore = $score * $decayFactor;
        
        $this->assertGreaterThan(0, $finalScore, "Post {$post->id} in top 10 has non-positive score");
    });
    
    echo "Ranking formula test completed successfully!\n";
});
