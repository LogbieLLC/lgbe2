<?php

use App\Models\User;
use App\Models\Community;
use App\Models\CommunityUser;

test('user can create a community', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/communities', [
            'name' => 'testcommunity',
            'description' => 'A test community',
            'rules' => '1. Be nice\n2. Follow guidelines'
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'name',
            'description',
            'rules',
            'created_at',
            'updated_at'
        ]);

    $this->assertDatabaseHas('communities', [
        'name' => 'testcommunity',
        'description' => 'A test community'
    ]);

    $this->assertDatabaseHas('community_user', [
        'user_id' => $user->id,
        'community_id' => $response->json('id'),
        'role' => 'moderator'
    ]);
});

test('user cannot create community with invalid data', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/communities', [
            'name' => 't', // Too short
            'description' => '' // Empty
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'description']);
});

test('user can join a community', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson("/api/communities/{$community->id}/join");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Successfully joined community']);

    $this->assertDatabaseHas('community_user', [
        'user_id' => $user->id,
        'community_id' => $community->id,
        'role' => 'member'
    ]);
});

test('user cannot join same community twice', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    
    // First join
    $this->actingAs($user)
        ->postJson("/api/communities/{$community->id}/join");

    // Second join attempt
    $response = $this->actingAs($user)
        ->postJson("/api/communities/{$community->id}/join");

    $response->assertStatus(400)
        ->assertJson(['message' => 'Already a member of this community']);
});

test('moderator can update community rules', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    
    // Make user a moderator
    CommunityUser::create([
        'user_id' => $user->id,
        'community_id' => $community->id,
        'role' => 'moderator'
    ]);

    $response = $this->actingAs($user)
        ->putJson("/api/communities/{$community->id}", [
            'rules' => 'Updated community rules'
        ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Community updated successfully']);

    $this->assertDatabaseHas('communities', [
        'id' => $community->id,
        'rules' => 'Updated community rules'
    ]);
});

test('non-moderator cannot update community rules', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    
    $response = $this->actingAs($user)
        ->putJson("/api/communities/{$community->id}", [
            'rules' => 'Updated community rules'
        ]);

    $response->assertStatus(403)
        ->assertJson(['message' => 'Unauthorized action']);
});

test('moderator can remove posts from community', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();
    
    // Make user a moderator
    CommunityUser::create([
        'user_id' => $user->id,
        'community_id' => $community->id,
        'role' => 'moderator'
    ]);

    // Create a post in the community
    $post = $community->posts()->create([
        'title' => 'Test Post',
        'content' => 'Test Content',
        'user_id' => User::factory()->create()->id
    ]);

    $response = $this->actingAs($user)
        ->deleteJson("/api/communities/{$community->id}/posts/{$post->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Post removed successfully']);

    $this->assertSoftDeleted('posts', [
        'id' => $post->id
    ]);
}); 