<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
// TestCase and refreshDatabase are now used from Pest.php

test('make:super-admin command can create a new super admin user', function () {
    // Run the command to create a super admin
    $this->artisan('make:super-admin', [
        '--create' => true,
        '--name' => 'Test Super Admin',
        '--username' => 'testsuperadmin',
        '--email' => 'testsuperadmin@example.com',
        '--password' => 'password123'
    ])->assertSuccessful();

    // Check that the super admin was created in the database
    $this->assertDatabaseHas('users', [
        'name' => 'Test Super Admin',
        'username' => 'testsuperadmin',
        'email' => 'testsuperadmin@example.com',
        'is_super_admin' => true
    ]);
});

test('make:super-admin command can promote existing user to super admin', function () {
    // Create a regular user
    $user = User::factory()->create([
        'email' => 'regularuser@example.com',
        'is_super_admin' => false
    ]);

    // Run the command to promote the user to super admin
    $this->artisan('make:super-admin', [
        '--email' => 'regularuser@example.com'
    ])->assertSuccessful();

    // Check that the user was promoted to super admin
    $user->refresh();
    expect($user->is_super_admin)->toBeTrue();
});

test('delete:super-admin command can delete a super admin user', function () {
    // Create a super admin user
    $user = User::factory()->create([
        'email' => 'superadmintodelete@example.com',
        'is_super_admin' => true
    ]);

    // Run the command to delete the super admin with confirmation
    $this->artisan('delete:super-admin', [
        'email' => 'superadmintodelete@example.com'
    ])->expectsConfirmation('Are you sure you want to delete super admin ' . $user->name . ' (superadmintodelete@example.com)?', 'yes')
      ->assertSuccessful();

    // Check that the super admin was deleted from the database
    $this->assertDatabaseMissing('users', [
        'email' => 'superadmintodelete@example.com'
    ]);
});

test('delete:super-admin command fails for non-super admin users', function () {
    // Create a regular user
    $user = User::factory()->create([
        'email' => 'regularuser@example.com',
        'is_super_admin' => false
    ]);

    // Run the command to delete the user
    $this->artisan('delete:super-admin', [
        'email' => 'regularuser@example.com'
    ])->assertExitCode(1);

    // Check that the user still exists in the database
    $this->assertDatabaseHas('users', [
        'email' => 'regularuser@example.com'
    ]);
});

test('super admin users cannot be deleted through web interface', function () {
    // Create a super admin user
    $user = User::factory()->create([
        'email' => 'superadmin@example.com',
        'is_super_admin' => true
    ]);

    // Attempt to delete through profile controller
    $this->actingAs($user)
         ->delete('/profile')
         ->assertSessionHasErrors('delete');

    // Check that the super admin still exists in the database
    $this->assertDatabaseHas('users', [
        'email' => 'superadmin@example.com'
    ]);
});

test('middleware prevents modification of super admin status', function () {
    // Create a super admin user
    $user = User::factory()->create([
        'email' => 'superadmin@example.com',
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
