<?php

test('the welcome page loads successfully', function () {
    $this->get('/')
         ->assertStatus(200);
});
