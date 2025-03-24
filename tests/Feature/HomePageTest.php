<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

test('home page loads successfully', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    $response->assertSee('<html', false); // false to disable escaping
});

test('home page contains expected components', function () {
    $response = $this->get('/');
    
    $response->assertStatus(200);
    // Check for key elements that should be on the home page
    // These assertions should be adjusted based on your actual home page content
    $response->assertSee('</body>', false);
    $response->assertSee('</head>', false);
});
