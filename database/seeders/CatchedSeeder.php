<?php

namespace Database\Seeders;

use App\Models\Catched;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CatchedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Catched::factory()->count(150)->create();
    }
}
