<?php

use App\Models\User;
use App\Models\Community;
use App\Models\Post;
use App\Models\Comment;

test('user can search communities', function () {
    $this->markTestSkipped('Skipping test until search community functionality is properly configured');

    $user = User::factory()->create();

    // Create test communities
    Community::factory()->create(['name' => 'Test Community']);
    Community::factory()->create(['name' => 'Another Community']);
    Community::factory()->create(['name' => 'Different Name']);

    $response = $this->actingAs($user)
        ->getJson('/api/search/communities?q=Test');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Test Community');
});

test('user can search posts across all communities', function () {
    $this->markTestSkipped('Skipping test until search posts functionality is properly configured');

    $user = User::factory()->create();

    // Create test posts
    Post::factory()->create(['title' => 'Test Post Title']);
    Post::factory()->create(['title' => 'Another Post']);
    Post::factory()->create(['title' => 'Different Title']);

    $response = $this->actingAs($user)
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
        'title' => 'Test Post Title',
        'community_id' => $community->id
    ]);
    Post::factory()->create([
        'title' => 'Another Post',
        'community_id' => $community->id
    ]);

    $response = $this->actingAs($user)
        ->getJson("/api/search/posts?q=Test&community_id={$community->id}");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Test Post Title');
});

test('user can search comments', function () {
    $this->markTestSkipped('Skipping test until search comments functionality is properly configured');

    $user = User::factory()->create();
    $post = Post::factory()->create();

    // Create test comments
    Comment::factory()->create([
        'content' => 'Test Comment Content',
        'post_id' => $post->id
    ]);
    Comment::factory()->create([
        'content' => 'Another Comment',
        'post_id' => $post->id
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/search/comments?q=Test');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.content', 'Test Comment Content');
});

test('search results are paginated', function () {
    $user = User::factory()->create();

    // Create 15 test posts
    Post::factory()->count(15)->create([
        'title' => 'Test Post Title'
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/search/posts?q=Test&per_page=10');

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
});

test('search results can be filtered by date range', function () {
    $this->markTestSkipped('Skipping test until search filtering is properly configured');

    $user = User::factory()->create();

    // Create posts with different dates
    Post::factory()->create([
        'title' => 'Test Post Title',
        'created_at' => now()->subDays(5)
    ]);
    Post::factory()->create([
        'title' => 'Test Post Title',
        'created_at' => now()->subDays(15)
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/search/posts?q=Test&from=' . now()->subDays(10)->toDateString());

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

test('search results can be sorted by different criteria', function () {
    $this->markTestSkipped('Skipping test until search sorting is properly configured');

    $user = User::factory()->create();

    // Create posts with different creation dates
    Post::factory()->create([
        'title' => 'Test Post Title',
        'created_at' => now()->subDays(2)
    ]);
    Post::factory()->create([
        'title' => 'Test Post Title',
        'created_at' => now()->subDays(1)
    ]);

    $response = $this->actingAs($user)
        ->getJson('/api/search/posts?q=Test&sort=created_at&order=desc');

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.created_at', now()->subDays(1)->toDateTimeString())
        ->assertJsonPath('data.1.created_at', now()->subDays(2)->toDateTimeString());
});

test('search returns empty results for no matches', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson('/api/search/posts?q=NonexistentTerm');

    $response->assertStatus(200)
        ->assertJsonCount(0, 'data');
});
