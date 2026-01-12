<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class FixRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure necessary roles exist in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = ['admin', 'client'];

        foreach ($roles as $roleName) {
            if (Role::where('name', $roleName)->doesntExist()) {
                Role::create(['name' => $roleName]);
                $this->info("Role '{$roleName}' created.");
            } else {
                $this->line("Role '{$roleName}' already exists.");
            }
        }

        $this->info('Roles check completed.');
    }
}
