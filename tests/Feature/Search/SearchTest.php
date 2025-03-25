<?php

use App\Models\User;
use App\Models\Community;
use App\Models\Post;
use App\Models\Comment;

test('user can search communities', function () {
    $user = User::factory()->create();
    
    // Create test communities
    Community::factory()->create(['name' => 'Test Community']);
    Community::factory()->create(['name' => 'Another Community']);
    Community::factory()->create(['name' => 'Different Name']);
    
    $response = actingAs($user)
        ->getJson('/api/search/communities?q=Test');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Test Community');
});

test('user can search posts across all communities', function () {
    $user = User::factory()->create();
    
    // Create test posts
    Post::factory()->create(['title' => 'Test Post Title']);
    Post::factory()->create(['title' => 'Another Post']);
    Post::factory()->create(['title' => 'Different Title']);
    
    $response = actingAs($user)
        ->getJson('/api/search/posts?q=Test');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Test Post Title');
});

test('user can search posts within a specific community', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    
    // Create test posts in the community
    Post::factory()->create([
        'title' => 'Test Post in Community',
        'community_id' => $community->id
    ]);
    
    // Create test posts in other communities
    Post::factory()->create([
        'title' => 'Test Post in Other Community'
    ]);
    
    $response = actingAs($user)
        ->getJson("/api/search/posts?community_id={$community->id}&q=Test");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Test Post in Community');
});

test('search returns empty results when no matches found', function () {
    $user = User::factory()->create();
    
    // Create some posts
    Post::factory()->count(3)->create();
    
    $response = actingAs($user)
        ->getJson('/api/search/posts?q=NonExistentTerm');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
});

test('search results are paginated', function () {
    $user = User::factory()->create();
    
    // Create many posts with the same search term
    for ($i = 1; $i <= 15; $i++) {
        Post::factory()->create([
            'title' => "Test Post {$i}"
        ]);
    }
    
    $response = actingAs($user)
        ->getJson('/api/search/posts?q=Test&page=1&per_page=10');

    $response->assertStatus(200)
        ->assertJsonCount(10, 'data')
        ->assertJsonStructure([
            'data',
            'links',
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total'
            ]
        ]);
    
    $this->assertEquals(1, $response->json('meta.current_page'));
    $this->assertEquals(2, $response->json('meta.last_page'));
    $this->assertEquals(10, $response->json('meta.per_page'));
    $this->assertEquals(15, $response->json('meta.total'));
});
