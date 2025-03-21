<?php

use App\Models\User;
use App\Models\Community;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;

test('user can create a post in a community', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();

    // Make user a member of the community
    $community->members()->attach($user->id, ['role' => 'member']);

    $response = $this->actingAs($user)
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

test('user can upvote a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $response = $this->actingAs($user)
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

test('user can change their vote on a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    // First vote up
    $this->actingAs($user)
        ->postJson("/api/posts/{$post->id}/vote", [
            'vote_type' => 'up'
        ]);

    // Then change to downvote
    $response = $this->actingAs($user)
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

test('user can comment on a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();

    $response = $this->actingAs($user)
        ->postJson("/api/posts/{$post->id}/comments", [
            'content' => 'This is a test comment'
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'content',
            'user_id',
            'post_id',
            'created_at',
            'updated_at'
        ]);

    $this->assertDatabaseHas('comments', [
        'content' => 'This is a test comment',
        'user_id' => $user->id,
        'post_id' => $post->id
    ]);
});

test('user can reply to a comment', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $parentComment = Comment::factory()->create([
        'post_id' => $post->id
    ]);

    $response = $this->actingAs($user)
        ->postJson("/api/posts/{$post->id}/comments", [
            'content' => 'This is a reply',
            'parent_comment_id' => $parentComment->id
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'content',
            'user_id',
            'post_id',
            'parent_comment_id',
            'created_at',
            'updated_at'
        ]);

    $this->assertDatabaseHas('comments', [
        'content' => 'This is a reply',
        'user_id' => $user->id,
        'post_id' => $post->id,
        'parent_comment_id' => $parentComment->id
    ]);
});

test('user can delete their own comment', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $user->id
    ]);

    $response = $this->actingAs($user)
        ->deleteJson("/api/comments/{$comment->id}");

    $response->assertStatus(200)
        ->assertJson(['message' => 'Comment deleted successfully']);

    $this->assertSoftDeleted('comments', [
        'id' => $comment->id
    ]);
});

test('user cannot delete another user\'s comment', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $post = Post::factory()->create();
    $comment = Comment::factory()->create([
        'post_id' => $post->id,
        'user_id' => $otherUser->id
    ]);

    $response = $this->actingAs($user)
        ->deleteJson("/api/comments/{$comment->id}");

    $response->assertStatus(403)
        ->assertJson(['message' => 'Unauthorized action']);

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id
    ]);
});

test('posts are sorted by score correctly', function () {
    $user = User::factory()->create();
    $community = Community::factory()->create();

    // Make user a member of the community
    $community->members()->attach($user->id, ['role' => 'member']);

    // Create two posts
    $post1 = Post::factory()->create([
        'community_id' => $community->id,
        'created_at' => now()->subDays(1)
    ]);
    $post2 = Post::factory()->create([
        'community_id' => $community->id,
        'created_at' => now()->subDays(2)
    ]);

    // Add votes to post2 to make it higher scoring
    Vote::create([
        'user_id' => $user->id,
        'votable_id' => $post2->id,
        'votable_type' => Post::class,
        'vote_type' => 'up'
    ]);

    $response = $this->actingAs($user)->getJson("/api/communities/{$community->id}/posts");

    $response->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $post2->id)
        ->assertJsonPath('data.1.id', $post1->id);
});
