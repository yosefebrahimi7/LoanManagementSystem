<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => Hash::make('password'),
                'is_active' => true,
                'role' => User::ROLE_ADMIN,
            ]
        );

        // Create or update Regular Users
        $user1 = User::updateOrCreate(
            ['email' => 'test1@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'User 1',
                'password' => Hash::make('password'),
                'is_active' => true,
                'role' => User::ROLE_USER,
            ]
        );

        $user2 = User::updateOrCreate(
            ['email' => 'test2@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'User 2',
                'password' => Hash::make('password'),
                'is_active' => true,
                'role' => User::ROLE_USER,
            ]
        );

        // Create wallets for users if they don't exist
        Wallet::updateOrCreate(['user_id' => $admin->id], ['balance' => 0]);
        Wallet::updateOrCreate(['user_id' => $user1->id], ['balance' => 0]);
        Wallet::updateOrCreate(['user_id' => $user2->id], ['balance' => 0]);
    }
}
