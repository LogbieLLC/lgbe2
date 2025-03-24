<?php

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Vote;

test('user can view their profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->getJson("/api/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'username',
            'email',
            'karma',
            'created_at',
            'updated_at'
        ]);
});

test('user karma is calculated correctly from post votes', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'user_id' => $user->id
    ]);

    // Create upvotes
    Vote::factory()->count(5)->create([
        'votable_id' => $post->id,
        'votable_type' => Post::class,
        'vote_type' => 'up'
    ]);

    // Create downvotes
    Vote::factory()->count(2)->create([
        'votable_id' => $post->id,
        'votable_type' => Post::class,
        'vote_type' => 'down'
    ]);

    $response = $this->actingAs($user)
        ->getJson("/api/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJsonPath('karma', 3); // 5 upvotes - 2 downvotes
});

test('user karma is calculated correctly from comment votes', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id
    ]);

    // Create upvotes
    Vote::factory()->count(3)->create([
        'votable_id' => $comment->id,
        'votable_type' => Comment::class,
        'vote_type' => 'up'
    ]);

    // Create downvotes
    Vote::factory()->count(1)->create([
        'votable_id' => $comment->id,
        'votable_type' => Comment::class,
        'vote_type' => 'down'
    ]);

    $response = $this->actingAs($user)
        ->getJson("/api/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJsonPath('karma', 2); // 3 upvotes - 1 downvote
});

test('user can update their profile information', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->putJson("/api/users/{$user->id}", [
            'username' => 'newusername',
            'email' => 'newemail@example.com'
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Profile updated successfully'
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'username' => 'newusername',
        'email' => 'newemail@example.com'
    ]);
});

test('user cannot update another user\'s profile', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->actingAs($user)
        ->putJson("/api/users/{$otherUser->id}", [
            'username' => 'newusername',
            'email' => 'newemail@example.com'
        ]);

    $response->assertStatus(403)
        ->assertJson([
            'message' => 'Unauthorized action'
        ]);

    $this->assertDatabaseMissing('users', [
        'id' => $otherUser->id,
        'username' => 'newusername',
        'email' => 'newemail@example.com'
    ]);
});

test('user can view their posts', function () {
    $user = User::factory()->create();
    Post::factory()->count(3)->create([
        'user_id' => $user->id
    ]);

    $response = $this->actingAs($user)
        ->getJson("/api/users/{$user->id}/posts");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('user can view their comments', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    Comment::factory()->count(3)->create([
        'post_id' => $post->id,
        'user_id' => $user->id
    ]);

    $response = $this->actingAs($user)
        ->getJson("/api/users/{$user->id}/comments");

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('user karma updates in real-time after vote', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create([
        'user_id' => $user->id
    ]);

    // Initial karma check
    $initialResponse = $this->actingAs($user)
        ->getJson("/api/users/{$user->id}");
    $initialKarma = $initialResponse->json('karma');

    // Add an upvote
    Vote::factory()->create([
        'votable_id' => $post->id,
        'votable_type' => Post::class,
        'vote_type' => 'up'
    ]);

    // Check updated karma
    $updatedResponse = $this->actingAs($user)
        ->getJson("/api/users/{$user->id}");
    $updatedKarma = $updatedResponse->json('karma');

    $this->assertEquals($initialKarma + 1, $updatedKarma);
});
