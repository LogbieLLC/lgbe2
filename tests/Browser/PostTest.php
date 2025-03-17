<?php

namespace Tests\Browser;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\Browser\Pages\CommunityPage;
use Tests\Browser\Pages\PostPage;
use Tests\DuskTestCase;

class PostTest extends DuskTestCase
{
    use DatabaseMigrations;
    
    public function test_user_can_create_post()
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();
        
        // Make user a member of the community
        $community->members()->attach($user->id, ['role' => 'member']);
        
        $this->browse(function (Browser $browser) use ($user, $community) {
            $browser->loginAs($user)
                    ->visit(new PostPage(null, $community))
                    ->screenshot('create_post_page')
                    ->assertSee('Create Post')
                    ->type('@title', 'Test Post Title')
                    ->type('@content', 'This is a test post content')
                    ->select('@type', 'text')
                    ->screenshot('post_form_filled')
                    ->press('@submit-button')
                    ->waitForLocation('/communities/' . $community->id)
                    ->screenshot('after_post_creation')
                    ->assertSee('Test Post Title');
                    
            // Verify post is created in database
            $this->assertDatabaseHas('posts', [
                'title' => 'Test Post Title',
                'content' => 'This is a test post content',
                'user_id' => $user->id,
                'community_id' => $community->id,
            ]);
        });
    }
}
