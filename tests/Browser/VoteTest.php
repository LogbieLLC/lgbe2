<?php

namespace Tests\Browser;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\PostPage;
use Tests\DuskTestCase;

class VoteTest extends DuskTestCase
{
    use DatabaseMigrations;
    
    public function test_user_can_vote_on_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        
        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs($user)
                    ->visit(new PostPage($post))
                    ->screenshot('post_page')
                    ->assertSee($post->title)
                    ->click('@upvote')
                    ->pause(1000) // Wait for AJAX request
                    ->screenshot('after_upvote')
                    ->assertSee('1'); // Score should be 1
                    
            // Verify vote is recorded in database
            $this->assertDatabaseHas('votes', [
                'user_id' => $user->id,
                'votable_id' => $post->id,
                'votable_type' => Post::class,
                'vote_type' => 'up',
            ]);
            
            // Test changing vote
            $browser->click('@downvote')
                    ->pause(1000) // Wait for AJAX request
                    ->screenshot('after_downvote')
                    ->assertSee('-1'); // Score should be -1
                    
            // Verify vote is updated in database
            $this->assertDatabaseHas('votes', [
                'user_id' => $user->id,
                'votable_id' => $post->id,
                'votable_type' => Post::class,
                'vote_type' => 'down',
            ]);
        });
    }
}
