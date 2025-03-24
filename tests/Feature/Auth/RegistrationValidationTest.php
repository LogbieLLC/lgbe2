<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('registration requires all fields', function () {
    // Test missing username
    $response = $this->postJson('/api/auth/register', [
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username']);

    // Test missing email
    $response = $this->postJson('/api/auth/register', [
        'username' => 'testuser',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    // Test missing password
    $response = $this->postJson('/api/auth/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('registration validates password requirements', function () {
    // Test password too short
    $response = $this->postJson('/api/auth/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'pass',
        'password_confirmation' => 'pass'
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);

    // Test password confirmation mismatch
    $response = $this->postJson('/api/auth/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'differentpassword'
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('registration validates unique email', function () {
    // Create a user with the email we'll try to register with
    User::factory()->create([
        'email' => 'existing@example.com'
    ]);

    // Try to register with the same email
    $response = $this->postJson('/api/auth/register', [
        'username' => 'testuser',
        'email' => 'existing@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('registration validates username format', function () {
    // Test username too short
    $response = $this->postJson('/api/auth/register', [
        'username' => 'a',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username']);

    // Test username with invalid characters (if applicable)
    $response = $this->postJson('/api/auth/register', [
        'username' => 'user name',  // Space in username
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['username']);
});
