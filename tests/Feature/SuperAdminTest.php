<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
// TestCase and refreshDatabase are now used from Pest.php

test('super admin can be created via artisan command', function () {
    // Run the command to create a super admin
    artisan('make:super-admin', [
        '--create' => true,
        '--name' => 'Test Super Admin',
        '--username' => 'testsuperadmin',
        '--email' => 'testsuperadmin@example.com',
        '--password' => 'password123'
    ])->assertSuccessful();

    // Check that the super admin was created in the database
    test()->assertDatabaseHas('users', [
        'email' => 'testsuperadmin@example.com',
        'is_super_admin' => true
    ]);
});

test('super admin account is locked after 5 failed login attempts', function () {
    // Create a super admin user
    $user = User::factory()->create([
        'email' => 'locktestadmin@example.com',
        'password' => Hash::make('correct_password'),
        'is_super_admin' => true,
        'login_attempts' => 0,
        'locked_at' => null
    ]);

    // Create a login request
    $request = new \App\Http\Requests\Auth\LoginRequest();
    $request->merge([
        'email' => 'locktestadmin@example.com',
        'password' => 'wrong_password'
    ]);

    // Simulate 5 failed login attempts
    for ($i = 0; $i < 5; $i++) {
        try {
            $request->authenticate();
        } catch (ValidationException $e) {
            // Expected exception
        }

        // Refresh the user model
        $user->refresh();
        
        // Check login attempts are incremented
        expect($user->login_attempts)->toBe($i + 1);
        
        // On the last attempt, the account should be locked
        if ($i == 4) {
            expect($user->locked_at)->not->toBeNull();
        } else {
            expect($user->locked_at)->toBeNull();
        }
    }
});

test('locked super admin account cannot authenticate', function () {
    // Create a locked super admin user
    $user = User::factory()->create([
        'email' => 'lockedadmin@example.com',
        'password' => Hash::make('password123'),
        'is_super_admin' => true,
        'login_attempts' => 5,
        'locked_at' => now()
    ]);

    // Create a login request
    $request = new \App\Http\Requests\Auth\LoginRequest();
    $request->merge([
        'email' => 'lockedadmin@example.com',
        'password' => 'password123'
    ]);

    // Attempt to authenticate should throw an exception
    try {
        $request->authenticate();
        test()->fail('Expected ValidationException was not thrown');
    } catch (ValidationException $e) {
        // Check that the error message mentions the account is locked
        expect($e->errors()['email'][0])->toContain('locked');
    }
});

test('super admin account can be unlocked via artisan command', function () {
    // Create a locked super admin user
    $user = User::factory()->create([
        'email' => 'unlocktestadmin@example.com',
        'password' => Hash::make('old_password'),
        'is_super_admin' => true,
        'login_attempts' => 5,
        'locked_at' => now()
    ]);

    // Run the command to unlock the super admin
    artisan('unlock:super-admin', [
        'email' => 'unlocktestadmin@example.com',
        '--password' => 'new_password123'
    ])->assertSuccessful();

    // Refresh the user model
    $user->refresh();

    // Check that the account is unlocked
    expect($user->login_attempts)->toBe(0);
    expect($user->locked_at)->toBeNull();
    
    // Check that the password was changed
    expect(Hash::check('new_password123', $user->password))->toBeTrue();
});
