<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:super-admin 
                            {--create : Create a new user as super admin}
                            {--email= : Email of the user to promote to super admin}
                            {--name= : Name for new super admin (when using --create)}
                            {--username= : Username for new super admin (when using --create)}
                            {--password= : Password for new super admin (when using --create)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or promote a user to super admin status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('create')) {
            $this->createSuperAdmin();
        } elseif ($this->option('email')) {
            $this->promoteSuperAdmin();
        } else {
            $this->error('Please specify either --create to create a new super admin or --email to promote an existing user.');
            return 1;
        }

        return 0;
    }

    /**
     * Create a new user with super admin status.
     */
    private function createSuperAdmin()
    {
        $name = $this->option('name');
        $username = $this->option('username');
        $email = $this->option('email');
        $password = $this->option('password');

        // Prompt for any missing information
        if (!$name) {
            $name = $this->ask('Enter the name for the super admin');
        }

        if (!$username) {
            $username = $this->ask('Enter the username for the super admin');
        }

        if (!$email) {
            $email = $this->ask('Enter the email for the super admin');
        }

        if (!$password) {
            $password = $this->secret('Enter the password for the super admin');
        }

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return;
        }

        // Create the super admin
        $superAdmin = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'karma' => 0,
            'is_super_admin' => true,
        ]);

        $this->info("Super admin created: {$superAdmin->name} ({$superAdmin->email})");
    }

    /**
     * Promote an existing user to super admin.
     */
    private function promoteSuperAdmin()
    {
        $email = $this->option('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("No user found with email: {$email}");
            return;
        }

        if ($user->is_super_admin) {
            $this->info("User {$user->name} is already a super admin.");
            return;
        }

        $user->is_super_admin = true;
        $user->save();

        $this->info("User {$user->name} ({$user->email}) has been promoted to super admin.");
    }
}
