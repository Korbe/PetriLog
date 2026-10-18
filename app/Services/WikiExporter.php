<?php

namespace App\Services;

use App\Models\Association;
use App\Models\Fish;
use App\Models\Lake;
use App\Models\River;
use App\Models\State;
use finfo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Exportiert das Wiki (Bundesländer, Vereine, Fische, Seen, Flüsse) im Format von database/seeders/data/*.json,
 * inklusive Bildern für database/seeders/assets. Nur freigegebene Einträge – Vorschläge in Prüfung bleiben draußen.
 */
class WikiExporter
{
    public const DATASETS = ['states', 'associations', 'fish', 'lakes', 'rivers'];

    // Gleiche Formatierung wie die bestehenden Seed-Dateien (4 Leerzeichen, Umlaute & Slashes unverändert)
    private const JSON_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

    /** @var array<string, string> relativer Asset-Pfad => Dateiinhalt */
    private array $assets = [];

    /**
     * @return array<string, string> Pfad relativ zu database/seeders => Dateiinhalt
     */
    public function files(bool $withImages = true): array
    {
        $this->assets = [];
        $files = [];

        foreach (self::DATASETS as $dataset) {
            $files["data/{$dataset}.json"] = $this->json($this->dataset($dataset, $withImages));
        }

        foreach ($this->assets as $path => $contents) {
            $files["assets/{$path}"] = $contents;
        }

        return $files;
    }

    public function dataset(string $name, bool $withImages = false): array
    {
        $rows = match ($name) {
            'states' => $this->states($withImages),
            'associations' => $this->associations(),
            'fish' => $this->fish($withImages),
            'lakes' => $this->waters(Lake::class),
            'rivers' => $this->waters(River::class),
        };

        return $this->keepSeedOrder($name, $rows);
    }

    /**
     * Übernimmt die Reihenfolge der bestehenden Seed-Datei, damit ein Re-Export im Git-Diff nur echte
     * Änderungen zeigt. Neue Einträge landen (in ihrer Sortierung) am Ende.
     */
    private function keepSeedOrder(string $name, array $rows): array
    {
        $path = database_path("seeders/data/{$name}.json");

        try {
            $existing = is_file($path) ? json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR) : [];
        } catch (Throwable) {
            $existing = [];
        }

        $position = array_flip(array_column($existing, 'name'));
        $fallback = count($position);
        $existingByName = array_column($existing, null, 'name');

        // Auch Listen innerhalb eines Eintrags (Fischarten, Bundesländer) behalten ihre Reihenfolge
        $rows = array_map(function (array $row) use ($existingByName) {
            foreach (['fish', 'states'] as $list) {
                $previous = $existingByName[$row['name']][$list] ?? null;
                if (! isset($row[$list]) || ! is_array($previous)) {
                    continue;
                }
                $order = array_flip($previous);
                $values = $row[$list];
                usort($values, fn($a, $b) => [$order[$a] ?? PHP_INT_MAX, $a] <=> [$order[$b] ?? PHP_INT_MAX, $b]);
                $row[$list] = $values;
            }

            return $row;
        }, $rows);

        // Stabil sortieren: bekannte Namen nach alter Position, neue dahinter in bisheriger Reihenfolge
        $indexed = array_map(null, array_keys($rows), $rows);
        usort($indexed, fn($a, $b) => [($position[$a[1]['name']] ?? $fallback), $a[0]] <=> [($position[$b[1]['name']] ?? $fallback), $b[0]]);

        return array_column($indexed, 1);
    }

    public function json(array $data): string
    {
        // Windows-Zeilenenden vermeiden, damit Git-Diffs sauber bleiben
        return json_encode($data, self::JSON_FLAGS) . "\n";
    }

    private function states(bool $withImages): array
    {
        return State::with('media')->orderBy('id')->get()
            ->map(fn(State $state) => [
                'name' => $state->name,
                'desc' => $state->desc,
                'image' => $this->image($state, 'state', 'states', $withImages),
            ])
            ->all();
    }

    private function associations(): array
    {
        return Association::with('state:id,name')->orderBy('name')->get()
            ->map(fn(Association $association) => [
                'name' => $association->name,
                'state' => $association->state?->name,
                'desc' => $association->desc,
                'link' => $association->link,
            ])
            ->all();
    }

    private function fish(bool $withImages): array
    {
        return Fish::approved()->with('media')->orderByDesc('is_default')->orderBy('name')->get()
            ->map(fn(Fish $fish) => [
                'name' => $fish->name,
                'is_default' => (bool) $fish->is_default,
                'desc' => $fish->desc,
                'image' => $this->image($fish, 'fish', 'fish', $withImages),
            ])
            ->all();
    }

    /**
     * @param class-string<Lake|River> $model
     */
    private function waters(string $model): array
    {
        return $model::approved()
            ->with(['states:id,name', 'fish' => fn($q) => $q->approved()->orderBy('name')])
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get()
            ->map(fn(Model $water) => [
                'name' => $water->name,
                'is_default' => (bool) $water->is_default,
                'states' => $water->states->sortBy('name')->pluck('name')->values()->all(),
                'desc' => $water->desc,
                'hint' => $water->hint,
                'fishing_rights' => $water->fishing_rights,
                'ticket_sales' => $water->ticket_sales,
                'fish' => $water->fish->pluck('name')->values()->all(),
            ])
            ->all();
    }

    /**
     * Liefert den Asset-Pfad (z. B. "fish/aesche.jpg") und merkt sich das Bild für den ZIP-Export.
     * Einzel-JSON und ZIP erzeugen dieselben Pfade, damit beide zusammenpassen.
     */
    private function image(Model $model, string $collection, string $folder, bool $withImages): ?string
    {
        /** @var Media|null $media */
        $media = $model->getFirstMedia($collection);

        if (! $media) {
            return null;
        }

        try {
            $contents = Storage::disk($media->disk)->get($media->getPathRelativeToRoot());
        } catch (Throwable) {
            $contents = null;
        }

        // Datei fehlt im Storage – kein toter Verweis im Seed (der Seeder würde sonst abbrechen)
        if ($contents === null || $contents === '') {
            return null;
        }

        // Die Bildoptimierung ersetzt Originale durch WebP – Endung daher nach echtem Inhalt wählen
        $extension = $this->extensionFor($contents) ?? (strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION)) ?: 'jpg');
        $slug = Str::slug($model->name, '-', 'de');
        // Wappen heißen wie auf dem Produktivsystem mit großem Anfangsbuchstaben (Kaernten.png, Niederoesterreich.png)
        if ($folder === 'states') {
            $slug = ucfirst($slug);
        }
        $path = $folder . '/' . $slug . '.' . $extension;

        if ($withImages) {
            $this->assets[$path] = $contents;
        }

        return $path;
    }

    private function extensionFor(string $contents): ?string
    {
        return match ((new finfo(FILEINFO_MIME_TYPE))->buffer($contents)) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            default => null,
        };
    }
}
