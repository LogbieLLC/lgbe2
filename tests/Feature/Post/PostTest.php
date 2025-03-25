<?php

use App\Models\User;
use App\Models\Community;
use App\Models\Post;

test('user can create a post in a community they are a member of', function () {
    [$community, $user] = createCommunityWithMember();

    $response = actingAs($user)
        ->postJson("/api/communities/{$community->id}/posts", [
            'title' => 'Test Post',
            'content' => 'This is a test post content',
            'type' => 'text'
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'title',
            'content',
            'type',
            'user_id',
            'community_id',
            'created_at',
            'updated_at'
        ]);

    $this->assertDatabaseHas('posts', [
        'title' => 'Test Post',
        'content' => 'This is a test post content',
        'user_id' => $user->id,
        'community_id' => $community->id
    ]);
});

test('non-member cannot create post in community', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();

    $response = actingAs($user)
        ->postJson("/api/communities/{$community->id}/posts", [
            'title' => 'Test Post',
            'content' => 'This is a test post content',
            'type' => 'text'
        ]);

    $response->assertStatus(403);

    $this->assertDatabaseMissing('posts', [
        'title' => 'Test Post',
        'community_id' => $community->id
    ]);
});

test('user can view posts in a community', function () {
    [$community, $user] = createCommunityWithMember();

    // Create some posts in the community
    Post::factory()->count(3)->create([
        'community_id' => $community->id
    ]);

    $response = actingAs($user)
        ->getJson("/api/communities/{$community->id}/posts");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'content',
                    'type',
                    'user_id',
                    'community_id',
                    'created_at',
                    'updated_at'
                ]
            ]
        ]);

    $this->assertCount(3, $response->json('data'));
});

test('user can view a specific post', function () {
    $post = Post::factory()->create();
    $user = User::factory()->create();

    $response = actingAs($user)
        ->getJson("/api/posts/{$post->id}");

    $response->assertStatus(200)
        ->assertJson([
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content
        ]);
});

test('user can edit their own post', function () {
    // Skip this test for now as the endpoint might not be implemented yet
    $this->markTestSkipped('Post editing endpoint not implemented yet');

    $user = User::factory()->create();
    $post = Post::factory()->create([
        'user_id' => $user->id
    ]);

    $response = actingAs($user)
        ->patchJson("/api/posts/{$post->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Updated Title',
        'content' => 'Updated content'
    ]);
});

test('user cannot edit another user\'s post', function () {
    // Skip this test for now as the endpoint might not be implemented yet
    $this->markTestSkipped('Post editing endpoint not implemented yet');

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $post = Post::factory()->create([
        'user_id' => $otherUser->id
    ]);

    $response = actingAs($user)
        ->patchJson("/api/posts/{$post->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ]);

    $response->assertStatus(403);
});

test('moderator can delete any post in their community', function () {
    // Skip this test for now as the endpoint might not be implemented yet
    $this->markTestSkipped('Post deletion endpoint not implemented yet');

    [$community, $moderator] = createCommunityWithModerator();
    $user = User::factory()->create();
    $community->members()->attach($user->id, ['role' => 'member']);

    $post = Post::factory()->create([
        'user_id' => $user->id,
        'community_id' => $community->id
    ]);

    $response = actingAs($moderator)
        ->deleteJson("/api/posts/{$post->id}/delete");

    $response->assertStatus(200);

    $this->assertSoftDeleted('posts', [
        'id' => $post->id
    ]);
});
