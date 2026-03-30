<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Students
            'view students',
            'create students',
            'edit students',
            'delete students',

            // Enrolment
            'manage enrolments',

            // Results
            'enter results',
            'publish results',
            'view results',

            // Fees
            'manage fee structures',
            'generate invoices',
            'record payments',
            'view fees',
            'view financial reports',

            // Messages
            'send messages',
            'view messages',

            // System / admin
            'manage users',
            'manage classes',
            'manage subjects',
            'manage sessions',
            'manage school settings',
            'delete records',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ── Super Admin — everything ──────────────────────────────────────────
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions(Permission::all());

        // ── Admin — most things, no destructive or system actions ─────────────
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view students', 'create students', 'edit students',
            'manage enrolments',
            'enter results', 'publish results', 'view results',
            'manage fee structures', 'generate invoices', 'record payments',
            'view fees', 'view financial reports',
            'send messages', 'view messages',
            'manage classes', 'manage subjects',
        ]);

        // ── Accountant — finance only ─────────────────────────────────────────
        $accountant = Role::firstOrCreate(['name' => 'accountant']);
        $accountant->syncPermissions([
            'view students',       // need to see who the invoice belongs to
            'view fees',
            'record payments',
            'generate invoices',
            'view financial reports',
        ]);

        // ── Teacher — own class results only ──────────────────────────────────
        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->syncPermissions([
            'view students',
            'enter results',
            'view results',
        ]);

        // ── Parent — read-only, own children ─────────────────────────────────
        $parent = Role::firstOrCreate(['name' => 'parent']);
        $parent->syncPermissions([
            'view results',
            'view fees',
            'view messages',
        ]);
    }
}
