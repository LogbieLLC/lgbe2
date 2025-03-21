<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:super-admin {email : Email of the super admin to delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a super admin user';

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
        
        // Confirm deletion
        if (!$this->confirm("Are you sure you want to delete super admin {$user->name} ({$user->email})?")) {
            $this->info('Operation cancelled.');
            return 0;
        }
        
        $user->delete();
        
        $this->info("Super admin {$user->name} ({$user->email}) has been deleted.");
        return 0;
    }
}
