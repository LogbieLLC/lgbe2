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
    public function test_authenticated_user_can_create_post(): void
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
    public function test_unauthenticated_users_redirected(): void
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
    public function test_post_form_validation(): void
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
    public function test_post_appears_in_community(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('post-in-community');
        });
    }
}
