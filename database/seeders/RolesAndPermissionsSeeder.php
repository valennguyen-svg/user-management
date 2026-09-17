<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'users.view']);
        Permission::create(['name' => 'users.create']);
        Permission::create(['name' => 'users.update']);
        Permission::create(['name' => 'users.delete']);
        Permission::create(['name' => 'users.export']);
        Permission::create(['name' => 'users.import']);

        // update cache to know about the newly created permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles and assign created permissions

        // this can be done as separate statements
        $role = Role::create(['name' => 'staff']);
        $role->givePermissionTo('users.view');

        // or may be done by chaining
        $role = Role::create(['name' => 'admin'])
            ->givePermissionTo(Permission::all());

        // create demo users and assign roles
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'status' => true,
        ]);
        $admin->assignRole('admin');

        $staff = User::create([
            'name' => 'Staff Demo',
            'email' => 'staff@example.com',
            'password' => Hash::make('password123'),
            'status' => true,
        ]);
        $staff->assignRole('staff');

        // seed multiple users and then assign each of them a role, WITHOUT using Factory States
        User::factory()
            ->count(20)
            ->create(['status' => true])
            ->each(function ($user) {
                $user->assignRole('staff');
            });
    }
}
