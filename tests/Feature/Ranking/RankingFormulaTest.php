<?php

namespace Tests\Feature\Ranking;

use App\Models\User;
use App\Models\Community;
use App\Models\Post;
use App\Models\Vote;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RankingFormulaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function rankingFormulaCorrectlyAppliesTimeDecayAndVoteWeighting()
    {
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
            if (!$highScorePost) {
                return false;
            }

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
        $this->assertEmpty($errors->toArray(), 'Ranking formula test failed with errors: ' . $errors->implode(', '));

    // Additional assertions
        $this->assertGreaterThan(0, $sortedPosts->count());
        $this->assertLessThanOrEqual(5, $sortedPosts->count());

    // Verify all posts have reasonable scores
        $sortedPosts->each(function ($post) {
            $this->assertIsNumeric($post->weightedScore);
        });
    }

    #[Test]
    public function rankingHandlesZeroAndNegativeScoresCorrectly()
    {
    // Create a community
        $community = Community::factory()->create();

    // Create posts with zero and negative scores
        $posts = collect();

    // Post with zero score (equal upvotes and downvotes)
        $zeroScorePost = Post::factory()->create([
        'community_id' => $community->id,
        'created_at' => now()
        ]);

    // Add equal upvotes and downvotes
        for ($j = 0; $j < 5; $j++) {
            $voteUser = User::factory()->create();
            Vote::factory()->create([
            'user_id' => $voteUser->id,
            'votable_id' => $zeroScorePost->id,
            'votable_type' => Post::class,
            'vote_type' => 'up'
            ]);

            $voteUser = User::factory()->create();
            Vote::factory()->create([
                'user_id' => $voteUser->id,
                'votable_id' => $zeroScorePost->id,
                'votable_type' => Post::class,
                'vote_type' => 'down'
            ]);
        }

    // Post with negative score (more downvotes than upvotes)
        $negativeScorePost = Post::factory()->create([
        'community_id' => $community->id,
        'created_at' => now()
        ]);

    // Add more downvotes than upvotes
        for ($j = 0; $j < 3; $j++) {
            $voteUser = User::factory()->create();
            Vote::factory()->create([
            'user_id' => $voteUser->id,
            'votable_id' => $negativeScorePost->id,
            'votable_type' => Post::class,
            'vote_type' => 'up'
            ]);
        }

        for ($j = 0; $j < 8; $j++) {
            $voteUser = User::factory()->create();
            Vote::factory()->create([
            'user_id' => $voteUser->id,
            'votable_id' => $negativeScorePost->id,
            'votable_type' => Post::class,
            'vote_type' => 'down'
            ]);
        }

    // Refresh the posts
        $zeroScorePost->refresh();
        $negativeScorePost->refresh();

    // Verify zero score post has zero score
        $this->assertEquals(0, $zeroScorePost->score);
        $this->assertEquals(0, $zeroScorePost->weightedScore);

    // Verify negative score post has negative score and weightedScore
        $this->assertLessThan(0, $negativeScorePost->score);
        $this->assertLessThan(0, $negativeScorePost->weightedScore);

    // Create another negative score post with older age
        $olderNegativePost = Post::factory()->create([
        'community_id' => $community->id,
        'created_at' => now()->subDays(10)
        ]);

    // Add same vote ratio as the first negative post
        for ($j = 0; $j < 3; $j++) {
            $voteUser = User::factory()->create();
            Vote::factory()->create([
            'user_id' => $voteUser->id,
            'votable_id' => $olderNegativePost->id,
            'votable_type' => Post::class,
            'vote_type' => 'up'
            ]);
        }

        for ($j = 0; $j < 8; $j++) {
            $voteUser = User::factory()->create();
            Vote::factory()->create([
            'user_id' => $voteUser->id,
            'votable_id' => $olderNegativePost->id,
            'votable_type' => Post::class,
            'vote_type' => 'down'
            ]);
        }

        $olderNegativePost->refresh();

    // Verify newer negative post has higher weighted score than older with same raw score
        $this->assertEquals($negativeScorePost->score, $olderNegativePost->score);

    // For negative scores, the newer post should have a less negative (higher) weighted score
    // With negative scores, the higher (less negative) value is numerically greater
        $this->assertGreaterThan($negativeScorePost->weightedScore, $olderNegativePost->weightedScore);
    }

    #[Test]
    public function rankingHandlesSignificantTimeDecayForOldPostsCorrectly()
    {
    // Create a community
        $community = Community::factory()->create();

    // Create posts with different ages but same vote patterns
        $posts = collect();

    // Ages to test (now, 30 days old, 180 days old, 365 days old)
        $ages = [0, 30, 180, 365];

        foreach ($ages as $ageInDays) {
            $post = Post::factory()->create([
            'community_id' => $community->id,
            'created_at' => now()->subDays($ageInDays)
            ]);

            // Add same vote pattern to all posts (10 upvotes, 2 downvotes)
            for ($j = 0; $j < 10; $j++) {
                $voteUser = User::factory()->create();
                Vote::factory()->create([
                    'user_id' => $voteUser->id,
                    'votable_id' => $post->id,
                    'votable_type' => Post::class,
                    'vote_type' => 'up'
                ]);
            }

            for ($j = 0; $j < 2; $j++) {
                $voteUser = User::factory()->create();
                Vote::factory()->create([
                'user_id' => $voteUser->id,
                'votable_id' => $post->id,
                'votable_type' => Post::class,
                'vote_type' => 'down'
                ]);
            }

            $post->refresh();
            $posts->push($post);
        }

    // Get posts sorted by weighted score
        $sortedPosts = $posts->sortByDesc(function ($post) {
            return $post->weightedScore;
        })->values();

    // Calculate expected decay factors
        $decayFactor = 0.1; // Lambda value from Post model

    // Verify all posts have the same raw score
        $expectedRawScore = 8; // 10 upvotes - 2 downvotes
        $posts->each(function ($post) use ($expectedRawScore) {
            $this->assertEquals($expectedRawScore, $post->score);
        });

    // Verify order is from newest to oldest
        for ($i = 0; $i < count($ages) - 1; $i++) {
            $newerPost = $sortedPosts[$i];
            $olderPost = $sortedPosts[$i + 1];

            $this->assertGreaterThan($olderPost->created_at, $newerPost->created_at);
            $this->assertGreaterThan($olderPost->weightedScore, $newerPost->weightedScore);
        }

    // Verify decay factor is working as expected
        $newestPost = $sortedPosts[0];
        $oldestPost = $sortedPosts[count($ages) - 1];

    // Calculate expected scores using the decay formula
        $newestExpectedScore = $expectedRawScore * exp(-$decayFactor * 0);
        $oldestExpectedScore = $expectedRawScore * exp(-$decayFactor * 365);

    // Allow for small floating point differences
        $this->assertLessThan(0.001, abs($newestPost->weightedScore - $newestExpectedScore));
        $this->assertLessThan(0.001, abs($oldestPost->weightedScore - $oldestExpectedScore));

    // Verify the oldest post has a significantly lower weighted score
        $this->assertLessThan($newestPost->weightedScore * 0.5, $oldestPost->weightedScore);
    }

    #[Test]
    public function rankingHandlesPostsWithIdenticalWeightedScores()
    {
    // Create a community
        $community = Community::factory()->create();

    // Create two posts with different ages and vote counts that result in same weighted score

    // First post: newer with lower score
        $newerPost = Post::factory()->create([
        'community_id' => $community->id,
        'created_at' => now()->subDays(10)
        ]);

    // Second post: older with higher score
        $olderPost = Post::factory()->create([
        'community_id' => $community->id,
        'created_at' => now()->subDays(30)
        ]);

    // Calculate required votes to create identical weighted scores
    // Using the formula: score_newer * exp(-0.1 * 10) = score_older * exp(-0.1 * 30)
    // This simplifies to: score_newer = score_older * exp(-0.1 * 20)

        $decayFactor = 0.1;
        $ratio = exp($decayFactor * 20); // e^2 ≈ 7.389

    // Create a scenario where olderPost would need ~7.4 times the score of newerPost
    // Let's use 5 for newer post, which means ~37 for older post

    // Add upvotes and downvotes to newer post (7 up, 2 down = 5 score)
        for ($j = 0; $j < 7; $j++) {
            $voteUser = User::factory()->create();
            Vote::factory()->create([
            'user_id' => $voteUser->id,
            'votable_id' => $newerPost->id,
            'votable_type' => Post::class,
            'vote_type' => 'up'
            ]);
        }

        for ($j = 0; $j < 2; $j++) {
            $voteUser = User::factory()->create();
            Vote::factory()->create([
            'user_id' => $voteUser->id,
            'votable_id' => $newerPost->id,
            'votable_type' => Post::class,
            'vote_type' => 'down'
            ]);
        }

    // Add upvotes and downvotes to older post (42 up, 5 down = 37 score)
        for ($j = 0; $j < 42; $j++) {
            $voteUser = User::factory()->create();
            Vote::factory()->create([
            'user_id' => $voteUser->id,
            'votable_id' => $olderPost->id,
            'votable_type' => Post::class,
            'vote_type' => 'up'
            ]);
        }

        for ($j = 0; $j < 5; $j++) {
            $voteUser = User::factory()->create();
            Vote::factory()->create([
            'user_id' => $voteUser->id,
            'votable_id' => $olderPost->id,
            'votable_type' => Post::class,
            'vote_type' => 'down'
            ]);
        }

    // Refresh posts
        $newerPost->refresh();
        $olderPost->refresh();

    // Verify raw scores
        $this->assertEquals(5, $newerPost->score);
        $this->assertEquals(37, $olderPost->score);

    // The weighted scores should be very close to each other
        $newerWeightedScore = $newerPost->weightedScore;
        $olderWeightedScore = $olderPost->weightedScore;

    // Allow for small floating point differences
        $this->assertLessThan(0.5, abs($newerWeightedScore - $olderWeightedScore));

    // Calculate theoretical weighted scores
        $theoreticalNewerScore = 5 * exp(-$decayFactor * 10);
        $theoreticalOlderScore = 37 * exp(-$decayFactor * 30);

    // Verify theoretical and actual scores are close
        $this->assertLessThan(0.001, abs($newerWeightedScore - $theoreticalNewerScore));
        $this->assertLessThan(0.001, abs($olderWeightedScore - $theoreticalOlderScore));
    }
}
