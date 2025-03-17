<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;

class CommunityPage extends Page
{
    protected $community;
    
    public function __construct($community = null)
    {
        $this->community = $community;
    }
    
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return $this->community 
            ? '/communities/' . $this->community->id
            : '/communities/create';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        if ($this->community) {
            $browser->assertSee('r/' . $this->community->name);
        } else {
            $browser->assertSee('Create Community');
        }
    }

    /**
     * Get the element shortcuts for the page.
     *
     * @return array<string, string>
     */
    public function elements(): array
    {
        return [
            '@name' => '#name',
            '@description' => '#description',
            '@rules' => '#rules',
            '@create-button' => 'button[type="submit"]',
        ];
    }
    
    public function createCommunity(Browser $browser, $name, $description, $rules = '')
    {
        $browser->type('@name', $name)
                ->type('@description', $description)
                ->type('@rules', $rules)
                ->screenshot('create_community_form')
                ->press('@create-button');
    }
}
