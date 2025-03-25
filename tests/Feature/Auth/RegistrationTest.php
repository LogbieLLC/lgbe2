<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);
});

test('registration requires valid email', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'not-an-email',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('registration requires password confirmation', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertSessionHasErrors(['password']);
});

test('api registration creates a user and returns token', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Test User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure(['token']);

    $this->assertDatabaseHas('users', [
        'name' => 'testuser',
        'username' => 'testuser',
        'email' => 'test@example.com',
    ]);
});
