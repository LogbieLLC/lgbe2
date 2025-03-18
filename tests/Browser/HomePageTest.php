<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

class HomePageTest extends DuskTestCase
{
    /**
     * Test that the home page loads.
     */
    #[Test]
    public function test_home_page_loads(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSourceHas('<html')
                    ->screenshot('home-page');
        });
    }
}
