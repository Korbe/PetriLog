<?php

namespace App\Services\Backup;

use Throwable;
use ZipArchive;

/**
 * Prüft ein Backup vollständig: Prüfsummendatei, ZIP-Struktur, Entschlüsselung und SHA-256 jeder enthaltenen Datei.
 * Nur ein Backup, das diese Prüfung besteht, gilt als gültig.
 */
class BackupVerifier
{
    /**
     * @return array{ok: bool, errors: array<int, string>, manifest: ?array, sha256: ?string}
     */
    public function verify(string $path, bool $requireChecksumFile = true): array
    {
        $errors = [];
        $manifest = null;
        $sha256 = null;

        if (! is_file($path)) {
            return ['ok' => false, 'errors' => ["Datei nicht gefunden: {$path}"], 'manifest' => null, 'sha256' => null];
        }

        // 1. Prüfsumme der Gesamtdatei (erkennt Übertragungs- und Speicherfehler)
        $sha256 = BackupFiles::sha256($path);
        $expected = BackupFiles::readChecksumFile($path);
        if ($expected !== null && ! hash_equals($expected, $sha256)) {
            $errors[] = 'SHA-256 der Datei stimmt nicht mit der .sha256-Datei überein.';
        } elseif ($expected === null && $requireChecksumFile) {
            $errors[] = 'Prüfsummendatei (.sha256) fehlt.';
        }

        // 2. ZIP-Konsistenz
        $zip = new ZipArchive();
        $opened = $zip->open($path, ZipArchive::CHECKCONS | ZipArchive::RDONLY);
        if ($opened !== true) {
            $errors[] = "ZIP-Datei ist beschädigt (Code {$opened}).";
            return ['ok' => false, 'errors' => $errors, 'manifest' => null, 'sha256' => $sha256];
        }

        try {
            $this->applyPassword($zip);

            // 3. Manifest lesen (schlägt bei falschem Passwort fehl)
            $raw = $zip->getFromName('manifest.json');
            if ($raw === false) {
                $errors[] = 'manifest.json fehlt oder lässt sich nicht entschlüsseln (Passwort prüfen).';
                return ['ok' => false, 'errors' => $errors, 'manifest' => null, 'sha256' => $sha256];
            }

            $manifest = json_decode($raw, true);
            if (! is_array($manifest) || ($manifest['format'] ?? null) !== BackupCreator::FORMAT_VERSION) {
                $errors[] = 'manifest.json ist ungültig oder hat ein unbekanntes Format.';
                return ['ok' => false, 'errors' => $errors, 'manifest' => null, 'sha256' => $sha256];
            }

            // 4. Datenbank-Dump
            $database = $manifest['database'] ?? [];
            $dumpHash = $this->hashEntry($zip, 'database.sql');
            if ($dumpHash === null) {
                $errors[] = 'database.sql fehlt oder ist nicht lesbar.';
            } elseif (! hash_equals((string) ($database['sha256'] ?? ''), $dumpHash)) {
                $errors[] = 'database.sql ist beschädigt (SHA-256 weicht ab).';
            }

            // 5. Alle Storage-Dateien
            $expectedFiles = $manifest['files']['items'] ?? [];
            $broken = [];
            foreach ($expectedFiles as $file) {
                $hash = $this->hashEntry($zip, $file['path']);
                if ($hash === null || ! hash_equals($file['sha256'], $hash)) {
                    $broken[] = $file['path'];
                }
            }
            if ($broken) {
                $errors[] = count($broken) . ' Datei(en) fehlen oder sind beschädigt: ' . implode(', ', array_slice($broken, 0, 5));
            }

            // Erwartete Einträge: Manifest + Dump + Dateien
            if ($zip->numFiles !== count($expectedFiles) + 2) {
                $errors[] = "Unerwartete Anzahl an Einträgen im ZIP ({$zip->numFiles} statt " . (count($expectedFiles) + 2) . ').';
            }
        } catch (Throwable $e) {
            $errors[] = "Prüfung abgebrochen: {$e->getMessage()}";
        } finally {
            $zip->close();
        }

        return ['ok' => $errors === [], 'errors' => $errors, 'manifest' => $manifest, 'sha256' => $sha256];
    }

    /**
     * Liest nur das Manifest (für Übersichten) – ohne vollständige Prüfung.
     */
    public function manifest(string $path): ?array
    {
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            return null;
        }

        try {
            $this->applyPassword($zip);
            $raw = $zip->getFromName('manifest.json');

            return $raw === false ? null : json_decode($raw, true);
        } finally {
            $zip->close();
        }
    }

    public function applyPassword(ZipArchive $zip): void
    {
        $password = config('backup.encryption_password');

        if (is_string($password) && $password !== '') {
            $zip->setPassword($password);
        }
    }

    private function hashEntry(ZipArchive $zip, string $name): ?string
    {
        $stream = $zip->getStream($name);
        if ($stream === false) {
            return null;
        }

        $context = hash_init('sha256');
        while (! feof($stream)) {
            $chunk = fread($stream, 1048576);
            if ($chunk === false) {
                fclose($stream);
                return null;
            }
            hash_update($context, $chunk);
        }
        fclose($stream);

        return hash_final($context);
    }
}
