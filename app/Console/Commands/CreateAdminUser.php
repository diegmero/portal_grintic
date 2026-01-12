<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {email} {password} {name=Admin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user with the admin role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->argument('name');

        if (User::where('email', $email)->exists()) {
            $this->error('User with this email already exists!');
            return;
        }

        // Ensure Admin Role exists
        if (!Role::where('name', 'admin')->exists()) {
             // Fallback if migration didn't run or role missing
            Role::create(['name' => 'admin']);
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'company_id' => 1, // Default company/tenant if needed
        ]);

        $user->assignRole('admin');

        $this->info("Admin user '{$name}' ({$email}) created successfully!");
    }
}
