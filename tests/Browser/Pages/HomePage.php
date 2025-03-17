<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class HomePage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertSee('Welcome to LGBE2')
                ->assertSee('A community-driven platform');
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@browse-communities' => 'a[href="' . route('communities.index') . '"]',
            '@register-link' => 'a[href="' . route('register') . '"]',
            '@create-community' => 'a[href="' . route('communities.create') . '"]',
        ];
    }
}
