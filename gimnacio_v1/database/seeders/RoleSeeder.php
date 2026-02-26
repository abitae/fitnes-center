<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guard = config('auth.defaults.guard');

        // Roles del sistema
        $roles = [
            'super_administrador',
            'administrador',
            'trainer',
            'caja',
            'vendedor',
            'cafetin',
            'nutricionista',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
        }

        // Permisos
        Permission::firstOrCreate(['name' => 'manage-users', 'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'manage-roles', 'guard_name' => $guard]);

        // Super administrador y administrador tienen gestión de usuarios y roles
        $superAdmin = Role::findByName('super_administrador', $guard);
        $superAdmin->givePermissionTo(['manage-users', 'manage-roles']);

        $admin = Role::findByName('administrador', $guard);
        $admin->givePermissionTo(['manage-users', 'manage-roles']);

        // Usuario inicial como super administrador
        $firstUser = User::where('email', 'abel.arana@hotmail.com')->first();
        if ($firstUser && ! $firstUser->hasRole('super_administrador')) {
            $firstUser->assignRole('super_administrador');
        }
    }
}
