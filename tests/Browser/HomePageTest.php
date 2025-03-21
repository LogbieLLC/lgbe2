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
    public function testHomePageLoads(): void
    {
        $this->withoutExceptionHandling();
        $this->browse(function (Browser $browser) {
            $browser->visit('http://127.0.0.1:8000/')
                    ->assertSourceHas('<html')
                    ->screenshot('home-page');
        });
    }
}
