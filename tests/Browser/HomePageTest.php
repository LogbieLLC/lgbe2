<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class HomePageTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test that the home page loads correctly.
     */
    public function test_home_page_loads(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Welcome to LGBE2')
                    ->assertSee('A community-driven platform')
                    ->screenshot('home-page');
        });
    }

    /**
     * Test that navigation links are working correctly.
     */
    public function test_navigation_links(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSeeLink('Browse Communities')
                    ->assertSeeLink('Sign up')
                    ->clickLink('Browse Communities')
                    ->assertPathIs('/communities')
                    ->screenshot('communities-page')
                    ->back()
                    ->clickLink('Sign up')
                    ->assertPathIs('/register')
                    ->screenshot('register-page');
        });
    }

    /**
     * Test that popular communities are displayed on the home page.
     */
    public function test_popular_communities_display(): void
    {
        // Create some test communities
        $communities = \App\Models\Community::factory()->count(3)->create();

        $this->browse(function (Browser $browser) use ($communities) {
            $browser->visit('/')
                    ->waitForText('Discover Popular Communities')
                    ->assertSee('Discover Popular Communities');
            
            // Check if communities are displayed
            foreach ($communities as $community) {
                $browser->assertSee('r/' . $community->name)
                        ->assertSee($community->description);
            }
            
            $browser->screenshot('communities-displayed');
        });
    }

    /**
     * Test that footer elements are displayed correctly.
     */
    public function test_footer_elements(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->scrollIntoView('footer')
                    ->assertSee('© 2025 LGBE2. All rights reserved.')
                    ->screenshot('footer');
        });
    }
}
