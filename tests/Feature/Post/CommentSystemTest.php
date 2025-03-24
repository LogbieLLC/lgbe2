<?php

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can comment on a post', function () {
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

test('unauthenticated user cannot comment on a post', function () {
    $post = Post::factory()->create();
    
    $response = $this->postJson("/api/posts/{$post->id}/comments", [
        'content' => 'This is a test comment'
    ]);

    $response->assertStatus(401);
    
    $this->assertDatabaseMissing('comments', [
        'content' => 'This is a test comment',
        'post_id' => $post->id
    ]);
});

test('user can edit their own comment', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    $comment = Comment::factory()->create([
        'user_id' => $user->id,
        'post_id' => $post->id,
        'content' => 'Original comment'
    ]);
    
    $response = $this->actingAs($user)
        ->putJson("/api/comments/{$comment->id}", [
            'content' => 'Edited comment'
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'content',
            'user_id',
            'post_id',
            'created_at',
            'updated_at'
        ])
        ->assertJson([
            'content' => 'Edited comment'
        ]);

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'content' => 'Edited comment'
    ]);
});

test('user cannot edit another user\'s comment', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $post = Post::factory()->create();
    $comment = Comment::factory()->create([
        'user_id' => $otherUser->id,
        'post_id' => $post->id,
        'content' => 'Original comment'
    ]);
    
    $response = $this->actingAs($user)
        ->putJson("/api/comments/{$comment->id}", [
            'content' => 'Edited comment'
        ]);

    $response->assertStatus(403);
    
    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'content' => 'Original comment'
    ]);
});

test('comment validation rejects empty content', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson("/api/posts/{$post->id}/comments", [
            'content' => ''
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['content']);
});

test('comment validation rejects too long content', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    
    // Create a string that's too long (assuming there's a max length validation)
    $longContent = str_repeat('a', 10000);
    
    $response = $this->actingAs($user)
        ->postJson("/api/posts/{$post->id}/comments", [
            'content' => $longContent
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['content']);
});

test('nested comments are retrieved with correct structure', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    
    // Create parent comment
    $parentComment = Comment::factory()->create([
        'user_id' => $user->id,
        'post_id' => $post->id,
        'content' => 'Parent comment'
    ]);
    
    // Create child comments
    $childComment1 = Comment::factory()->create([
        'user_id' => $user->id,
        'post_id' => $post->id,
        'parent_comment_id' => $parentComment->id,
        'content' => 'Child comment 1'
    ]);
    
    $childComment2 = Comment::factory()->create([
        'user_id' => $user->id,
        'post_id' => $post->id,
        'parent_comment_id' => $parentComment->id,
        'content' => 'Child comment 2'
    ]);
    
    // Get comments for the post
    $response = $this->getJson("/api/posts/{$post->id}/comments");
    
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data') // Only parent comments in the root
        ->assertJsonPath('data.0.id', $parentComment->id)
        ->assertJsonPath('data.0.content', 'Parent comment')
        ->assertJsonCount(2, 'data.0.replies'); // Two child comments
});
