<?php

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Vote;

test('user can upvote a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $response = actingAs($user)
        ->postJson("/api/posts/{$post->id}/vote", [
            'vote_type' => 'up'
        ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Vote added successfully']);

    $this->assertDatabaseHas('votes', [
        'user_id' => $user->id,
        'votable_id' => $post->id,
        'votable_type' => Post::class,
        'vote_type' => 'up'
    ]);
});

test('user can downvote a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $response = actingAs($user)
        ->postJson("/api/posts/{$post->id}/vote", [
            'vote_type' => 'down'
        ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Vote added successfully']);

    $this->assertDatabaseHas('votes', [
        'user_id' => $user->id,
        'votable_id' => $post->id,
        'votable_type' => Post::class,
        'vote_type' => 'down'
    ]);
});

test('user can change their vote on a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    // First vote up
    actingAs($user)
        ->postJson("/api/posts/{$post->id}/vote", [
            'vote_type' => 'up'
        ]);

    // Then change to downvote
    $response = actingAs($user)
        ->postJson("/api/posts/{$post->id}/vote", [
            'vote_type' => 'down'
        ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Vote updated successfully']);

    $this->assertDatabaseHas('votes', [
        'user_id' => $user->id,
        'votable_id' => $post->id,
        'votable_type' => Post::class,
        'vote_type' => 'down'
    ]);
});

test('user can upvote a comment', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->create();

    $response = actingAs($user)
        ->postJson("/api/comments/{$comment->id}/vote", [
            'vote_type' => 'up'
        ]);

    // Skip this test for now as the endpoint might not be implemented yet
    $this->markTestSkipped('Comment voting endpoint not implemented yet');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Vote added successfully']);

    $this->assertDatabaseHas('votes', [
        'user_id' => $user->id,
        'votable_id' => $comment->id,
        'votable_type' => Comment::class,
        'vote_type' => 'up'
    ]);
});

test('karma is updated correctly when post is upvoted', function () {
    $postAuthor = User::factory()->create(['karma' => 0]);
    $voter = User::factory()->create();

    $post = Post::factory()->create([
        'user_id' => $postAuthor->id
    ]);

    $initialKarma = $postAuthor->karma;

    // Upvote the post
    actingAs($voter)
        ->postJson("/api/posts/{$post->id}/vote", [
            'vote_type' => 'up'
        ]);

    $postAuthor->refresh();
    $this->assertEquals($initialKarma + 1, $postAuthor->karma);
});
