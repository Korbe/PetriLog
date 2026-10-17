<?php

namespace App\Console\Commands;

use App\Support\RichText;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SanitizeRichText extends Command
{
    // php artisan richtext:sanitize --dry-run
    protected $signature = 'richtext:sanitize {--dry-run : Nur anzeigen, wie viele Einträge geändert würden}';
    protected $description = 'Bereinigt bestehendes Rich-Text-HTML (Fang-Bemerkungen, Tickets) mit der VEditor-Allowlist';

    /**
     * Tabelle => Spalten mit Rich-Text aus dem VEditor
     */
    protected array $columns = [
        'catcheds' => ['remark'],
        'tickets' => ['description', 'steps'],
    ];

    public function handle()
    {
        $dryRun = (bool) $this->option('dry-run');

        foreach ($this->columns as $table => $columns) {
            $changed = 0;

            // Direkt über den Query Builder, damit updated_at und Model-Events unberührt bleiben
            DB::table($table)
                ->select(array_merge(['id'], $columns))
                ->orderBy('id')
                ->chunkById(500, function ($rows) use ($table, $columns, $dryRun, &$changed) {
                    foreach ($rows as $row) {
                        $updates = [];

                        foreach ($columns as $column) {
                            $clean = RichText::sanitize($row->{$column});

                            if ($clean !== $row->{$column}) {
                                $updates[$column] = $clean;
                            }
                        }

                        if ($updates === []) {
                            continue;
                        }

                        $changed++;

                        if (! $dryRun) {
                            DB::table($table)->where('id', $row->id)->update($updates);
                        }
                    }
                });

            $this->info(($dryRun ? '[dry-run] ' : '') . "{$table}: {$changed} Einträge " . ($dryRun ? 'würden bereinigt' : 'bereinigt'));
        }

        return Command::SUCCESS;
    }
}
