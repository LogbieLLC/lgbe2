<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\HomePage;
use Tests\DuskTestCase;

class HomePageTest extends DuskTestCase
{
    use DatabaseMigrations;
    
    public function test_home_page_renders_correctly()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                    ->screenshot('home_page')
                    ->assertSee('Welcome to LGBE2')
                    ->assertSee('A community-driven platform')
                    ->assertSee('Browse Communities')
                    ->assertSee('Sign up');
        });
    }
    
    public function test_home_page_shows_create_community_for_authenticated_users()
    {
        $user = User::factory()->create();
        
        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit(new HomePage)
                    ->screenshot('home_page_authenticated')
                    ->assertSee('Create a Community')
                    ->assertDontSee('Sign up');
        });
    }
}
