<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class UnlockSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unlock:super-admin {email : Email of the super admin to unlock} {--password= : New password for the super admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unlock a locked super admin account and reset password';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("No user found with email: {$email}");
            return 1;
        }
        
        if (!$user->is_super_admin) {
            $this->error("User {$user->name} is not a super admin.");
            return 1;
        }
        
        if (!$user->locked_at) {
            $this->info("Super admin account {$user->name} ({$user->email}) is not locked.");
            return 0;
        }
        
        // Get new password
        $password = $this->option('password');
        if (!$password) {
            $password = $this->secret('Enter a new password for the super admin');
            $passwordConfirmation = $this->secret('Confirm the new password');
            
            if ($password !== $passwordConfirmation) {
                $this->error('Passwords do not match.');
                return 1;
            }
            
            if (strlen($password) < 8) {
                $this->error('Password must be at least 8 characters long.');
                return 1;
            }
        }
        
        // Unlock account and reset password
        $user->login_attempts = 0;
        $user->locked_at = null;
        $user->password = Hash::make($password);
        $user->save();
        
        $this->info("Super admin account {$user->name} ({$user->email}) has been unlocked and password reset.");
        return 0;
    }
}
