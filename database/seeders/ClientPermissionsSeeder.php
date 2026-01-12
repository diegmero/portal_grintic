<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ClientPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Specific permissions for Client Users
        $permissions = [
            'view_projects', // New: Can see projects list
            'create_stages',
            'create_tasks',
            'create_subtasks',
            'upload_files',
            'edit_project', // Basic edit like name/description
            'view_financials', // See invoices/payments
            'create_comments',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info('Permisos de cliente actualizados correctamente.');

        // Ensure there is a 'client_user' role (optional, but good practice)
        // $role = Role::firstOrCreate(['name' => 'client_user', 'guard_name' => 'web']);
        // $role->givePermissionTo($permissions); // By default give all? Or none? checking...
        // Better to assign permissions directly to users for granularity as requested.
    }
}
