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
    public function basicExample()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('http://127.0.0.1:8000')
                    ->assertSee('Laravel');
        });
    }
}
