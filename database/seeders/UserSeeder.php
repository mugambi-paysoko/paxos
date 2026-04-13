<?php

namespace Database\Seeders;

use App\Models\User;
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
        User::firstOrCreate(
            ['email' => 'admin@paxos.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Create Lender Users
        User::firstOrCreate(
            ['email' => 'lender@paxos.com'],
            [
                'name' => 'Lender User',
                'password' => Hash::make('password'),
                'role' => 'lender',
            ]
        );

        User::firstOrCreate(
            ['email' => 'lender2@paxos.com'],
            [
                'name' => 'Second Lender',
                'password' => Hash::make('password'),
                'role' => 'lender',
            ]
        );

        $this->command->info('Admin and Lender users created successfully!');
        $this->command->info('Admin: admin@paxos.com / password');
        $this->command->info('Lender 1: lender@paxos.com / password');
        $this->command->info('Lender 2: lender2@paxos.com / password');
    }
}
