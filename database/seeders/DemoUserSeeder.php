<?php

namespace Database\Seeders;

use App\Models\Catched;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\River;
use App\Models\State;
use App\Models\User;
use Database\Seeders\Concerns\UsesSeedData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Demo-Account mit realistischen Fängen (Kärnten/Steiermark) und Fotos.
 * Idempotent: Fänge des Demo-Users werden bei jedem Lauf gelöscht und neu angelegt.
 */
class DemoUserSeeder extends Seeder
{
    use UsesSeedData;

    public const EMAIL = 'demo@petrilog.com';

    /**
     * Einheiten: length cm, weight g, depth cm, temperature °C (Wasser), air_pressure hPa.
     * days_ago/time → Datum relativ zu now(). Fisch und Gewässer per Name (exakt wie in data/*.json).
     */
    /**
     * Bewusst nur 4 Fänge: Ohne Abo sind 5 erlaubt, so kann der Demo-User selbst noch einen anlegen.
     */
    private const CATCHES = [
        [
            'fish' => 'Hecht', 'lake' => 'Wörthersee', 'days_ago' => 3, 'time' => '17:40',
            'length' => 78, 'weight' => 3650, 'depth' => 450, 'temperature' => 14, 'air_pressure' => 1018,
            'bait' => 'Gummifisch 12 cm, Barsch-Dekor',
            'latitude' => 46.6330, 'longitude' => 14.1400, 'address' => 'Wörthersee, Pörtschach am Wörthersee',
            'remark' => '<p>Biss direkt an der Schilfkante, kurz bevor die Sonne weg war. <strong>Schöner Herbsthecht</strong>, schonend released.</p>',
            'photos' => ['catches/hecht-1.jpg'],
        ],
        [
            'fish' => 'Regenbogenforelle', 'lake' => 'Millstätter See', 'days_ago' => 40, 'time' => '06:45',
            'length' => 46, 'weight' => 1150, 'depth' => 400, 'temperature' => 18, 'air_pressure' => 1017,
            'bait' => 'Blinker 15 g, kupfer',
            'latitude' => 46.7950, 'longitude' => 13.5800, 'address' => 'Millstätter See, Millstatt',
            'remark' => '<p>Weit ausgeworfen und langsam eingeholt. <strong>Mehrere Sprünge</strong> im Drill.</p>',
            'photos' => ['catches/regenbogenforelle-1.jpg', 'catches/regenbogenforelle-2.jpg'],
        ],
        [
            // Schwerster Fang (Dashboard-Highlight)
            'fish' => 'Spiegelkarpfen', 'lake' => 'Faaker See', 'days_ago' => 95, 'time' => '05:50',
            'length' => 81, 'weight' => 13400, 'depth' => 350, 'temperature' => 21, 'air_pressure' => 1014,
            'bait' => 'Boilie Fisch/Krill 24 mm',
            'latitude' => 46.5780, 'longitude' => 13.9150, 'address' => 'Faaker See, Faak am See',
            'remark' => '<p>Neuer persönlicher Rekord beim Karpfen. <em>Was für ein Morgen!</em></p>',
            'photos' => ['catches/spiegelkarpfen-2.jpg'],
        ],
        [
            // Längster Fang (Dashboard-Highlight)
            'fish' => 'Huchen', 'river' => 'Drau', 'days_ago' => 180, 'time' => '07:30',
            'length' => 112, 'weight' => 12800, 'depth' => 250, 'temperature' => 6, 'air_pressure' => 1024,
            'bait' => 'Huchenzopf 18 cm',
            'latitude' => 46.6130, 'longitude' => 13.8500, 'address' => 'Drau, Villach',
            'remark' => '<p><strong>Fisch des Lebens!</strong> Nach drei Wintern endlich ein Huchen über einem Meter. <em>Nach dem Foto schonend zurückgesetzt.</em></p>',
            'photos' => ['catches/huchen-1.jpg'],
        ],
    ];

    public function run(): void
    {
        $user = User::where('email', self::EMAIL)->first();

        if (!$user) {
            $user = User::create([
                'name' => 'Demo Angler',
                'email' => self::EMAIL,
                'password' => Hash::make(env('DEMO_USER_PASSWORD', 'password')),
                'state_id' => State::where('name', 'Kärnten')->value('id')
                    ?? throw new RuntimeException('State "Kärnten" nicht gefunden – StatesSeeder zuerst ausführen'),
            ]);

            // E-Mail von vornherein bestätigt (email_verified_at ist nicht fillable)
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $fishIds = Fish::pluck('id', 'name');
        $lakes = Lake::with('fish:id')->get()->keyBy('name');
        $rivers = River::with('fish:id')->get()->keyBy('name');

        DB::transaction(function () use ($user, $fishIds, $lakes, $rivers) {
            // Vorhandene Fänge inkl. Medien entfernen (delete() löscht die Fotos mit)
            $user->catched()->get()->each->delete();

            foreach (self::CATCHES as $data) {
                $water = isset($data['lake']) ? $lakes[$data['lake']] ?? null : $rivers[$data['river']] ?? null;
                $waterName = $data['lake'] ?? $data['river'];
                $fishId = $fishIds[$data['fish']] ?? null;

                if (!$water || !$fishId) {
                    throw new RuntimeException("Demo-Fang: Fisch \"{$data['fish']}\" oder Gewässer \"{$waterName}\" nicht gefunden");
                }

                // Nur Arten, die laut Stammdaten im Gewässer vorkommen
                if (!$water->fish->contains('id', $fishId)) {
                    throw new RuntimeException("Demo-Fang: \"{$data['fish']}\" ist \"{$waterName}\" nicht zugeordnet");
                }

                $catch = Catched::create([
                    'user_id' => $user->id,
                    'fish_id' => $fishId,
                    'lake_id' => isset($data['lake']) ? $water->id : null,
                    'river_id' => isset($data['river']) ? $water->id : null,
                    'date' => now()->subDays($data['days_ago'])->setTimeFromTimeString($data['time']),
                    ...collect($data)->only([
                        'length', 'weight', 'depth', 'temperature', 'air_pressure', 'bait',
                        'latitude', 'longitude', 'address', 'remark',
                    ])->all(),
                ]);

                foreach ($data['photos'] as $order => $photo) {
                    $catch->addMedia($this->seedAsset($photo))
                        ->preservingOriginal()
                        ->setOrder($order)
                        ->toMediaCollection('photos');
                }
            }
        });
    }
}
