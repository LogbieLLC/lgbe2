<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class RegistrationTest extends DuskTestCase
{
    use DatabaseMigrations;
    
    public function test_user_can_register()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->screenshot('registration_page')
                    ->assertSee('Create an account')
                    ->type('name', 'Test User')
                    ->type('email', 'test_' . time() . '@example.com')
                    ->type('password', 'password')
                    ->type('password_confirmation', 'password')
                    ->screenshot('registration_form_filled')
                    ->press('Create account')
                    ->waitForLocation('/dashboard')
                    ->screenshot('after_registration')
                    ->assertPathIs('/dashboard');
                    
            // Verify user is created in database
            $this->assertDatabaseHas('users', [
                'name' => 'Test User',
            ]);
        });
    }
    
    public function test_user_cannot_register_with_invalid_data()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                    ->type('name', 'T')  // Too short
                    ->type('email', 'invalid-email')
                    ->type('password', '123')  // Too short
                    ->type('password_confirmation', '123')
                    ->screenshot('registration_invalid_data')
                    ->press('Create account')
                    ->screenshot('registration_validation_errors')
                    ->assertSee('The name field must be at least')
                    ->assertSee('The email field must be a valid email address')
                    ->assertSee('The password field must be at least');
        });
    }
}
