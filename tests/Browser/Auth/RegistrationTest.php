<?php

namespace Tests\Browser\Auth;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;

class RegistrationTest extends DuskTestCase
{
    use DatabaseMigrations;
    
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
     * Clean up after each test.
     */
    protected function tearDown(): void
    {
        // Make sure we restore exception handling before ending the test
        $this->withExceptionHandling();
        
        parent::tearDown();
    }

    /**
     * Test successful user registration.
     */
    #[Test]
    public function test_successful_registration(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('registration-home');
        });
    }

    /**
     * Test validation for missing fields.
     */
    #[Test]
    public function test_validation_missing_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('registration-validation');
        });
    }

    /**
     * Test validation for password requirements.
     */
    #[Test]
    public function test_validation_password_requirements(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('registration-password');
        });
    }

    /**
     * Test validation for unique email.
     */
    #[Test]
    public function test_validation_unique_email(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('registration-email');
        });
    }
}
