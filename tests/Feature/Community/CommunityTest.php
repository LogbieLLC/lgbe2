<?php

use App\Models\User;
use App\Models\Community;

test('user can create a community', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
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

    // Check that creator is automatically a moderator
    $this->assertDatabaseHas('community_user', [
        'user_id' => $user->id,
        'community_id' => $response->json('id'),
        'role' => 'moderator'
    ]);
});

test('user cannot create community with invalid data', function () {
    $user = User::factory()->create();

    $response = actingAs($user)
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

    $response = actingAs($user)
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
    actingAs($user)
        ->postJson("/api/communities/{$community->id}/join");

    // Second join attempt
    $response = actingAs($user)
        ->postJson("/api/communities/{$community->id}/join");

    $response->assertStatus(400)
        ->assertJson(['message' => 'Already a member of this community']);
});

test('moderator can update community rules', function () {
    [$community, $moderator] = createCommunityWithModerator();

    $response = actingAs($moderator)
        ->putJson("/api/communities/{$community->id}", [
            'description' => 'Updated community description',
            'rules' => 'Updated community rules'
        ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Community updated successfully']);

    $this->assertDatabaseHas('communities', [
        'id' => $community->id,
        'description' => 'Updated community description',
        'rules' => 'Updated community rules'
    ]);
});

test('non-moderator cannot update community rules', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();

    $response = actingAs($user)
        ->putJson("/api/communities/{$community->id}", [
            'description' => 'Updated community description',
            'rules' => 'Updated community rules'
        ]);

    $response->assertStatus(403);
});
