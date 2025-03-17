<?php

namespace Tests\Browser\Auth;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class RegistrationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test successful user registration.
     */
    public function test_successful_registration(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->assertSee('Register')
                    ->type('name', 'Test User')
                    ->type('username', 'testuser' . rand(1000, 9999))
                    ->type('email', 'test' . rand(1000, 9999) . '@example.com')
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->screenshot('registration-form-filled')
                    ->press('Register')
                    ->waitForLocation('/dashboard')
                    ->assertPathIs('/dashboard')
                    ->assertSee('Dashboard')
                    ->screenshot('registration-successful');
        });
    }

    /**
     * Test validation for missing fields.
     */
    public function test_validation_missing_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->press('Register')
                    ->waitForText('The name field is required')
                    ->assertSee('The name field is required')
                    ->assertSee('The email field is required')
                    ->assertSee('The password field is required')
                    ->screenshot('registration-validation-missing-fields');
        });
    }

    /**
     * Test validation for password requirements.
     */
    public function test_validation_password_requirements(): void
    {
        $this->browse(function (Browser $browser) {
            // Test password too short
            $browser->visit('/register')
                    ->type('name', 'Test User')
                    ->type('username', 'testuser' . rand(1000, 9999))
                    ->type('email', 'test' . rand(1000, 9999) . '@example.com')
                    ->type('password', 'pass')
                    ->type('password_confirmation', 'pass')
                    ->press('Register')
                    ->waitForText('The password must be at least 8 characters')
                    ->assertSee('The password must be at least 8 characters')
                    ->screenshot('registration-password-too-short');

            // Test password confirmation mismatch
            $browser->visit('/register')
                    ->type('name', 'Test User')
                    ->type('username', 'testuser' . rand(1000, 9999))
                    ->type('email', 'test' . rand(1000, 9999) . '@example.com')
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'differentpassword')
                    ->press('Register')
                    ->waitForText('The password confirmation does not match')
                    ->assertSee('The password confirmation does not match')
                    ->screenshot('registration-password-mismatch');
        });
    }

    /**
     * Test validation for unique email.
     */
    public function test_validation_unique_email(): void
    {
        // Create a user with a known email
        $user = User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('/register')
                    ->type('name', 'Test User')
                    ->type('username', 'testuser' . rand(1000, 9999))
                    ->type('email', 'existing@example.com')
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    ->press('Register')
                    ->waitForText('The email has already been taken')
                    ->assertSee('The email has already been taken')
                    ->screenshot('registration-email-taken');
        });
    }
}
