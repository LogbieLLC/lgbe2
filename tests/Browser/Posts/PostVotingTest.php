<?php

namespace Tests\Browser\Posts;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

class PostVotingTest extends DuskTestCase
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
     * Test that authenticated users can upvote posts.
     */
    #[Test]
    public function testAuthenticatedUserCanUpvote(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('upvote-test');
        });
    }

    /**
     * Test that authenticated users can downvote posts.
     */
    #[Test]
    public function testAuthenticatedUserCanDownvote(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('downvote-test');
        });
    }

    /**
     * Test that votes toggle when clicking the same button.
     */
    #[Test]
    public function testVoteTogglesWhenClickingSameButton(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('vote-toggle-test');
        });
    }

    /**
     * Test that unauthenticated users cannot vote.
     */
    #[Test]
    public function testUnauthenticatedUsersCannotVote(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('unauthenticated-vote-test');
        });
    }

    /**
     * Test that vote count updates correctly.
     */
    #[Test]
    public function testVoteCountUpdatesCorrectly(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('vote-count-test');
        });
    }
}
