<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use PHPUnit\Framework\Attributes\Test;

class SimpleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     */
    #[Test]
    public function basic_example()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('http://localhost:8000')
                    ->assertSee('Laravel');
        });
    }
}
