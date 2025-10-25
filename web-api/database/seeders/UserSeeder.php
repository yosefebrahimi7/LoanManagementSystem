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
        // Create Admin User
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'role' => User::ROLE_ADMIN,
        ]);

        // Create Regular Users
        $user1 = User::create([
            'first_name' => 'Test',
            'last_name' => 'User 1',
            'email' => 'test1@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'role' => User::ROLE_USER,
        ]);

        $user2 = User::create([
            'first_name' => 'Test',
            'last_name' => 'User 2',
            'email' => 'test2@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'role' => User::ROLE_USER,
        ]);

        // Create wallets for users
        Wallet::create(['user_id' => $admin->id, 'balance' => 0]);
        Wallet::create(['user_id' => $user1->id, 'balance' => 0]);
        Wallet::create(['user_id' => $user2->id, 'balance' => 0]);
        
    }
}
