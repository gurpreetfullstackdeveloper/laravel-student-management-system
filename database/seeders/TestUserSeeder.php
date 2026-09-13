<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        User::updateOrCreate(
            ['email' => 'admin@testschool.local'],
            [
                'name' => 'Test Super Admin',
                'username' => 'testadmin',
                'role' => 'super_admin',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Admin@12345'),
            ]
        );

        // Teacher
        User::updateOrCreate(
            ['email' => 'teacher@testschool.local'],
            [
                'name' => 'Test Teacher',
                'username' => 'testteacher',
                'role' => 'teacher',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Teacher@12345'),
            ]
        );

        // Student
        User::updateOrCreate(
            ['email' => 'student@testschool.local'],
            [
                'name' => 'Test Student',
                'username' => 'teststudent',
                'role' => 'student',
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Student@12345'),
            ]
        );
    }
}
