<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'korbitschl@gmail.com';

        $user = User::where('email', $email)->first();

        if (!$user) {
            User::create([
                'email' => $email,
                'name' => 'Lukas Korbitsch',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]);

            echo "Admin user created: {$email}\n";
        } else {
            echo "Admin user already exists: {$email}\n";
        }
    }
}
