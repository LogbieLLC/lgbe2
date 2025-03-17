<?php

namespace Tests\Browser\Posts;

use App\Models\User;
use App\Models\Community;
use App\Models\Post;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PostVotingTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test that an authenticated user can upvote a post.
     */
    public function test_authenticated_user_can_upvote(): void
    {
        // Create a user, community, and post
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $post = Post::factory()->create([
            'community_id' => $community->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs($user)
                    ->visit(route('posts.show', $post))
                    ->assertSee($post->title)
                    
                    // Check initial vote count
                    ->assertSee('Score: 0')
                    ->screenshot('post-before-upvote')
                    
                    // Click upvote button
                    ->click('@upvote-button')
                    ->waitUntilMissing('.opacity-50') // Wait for AJAX to complete
                    
                    // Verify vote count increased
                    ->assertSee('Score: 1')
                    ->screenshot('post-after-upvote');
        });
    }

    /**
     * Test that an authenticated user can downvote a post.
     */
    public function test_authenticated_user_can_downvote(): void
    {
        // Create a user, community, and post
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $post = Post::factory()->create([
            'community_id' => $community->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs($user)
                    ->visit(route('posts.show', $post))
                    ->assertSee($post->title)
                    
                    // Check initial vote count
                    ->assertSee('Score: 0')
                    ->screenshot('post-before-downvote')
                    
                    // Click downvote button
                    ->click('@downvote-button')
                    ->waitUntilMissing('.opacity-50') // Wait for AJAX to complete
                    
                    // Verify vote count decreased
                    ->assertSee('Score: -1')
                    ->screenshot('post-after-downvote');
        });
    }

    /**
     * Test that a vote toggles when clicking the same button again.
     */
    public function test_vote_toggles_when_clicking_same_button(): void
    {
        // Create a user, community, and post
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $post = Post::factory()->create([
            'community_id' => $community->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs($user)
                    ->visit(route('posts.show', $post))
                    
                    // Upvote the post
                    ->click('@upvote-button')
                    ->waitUntilMissing('.opacity-50')
                    ->assertSee('Score: 1')
                    ->screenshot('post-upvoted')
                    
                    // Click upvote again to remove the vote
                    ->click('@upvote-button')
                    ->waitUntilMissing('.opacity-50')
                    
                    // Verify vote was removed
                    ->assertSee('Score: 0')
                    ->screenshot('post-vote-removed');
        });
    }

    /**
     * Test that unauthenticated users cannot vote.
     */
    public function test_unauthenticated_users_cannot_vote(): void
    {
        // Create a community and post
        $community = Community::factory()->create();
        $post = Post::factory()->create([
            'community_id' => $community->id
        ]);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->visit(route('posts.show', $post))
                    ->assertSee($post->title)
                    
                    // Check if vote buttons redirect to login
                    ->click('@upvote-button')
                    ->assertPathIs('/login')
                    ->screenshot('upvote-redirects-to-login')
                    
                    ->visit(route('posts.show', $post))
                    ->click('@downvote-button')
                    ->assertPathIs('/login')
                    ->screenshot('downvote-redirects-to-login');
        });
    }

    /**
     * Test that vote count updates correctly.
     */
    public function test_vote_count_updates_correctly(): void
    {
        // Create users, community, and post
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $community = Community::factory()->create();
        $post = Post::factory()->create([
            'community_id' => $community->id
        ]);

        $this->browse(function (Browser $browser) use ($user1, $user2, $post) {
            // First user upvotes
            $browser->loginAs($user1)
                    ->visit(route('posts.show', $post))
                    ->click('@upvote-button')
                    ->waitUntilMissing('.opacity-50')
                    ->assertSee('Score: 1')
                    ->screenshot('first-user-upvote')
                    ->logout();
            
            // Second user downvotes
            $browser->loginAs($user2)
                    ->visit(route('posts.show', $post))
                    ->click('@downvote-button')
                    ->waitUntilMissing('.opacity-50')
                    ->assertSee('Score: 0') // 1 upvote - 1 downvote = 0
                    ->screenshot('second-user-downvote');
        });
    }
}
