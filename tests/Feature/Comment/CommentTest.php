<?php

use App\Models\User;
use App\Models\Post;
use App\Models\Comment;

test('user can comment on a post', function () {
    $user = User::factory()->create();
    $post = Post::factory()->create();
    
    $response = actingAs($user)
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
    
    $response = actingAs($user)
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
    
    $response = actingAs($user)
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
    
    $response = actingAs($user)
        ->deleteJson("/api/comments/{$comment->id}");

    $response->assertStatus(403);

    $this->assertDatabaseHas('comments', [
        'id' => $comment->id,
        'deleted_at' => null
    ]);
});
