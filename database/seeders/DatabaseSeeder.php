<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@goldencrm.com'],
            [
                'name'      => 'Admin',
                'phone'     => '9999999999',
                'role'      => 'admin',
                'password'  => Hash::make('admin@123'),
                'is_active' => true,
            ]
        );

        $counselors = [
            ['name' => 'Riya Sharma',  'email' => 'riya@goldencrm.com',  'phone' => '9000000001'],
            ['name' => 'Arjun Mehta',  'email' => 'arjun@goldencrm.com', 'phone' => '9000000002'],
            ['name' => 'Sneha Kapoor', 'email' => 'sneha@goldencrm.com', 'phone' => '9000000003'],
        ];

        foreach ($counselors as $c) {
            User::updateOrCreate(
                ['email' => $c['email']],
                array_merge($c, [
                    'role'      => 'counselor',
                    'password'  => Hash::make('counselor@123'),
                    'is_active' => true,
                ])
            );
        }
    }
}
