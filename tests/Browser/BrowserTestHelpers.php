<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Community;
use App\Models\Post;
use App\Models\Comment;
use Laravel\Dusk\Browser;

/**
 * Helper trait for browser tests.
 * 
 * This trait provides common methods for setting up test data and performing
 * common actions in browser tests.
 */
trait BrowserTestHelpers
{
    /**
     * Create a test community with the given user as creator.
     *
     * @param \App\Models\User|null $user The user to create the community with (optional)
     * @param array $attributes Additional attributes for the community
     * @return \App\Models\Community
     */
    protected function createTestCommunity(?User $user = null, array $attributes = []): Community
    {
        $user = $user ?? User::factory()->create();
        
        $community = Community::factory()->create(array_merge([
            'created_by' => $user->id,
        ], $attributes));
        
        // Make the user a member and moderator of the community
        $community->members()->attach($user->id, ['role' => 'moderator']);
        
        return $community;
    }
    
    /**
     * Create a test post in the given community.
     *
     * @param \App\Models\Community $community The community to create the post in
     * @param \App\Models\User|null $user The user to create the post with (optional)
     * @param array $attributes Additional attributes for the post
     * @return \App\Models\Post
     */
    protected function createTestPost(Community $community, ?User $user = null, array $attributes = []): Post
    {
        $user = $user ?? User::factory()->create();
        
        // Make sure the user is a member of the community
        if (!$community->members()->where('user_id', $user->id)->exists()) {
            $community->members()->attach($user->id, ['role' => 'member']);
        }
        
        return Post::factory()->create(array_merge([
            'community_id' => $community->id,
            'user_id' => $user->id,
        ], $attributes));
    }
    
    /**
     * Create a test comment on the given post.
     *
     * @param \App\Models\Post $post The post to comment on
     * @param \App\Models\User|null $user The user to create the comment with (optional)
     * @param array $attributes Additional attributes for the comment
     * @return \App\Models\Comment
     */
    protected function createTestComment(Post $post, ?User $user = null, array $attributes = []): Comment
    {
        $user = $user ?? User::factory()->create();
        
        return Comment::factory()->create(array_merge([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ], $attributes));
    }
    
    /**
     * Create a test reply to the given comment.
     *
     * @param \App\Models\Comment $parentComment The parent comment
     * @param \App\Models\User|null $user The user to create the reply with (optional)
     * @param array $attributes Additional attributes for the reply
     * @return \App\Models\Comment
     */
    protected function createTestReply(Comment $parentComment, ?User $user = null, array $attributes = []): Comment
    {
        $user = $user ?? User::factory()->create();
        
        return Comment::factory()->create(array_merge([
            'post_id' => $parentComment->post_id,
            'user_id' => $user->id,
            'parent_comment_id' => $parentComment->id,
        ], $attributes));
    }
    
    /**
     * Login a user and navigate to a post.
     *
     * @param \Laravel\Dusk\Browser $browser The browser instance
     * @param \App\Models\User $user The user to login as
     * @param \App\Models\Post $post The post to navigate to
     * @return \Laravel\Dusk\Browser
     */
    protected function loginAndVisitPost(Browser $browser, User $user, Post $post): Browser
    {
        return $browser->loginAs($user)
                       ->visit(route('posts.show', $post))
                       ->assertSee($post->title);
    }
    
    /**
     * Login a user and navigate to a community.
     *
     * @param \Laravel\Dusk\Browser $browser The browser instance
     * @param \App\Models\User $user The user to login as
     * @param \App\Models\Community $community The community to navigate to
     * @return \Laravel\Dusk\Browser
     */
    protected function loginAndVisitCommunity(Browser $browser, User $user, Community $community): Browser
    {
        return $browser->loginAs($user)
                       ->visit(route('communities.show', $community))
                       ->assertSee('r/' . $community->name);
    }
    
    /**
     * Create a unique username.
     *
     * @param string $prefix The prefix for the username
     * @return string
     */
    protected function createUniqueUsername(string $prefix = 'user'): string
    {
        return $prefix . '_' . uniqid();
    }
    
    /**
     * Create a unique email.
     *
     * @param string $prefix The prefix for the email
     * @return string
     */
    protected function createUniqueEmail(string $prefix = 'user'): string
    {
        return $prefix . '_' . uniqid() . '@example.com';
    }
}
