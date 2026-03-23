<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@nurtureville.test'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('password'),
                'user_type' => 'super_admin',
                'is_active' => true,
            ]
        );
        $superAdmin->assignRole('super_admin');

        // A test teacher account
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@nurtureville.test'],
            [
                'name'      => 'Test Teacher',
                'password'  => Hash::make('password'),
                'user_type' => 'teacher',
                'is_active' => true,
            ]
        );
        $teacher->assignRole('teacher');
    }
}
