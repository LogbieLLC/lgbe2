<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('user can register with valid data', function () {
    $response = $this->postJson('/api/auth/register', [
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123'
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'user' => [
                'id',
                'username',
                'email',
                'karma'
            ],
            'token'
        ]);

    $this->assertDatabaseHas('users', [
        'name' => 'testuser',
        'email' => 'test@example.com'
    ]);
});

test('user cannot register with invalid data', function () {
    $response = $this->postJson('/api/auth/register', [
        'username' => 't', // Too short
        'email' => 'invalid-email',
        'password' => '123', // Too short
        'password_confirmation' => '123'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'user' => [
                'id',
                'username',
                'email',
                'karma'
            ],
            'token'
        ]);
});

test('user cannot login with invalid credentials', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword'
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Invalid credentials']);
});

test('user can request password reset', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com'
    ]);

    $response = $this->postJson('/api/auth/forgot-password', [
        'email' => 'test@example.com'
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Password reset link sent']);
});

test('user can reset password with valid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com'
    ]);

    $token = $this->postJson('/api/auth/forgot-password', [
        'email' => 'test@example.com'
    ]);

    $response = $this->postJson('/api/auth/reset-password', [
        'token' => $token,
        'email' => 'test@example.com',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123'
    ]);

    $response->assertStatus(200)
        ->assertJson(['message' => 'Password reset successfully']);

    $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
});
