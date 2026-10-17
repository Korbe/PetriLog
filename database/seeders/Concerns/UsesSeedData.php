<?php

namespace Database\Seeders\Concerns;

use RuntimeException;
use Spatie\MediaLibrary\HasMedia;

/**
 * Zugriff auf die versionierten Seed-Daten (database/seeders/data) und Bilder (database/seeders/assets).
 */
trait UsesSeedData
{
    /**
     * Liest database/seeders/data/{name}.json als Array.
     */
    protected function seedData(string $name): array
    {
        $path = database_path("seeders/data/{$name}.json");

        if (!is_file($path)) {
            throw new RuntimeException("Seed-Datei nicht gefunden: {$path}");
        }

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Absoluter Pfad zu einem Asset relativ zu database/seeders/assets.
     */
    protected function seedAsset(string $relativePath): string
    {
        $path = database_path('seeders/assets/' . ltrim($relativePath, '/'));

        if (!is_file($path)) {
            throw new RuntimeException("Seed-Asset nicht gefunden: {$path}");
        }

        return $path;
    }

    /**
     * Hängt ein Bild an, aber nur wenn die Collection noch leer ist (idempotent).
     * preservingOriginal(): das Asset bleibt im Repo liegen und wird nur kopiert.
     */
    protected function attachSeedImage(HasMedia $model, ?string $relativePath, string $collection): void
    {
        if (!$relativePath || $model->getMedia($collection)->isNotEmpty()) {
            return;
        }

        $model->addMedia($this->seedAsset($relativePath))
            ->preservingOriginal()
            ->toMediaCollection($collection);
    }

    /**
     * Löst Namen in IDs auf (Map name => id); unbekannte Namen → Exception mit Kontext.
     */
    protected function resolveIds(array $names, $idsByName, string $context): array
    {
        $missing = array_diff($names, $idsByName->keys()->all());

        if ($missing) {
            throw new RuntimeException("{$context}: nicht gefunden: " . implode(', ', $missing));
        }

        return $idsByName->only($names)->values()->all();
    }
}
