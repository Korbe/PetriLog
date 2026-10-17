<?php

namespace App\Services\Backup;

use Carbon\CarbonImmutable;

/**
 * Großvater-Vater-Sohn-Aufbewahrung: je Tag/Woche/Monat wird das jeweils neueste Backup behalten.
 * Das allerneueste Backup bleibt immer erhalten.
 */
class RetentionPolicy
{
    public function __construct(
        private int $daily,
        private int $weekly,
        private int $monthly,
    ) {
    }

    public static function fromConfig(): self
    {
        $config = config('backup.retention.remote');

        return new self((int) $config['daily'], (int) $config['weekly'], (int) $config['monthly']);
    }

    /**
     * @param array<string, CarbonImmutable> $backups Dateiname => Erstellungszeitpunkt
     * @return array{keep: array<int, string>, delete: array<int, string>}
     */
    public function apply(array $backups): array
    {
        arsort($backups); // neueste zuerst

        $keep = [];
        if ($backups) {
            $keep[array_key_first($backups)] = true;
        }

        foreach ([
            [$this->daily, fn(CarbonImmutable $d) => $d->format('Y-m-d')],
            [$this->weekly, fn(CarbonImmutable $d) => $d->format('o-\WW')],
            [$this->monthly, fn(CarbonImmutable $d) => $d->format('Y-m')],
        ] as [$limit, $bucket]) {
            $seen = [];
            foreach ($backups as $name => $date) {
                $key = $bucket($date);
                if (isset($seen[$key])) {
                    continue;
                }
                if (count($seen) >= $limit) {
                    break;
                }
                $seen[$key] = true;
                $keep[$name] = true;
            }
        }

        $keepNames = array_keys($keep);

        return [
            'keep' => $keepNames,
            'delete' => array_values(array_diff(array_keys($backups), $keepNames)),
        ];
    }
}
