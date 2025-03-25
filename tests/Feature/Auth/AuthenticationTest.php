<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');
    
    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();
    
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    
    $this->assertAuthenticated();
    $response->assertRedirect('/dashboard');
});

test('users cannot authenticate with invalid password', function () {
    $user = User::factory()->create();
    
    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);
    
    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();
    
    $response = actingAs($user)->post('/logout');
    
    $this->assertGuest();
    $response->assertRedirect('/');
});

test('api login returns token for valid credentials', function () {
    $user = User::factory()->create();
    
    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);
    
    $response->assertStatus(200);
    $response->assertJsonStructure(['token']);
});

test('api login fails for invalid credentials', function () {
    $user = User::factory()->create();
    
    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);
    
    $response->assertStatus(401);
});
