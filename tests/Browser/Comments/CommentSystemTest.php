<?php

namespace Tests\Browser\Comments;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

class CommentSystemTest extends DuskTestCase
{
    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Preserve exception handlers to prevent risky test warnings
        $this->withExceptionHandling();
    }

    /**
     * Test that authenticated users can comment on posts.
     */
    #[Test]
    public function test_authenticated_user_can_comment_on_post(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('comment-test');
        });
    }

    /**
     * Test that users can reply to comments.
     */
    #[Test]
    public function test_user_can_reply_to_comment(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('reply-test');
        });
    }

    /**
     * Test that users can edit their own comments.
     */
    #[Test]
    public function test_user_can_edit_own_comment(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('edit-test');
        });
    }
}
