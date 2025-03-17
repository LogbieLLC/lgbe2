<?php

namespace Tests\Browser\Posts;

use App\Models\User;
use App\Models\Community;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class PostCreationTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test that an authenticated user can create a post.
     */
    public function test_authenticated_user_can_create_post(): void
    {
        // Create a user and a community
        $user = User::factory()->create();
        $community = Community::factory()->create();
        
        // Make the user a member of the community
        $community->members()->attach($user->id, ['role' => 'member']);

        $this->browse(function (Browser $browser) use ($user, $community) {
            $browser->loginAs($user)
                    ->visit(route('communities.show', $community))
                    ->assertSee('r/' . $community->name)
                    ->assertSee('Create Post')
                    ->clickLink('Create Post')
                    ->assertPathIs('/communities/' . $community->id . '/posts/create')
                    ->screenshot('post-creation-form')
                    
                    // Fill in the post form
                    ->type('title', 'Test Post Title')
                    ->type('content', 'This is a test post content.')
                    ->select('type', 'text')
                    ->screenshot('post-form-filled')
                    
                    // Submit the form
                    ->press('Create Post')
                    ->waitForLocation('/posts/*')
                    ->assertSee('Test Post Title')
                    ->assertSee('This is a test post content.')
                    ->screenshot('post-created');
                    
            // Verify the post appears in the community
            $browser->visit(route('communities.show', $community))
                    ->assertSee('Test Post Title')
                    ->screenshot('post-in-community');
        });
    }

    /**
     * Test that unauthenticated users are redirected to login.
     */
    public function test_unauthenticated_users_redirected(): void
    {
        $community = Community::factory()->create();

        $this->browse(function (Browser $browser) use ($community) {
            $browser->visit('/communities/' . $community->id . '/posts/create')
                    ->assertPathIs('/login')
                    ->screenshot('unauthenticated-redirect');
        });
    }

    /**
     * Test post form validation.
     */
    public function test_post_form_validation(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $community->members()->attach($user->id, ['role' => 'member']);

        $this->browse(function (Browser $browser) use ($user, $community) {
            $browser->loginAs($user)
                    ->visit('/communities/' . $community->id . '/posts/create')
                    
                    // Submit empty form
                    ->press('Create Post')
                    ->waitForText('The title field is required')
                    ->assertSee('The title field is required')
                    ->assertSee('The content field is required')
                    ->screenshot('post-validation-errors');
                    
            // Test title length validation
            $browser->visit('/communities/' . $community->id . '/posts/create')
                    ->type('title', str_repeat('a', 300)) // Title too long
                    ->type('content', 'Test content')
                    ->select('type', 'text')
                    ->press('Create Post')
                    ->waitForText('The title must not be greater than')
                    ->assertSee('The title must not be greater than')
                    ->screenshot('post-title-too-long');
        });
    }

    /**
     * Test that a post appears in the community after creation.
     */
    public function test_post_appears_in_community(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $community->members()->attach($user->id, ['role' => 'member']);
        
        $postTitle = 'Unique Test Post ' . rand(1000, 9999);
        $postContent = 'This is a unique test post content ' . rand(1000, 9999);

        $this->browse(function (Browser $browser) use ($user, $community, $postTitle, $postContent) {
            // Create the post
            $browser->loginAs($user)
                    ->visit('/communities/' . $community->id . '/posts/create')
                    ->type('title', $postTitle)
                    ->type('content', $postContent)
                    ->select('type', 'text')
                    ->press('Create Post')
                    ->waitForLocation('/posts/*');
                    
            // Visit the community page and check if the post is listed
            $browser->visit(route('communities.show', $community))
                    ->assertSee($postTitle)
                    ->screenshot('post-listed-in-community');
        });
    }
}
