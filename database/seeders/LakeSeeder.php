<?php

namespace Database\Seeders;

use App\Models\Fish;
use App\Models\Lake;
use App\Models\State;
use Database\Seeders\Concerns\UsesSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LakeSeeder extends Seeder
{
    use UsesSeedData;

    public function run(): void
    {
        $lakes = $this->seedData('lakes');
        $stateIds = State::pluck('id', 'name');
        $fishIds = Fish::pluck('id', 'name');

        DB::transaction(function () use ($lakes, $stateIds, $fishIds) {
            foreach ($lakes as $data) {
                // is_default ist nicht fillable → forceFill
                $lake = Lake::firstOrNew(['name' => $data['name']]);
                $lake->forceFill([
                    'desc' => $data['desc'] ?? null,
                    'hint' => $data['hint'] ?? null,
                    'fishing_rights' => $data['fishing_rights'] ?? null,
                    'ticket_sales' => $data['ticket_sales'] ?? null,
                    'is_default' => (bool) ($data['is_default'] ?? false),
                ])->save();

                // Bundesländer und Fischarten (n:n) über Namen zuordnen
                $lake->states()->sync(
                    $this->resolveIds($data['states'] ?? [], $stateIds, 'State(s) für Lake "' . $lake->name . '"')
                );
                $lake->fish()->sync(
                    $this->resolveIds($data['fish'] ?? [], $fishIds, 'Fisch(e) für Lake "' . $lake->name . '"')
                );
            }
        });
    }
}
