<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class PostPage extends Page
{
    protected $post;
    protected $community;
    
    public function __construct($post = null, $community = null)
    {
        $this->post = $post;
        $this->community = $community;
    }
    
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        if ($this->post) {
            return '/posts/' . $this->post->id;
        } elseif ($this->community) {
            return '/communities/' . $this->community->id . '/posts/create';
        }
        
        return '/';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        if ($this->post) {
            $browser->assertSee($this->post->title);
        } elseif ($this->community) {
            $browser->assertSee('Create Post');
        }
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@title' => '#title',
            '@content' => '#content',
            '@type' => '#type',
            '@submit-button' => 'button[type="submit"]',
            '@upvote' => '.upvote-button',
            '@downvote' => '.downvote-button',
            '@comment-content' => '#comment-content',
            '@submit-comment' => '.submit-comment-button',
        ];
    }
    
    public function createPost(Browser $browser, $title, $content, $type = 'text')
    {
        $browser->type('@title', $title)
                ->type('@content', $content)
                ->select('@type', $type)
                ->screenshot('create_post_form')
                ->press('@submit-button');
    }
    
    public function addComment(Browser $browser, $content)
    {
        $browser->type('@comment-content', $content)
                ->screenshot('add_comment_form')
                ->press('@submit-comment');
    }
}
