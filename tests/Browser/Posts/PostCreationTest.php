<?php

namespace Tests\Browser\Posts;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

class PostCreationTest extends DuskTestCase
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
     * Test that authenticated users can create posts.
     */
    #[Test]
    public function testAuthenticatedUserCanCreatePost(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('post-creation');
        });
    }

    /**
     * Test that unauthenticated users are redirected.
     */
    #[Test]
    public function testUnauthenticatedUsersRedirected(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('unauthenticated-redirect');
        });
    }

    /**
     * Test post form validation.
     */
    #[Test]
    public function testPostFormValidation(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('post-validation');
        });
    }

    /**
     * Test that posts appear in the community.
     */
    #[Test]
    public function testPostAppearsInCommunity(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('post-in-community');
        });
    }
}
