<?php

namespace Tests\Browser\Comments;

use App\Models\User;
use App\Models\Community;
use App\Models\Post;
use App\Models\Comment;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CommentSystemTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test that an authenticated user can comment on a post.
     */
    public function test_authenticated_user_can_comment(): void
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
                    
                    // Find the comment form
                    ->assertPresent('@comment-form')
                    ->screenshot('comment-form')
                    
                    // Add a comment
                    ->type('@comment-content', 'This is a test comment.')
                    ->screenshot('comment-form-filled')
                    ->press('Submit')
                    
                    // Wait for the comment to appear
                    ->waitForText('This is a test comment.')
                    ->assertSee('This is a test comment.')
                    ->screenshot('comment-submitted');
        });
    }

    /**
     * Test comment form validation.
     */
    public function test_comment_form_validation(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $post = Post::factory()->create([
            'community_id' => $community->id
        ]);

        $this->browse(function (Browser $browser) use ($user, $post) {
            $browser->loginAs($user)
                    ->visit(route('posts.show', $post))
                    
                    // Submit empty comment
                    ->press('Submit')
                    ->waitForText('The content field is required')
                    ->assertSee('The content field is required')
                    ->screenshot('comment-validation-error');
        });
    }

    /**
     * Test that comments are displayed correctly.
     */
    public function test_comments_displayed_correctly(): void
    {
        // Create a user, community, post, and comments
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $post = Post::factory()->create([
            'community_id' => $community->id
        ]);
        
        // Create multiple comments
        $comment1 = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'First test comment'
        ]);
        
        $comment2 = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'Second test comment'
        ]);

        $this->browse(function (Browser $browser) use ($post, $comment1, $comment2) {
            $browser->visit(route('posts.show', $post))
                    
                    // Check if comments are displayed
                    ->assertSee('First test comment')
                    ->assertSee('Second test comment')
                    ->screenshot('comments-displayed');
        });
    }

    /**
     * Test that nested comments are displayed correctly.
     */
    public function test_nested_comments_display(): void
    {
        // Create a user, community, post, and comments
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $post = Post::factory()->create([
            'community_id' => $community->id
        ]);
        
        // Create parent comment
        $parentComment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'Parent comment'
        ]);
        
        // Create child comment
        $childComment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_comment_id' => $parentComment->id,
            'content' => 'Child comment'
        ]);

        $this->browse(function (Browser $browser) use ($post, $parentComment, $childComment) {
            $browser->visit(route('posts.show', $post))
                    
                    // Check if parent comment is displayed
                    ->assertSee('Parent comment')
                    
                    // Check if child comment is displayed and properly nested
                    ->assertSee('Child comment')
                    ->assertPresent('@comment-' . $childComment->id)
                    ->assertPresent('@nested-comment-' . $childComment->id)
                    ->screenshot('nested-comments');
        });
    }

    /**
     * Test that a user can reply to a comment.
     */
    public function test_user_can_reply_to_comment(): void
    {
        // Create a user, community, post, and comment
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $post = Post::factory()->create([
            'community_id' => $community->id
        ]);
        
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'Original comment'
        ]);

        $this->browse(function (Browser $browser) use ($user, $post, $comment) {
            $browser->loginAs($user)
                    ->visit(route('posts.show', $post))
                    
                    // Click reply button on the comment
                    ->click('@reply-button-' . $comment->id)
                    ->waitForPresent('@reply-form-' . $comment->id)
                    ->screenshot('reply-form')
                    
                    // Add a reply
                    ->type('@reply-content-' . $comment->id, 'This is a reply to the comment.')
                    ->press('Reply')
                    
                    // Wait for the reply to appear
                    ->waitForText('This is a reply to the comment.')
                    ->assertSee('This is a reply to the comment.')
                    ->screenshot('reply-submitted');
        });
    }

    /**
     * Test that a user can delete their own comment.
     */
    public function test_user_can_delete_own_comment(): void
    {
        // Create a user, community, post, and comment
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $post = Post::factory()->create([
            'community_id' => $community->id
        ]);
        
        $comment = Comment::factory()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'Comment to be deleted'
        ]);

        $this->browse(function (Browser $browser) use ($user, $post, $comment) {
            $browser->loginAs($user)
                    ->visit(route('posts.show', $post))
                    
                    // Verify comment exists
                    ->assertSee('Comment to be deleted')
                    ->screenshot('comment-before-delete')
                    
                    // Click delete button
                    ->click('@delete-comment-' . $comment->id)
                    
                    // Confirm deletion in modal
                    ->waitForText('Are you sure you want to delete this comment?')
                    ->press('Delete')
                    
                    // Verify comment is removed
                    ->waitUntilMissing('@comment-' . $comment->id)
                    ->assertDontSee('Comment to be deleted')
                    ->screenshot('comment-after-delete');
        });
    }
}
