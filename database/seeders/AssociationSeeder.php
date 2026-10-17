<?php

namespace Database\Seeders;

use App\Models\Association;
use App\Models\State;
use Database\Seeders\Concerns\UsesSeedData;
use Illuminate\Database\Seeder;
use RuntimeException;

class AssociationSeeder extends Seeder
{
    use UsesSeedData;

    public function run(): void
    {
        $stateIds = State::pluck('id', 'name');

        foreach ($this->seedData('associations') as $data) {
            $stateId = $stateIds[$data['state']] ?? throw new RuntimeException(
                'State "' . $data['state'] . '" nicht gefunden für Verein "' . $data['name'] . '"'
            );

            Association::updateOrCreate(
                ['name' => $data['name']],
                [
                    'state_id' => $stateId,
                    'desc' => $data['desc'] ?? null,
                    'link' => $data['link'] ?? null,
                ]
            );
        }
    }
}
