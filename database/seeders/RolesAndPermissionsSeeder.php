<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // Student management
            'view students',
            'create students',
            'edit students',
            'delete students',

            // Enrolment
            'manage enrolments',

            // Results
            'enter results',
            'upload results',
            'publish results',
            'view results',

            // Fees
            'manage fee structures',
            'record payments',
            'view fees',

            // Lesson notes
            'upload lesson notes',
            'view lesson notes',

            // Messaging
            'send messages',
            'view messages',

            // System
            'manage users',
            'manage classes',
            'manage subjects',
            'manage sessions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all()); // gets everything

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view students', 'create students', 'edit students',
            'manage enrolments',
            'publish results', 'view results',
            'manage fee structures', 'record payments', 'view fees',
            'view lesson notes',
            'send messages', 'view messages',
            'manage classes', 'manage subjects', 'manage sessions',
        ]);

        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->syncPermissions([
            'view students',
            'enter results', 'upload results', 'view results',
            'upload lesson notes', 'view lesson notes',
            'view messages',
        ]);

        $parent = Role::firstOrCreate(['name' => 'parent']);
        $parent->syncPermissions([
            'view results',
            'view fees',
            'view lesson notes',
            'view messages',
        ]);
    }
}
