<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin12345'),
                'role' => 'admin',
            ],
            [
                'name' => 'Guide User',
                'email' => 'guide@example.com',
                'password' => Hash::make('guide12345'),
                'role' => 'guide',
            ],
            [
                'name' => 'Client User',
                'email' => 'client@example.com',
                'password' => Hash::make('client12345'),
                'role' => 'client',
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['email' => $data['email']], $data);
        }
    }
}
