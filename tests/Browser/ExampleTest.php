<?php

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;

test('basic example', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('http://127.0.0.1:8000')
                ->assertSee('Laravel');
    });
});
