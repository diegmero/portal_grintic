<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions (optional, for now just using roles)
        // Permission::create(['name' => 'edit projects']);
        
        // create roles
        // create roles
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleClient = Role::firstOrCreate(['name' => 'client']);

        // Give permissions to roles like:
        // $roleAdmin->givePermissionTo(Permission::all());

        // Create a default Admin user if none exists
        if (User::where('email', 'admin@grinweb.com')->doesntExist()) {
             $user = User::create([
                'name' => 'Admin User',
                'email' => 'admin@grinweb.com',
                'password' => bcrypt('password'), // Change in prod
            ]);
            $user->assignRole($roleAdmin);
        }
    }
}
