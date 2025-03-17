<?php

namespace Tests\Browser;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\PostPage;
use Tests\DuskTestCase;

class CommentTest extends DuskTestCase
{
    use DatabaseMigrations;
    
    public function test_user_can_comment_on_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        
        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs($user)
                    ->visit(new PostPage($post))
                    ->screenshot('post_page_for_comment')
                    ->assertSee($post->title)
                    ->type('@comment-content', 'This is a test comment')
                    ->screenshot('comment_form_filled')
                    ->press('@submit-comment')
                    ->pause(1000) // Wait for AJAX request
                    ->screenshot('after_comment_submission')
                    ->assertSee('This is a test comment');
                    
            // Verify comment is created in database
            $this->assertDatabaseHas('comments', [
                'content' => 'This is a test comment',
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);
        });
    }
}
