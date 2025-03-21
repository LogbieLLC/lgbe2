<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function testGuestsAreRedirectedToTheLoginPage()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function testAuthenticatedUsersCanVisitTheDashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard');
        $response->assertStatus(200);
    }
}
