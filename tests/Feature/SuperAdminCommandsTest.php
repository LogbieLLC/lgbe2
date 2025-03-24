<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// TestCase and refreshDatabase are now used from Pest.php

test('make:super-admin command can create a new super admin user', function () {
    // Generate a unique email and username for testing
    $email = 'testsuperadmin' . uniqid() . '@example.com';
    $username = 'testsuperadmin' . uniqid();

    // Run the command to create a super admin
    $this->artisan('make:super-admin', [
        '--create' => true,
        '--name' => 'Test Super Admin',
        '--username' => $username,
        '--email' => $email,
        '--password' => 'password123'
    ])->assertSuccessful();

    // Check that the super admin was created in the database
    $this->assertDatabaseHas('users', [
        'name' => 'Test Super Admin',
        'username' => $username,
        'email' => $email,
        'is_super_admin' => 1
    ]);
});

test('make:super-admin command can promote existing user to super admin', function () {
    // Create a regular user
    $user = User::factory()->create([
        'email' => 'regularuser' . uniqid() . '@example.com',
        'is_super_admin' => false
    ]);

    // Run the command to promote the user to super admin
    $this->artisan('make:super-admin', [
        '--email' => $user->email
    ])->assertSuccessful();

    // Check that the user was promoted to super admin
    $user->refresh();
    expect($user->is_super_admin)->toBeTrue();
});

test('delete:super-admin command can delete a super admin user', function () {
    // Create a super admin user
    $user = User::factory()->create([
        'email' => 'superadmintodelete' . uniqid() . '@example.com',
        'is_super_admin' => true
    ]);

    // Run the command to delete the super admin with confirmation
    $this->artisan('delete:super-admin', [
        'email' => $user->email
    ])->expectsConfirmation('Are you sure you want to delete super admin ' . $user->name . ' (' . $user->email . ')?', 'yes')
      ->assertSuccessful();

    // Check that the super admin was deleted from the database
    $this->assertDatabaseMissing('users', [
        'email' => $user->email
    ]);
});

test('delete:super-admin command fails for non-super admin users', function () {
    // Create a regular user
    $user = User::factory()->create([
        'email' => 'regularuser' . uniqid() . '@example.com',
        'is_super_admin' => false
    ]);

    // Run the command to delete the user
    $this->artisan('delete:super-admin', [
        'email' => $user->email
    ])->assertExitCode(1);

    // Check that the user still exists in the database
    $this->assertDatabaseHas('users', [
        'email' => $user->email
    ]);
});

test('super admin users cannot be deleted through web interface', function () {
    // Create a super admin user
    $user = User::factory()->create([
        'email' => 'superadmin' . uniqid() . '@example.com',
        'is_super_admin' => true
    ]);

    // Attempt to delete through profile controller
    $this->actingAs($user)
         ->delete('/profile', [
             'password' => 'password',
         ])
         ->assertSessionHasErrors('delete');

    // Check that the super admin still exists in the database
    $this->assertDatabaseHas('users', [
        'email' => $user->email
    ]);
});

test('middleware prevents modification of super admin status', function () {
    // Create a super admin user
    $user = User::factory()->create([
        'email' => 'superadmin' . uniqid() . '@example.com',
        'is_super_admin' => true
    ]);

    // Attempt to update user profile with is_super_admin set to false
    $this->actingAs($user)
         ->patch('/profile', [
             'name' => 'Updated Name',
             'is_super_admin' => false
         ]);

    // Check that the super admin status was not changed
    $user->refresh();
    expect($user->is_super_admin)->toBeTrue();
});
