<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('local')) {
            $this->call([
                StatesSeeder::class,
                AssociationSeeder::class,
                FishSeeder::class,
                LakeSeeder::class,
                RiverSeeder::class,
                AdminUserSeeder::class,
                DemoUserSeeder::class,
            ]);
        }
    }
}
