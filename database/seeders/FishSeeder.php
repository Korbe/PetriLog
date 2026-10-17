<?php

namespace Database\Seeders;

use App\Models\Fish;
use Database\Seeders\Concerns\UsesSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FishSeeder extends Seeder
{
    use UsesSeedData;

    public function run(): void
    {
        $fish = $this->seedData('fish');

        DB::transaction(function () use ($fish) {
            foreach ($fish as $data) {
                // is_default ist nicht fillable → forceFill
                $model = Fish::firstOrNew(['name' => $data['name']]);
                $model->forceFill([
                    'desc' => $data['desc'] ?? null,
                    'is_default' => (bool) ($data['is_default'] ?? false),
                ])->save();

                $this->attachSeedImage($model, $data['image'] ?? null, 'fish');
            }
        });
    }
}
