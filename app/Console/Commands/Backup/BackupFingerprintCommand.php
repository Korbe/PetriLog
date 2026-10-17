<?php

namespace App\Console\Commands\Backup;

use Illuminate\Console\Command;
use phpseclib3\Net\SSH2;
use Throwable;

/**
 * Ermittelt den Host-Fingerprint des Backup-Servers im Format, das Flysystem/phpseclib erwartet
 * (Hex mit Doppelpunkten: MD5 bei ssh-rsa, sonst SHA-512) – plus den OpenSSH-Fingerprint zum Abgleich.
 */
class BackupFingerprintCommand extends Command
{
    protected $signature = 'backup:fingerprint {host? : Standard: BACKUP_REMOTE_HOST} {--port=}';

    protected $description = 'Zeigt den Host-Fingerprint des Backup-Servers für BACKUP_REMOTE_HOST_FINGERPRINT';

    public function handle(): int
    {
        $host = $this->argument('host') ?: config('backup.remote.host');
        $port = (int) ($this->option('port') ?: config('backup.remote.port', 22));

        if (! $host) {
            $this->error('Kein Host angegeben (BACKUP_REMOTE_HOST).');
            return 2;
        }

        try {
            $ssh = new SSH2($host, $port, 15);
            $key = $ssh->getServerPublicHostKey();
        } catch (Throwable $e) {
            $this->error("Verbindung zu {$host}:{$port} fehlgeschlagen: {$e->getMessage()}");
            return 7;
        }

        if (! $key) {
            $this->error('Der Server hat keinen Host-Schlüssel geliefert.');
            return 7;
        }

        [$type, $blob] = explode(' ', $key, 3) + [null, null];
        $raw = base64_decode((string) $blob);
        $algo = $type === 'ssh-rsa' ? 'md5' : 'sha512';
        $flysystem = implode(':', str_split(hash($algo, $raw), 2));
        $openssh = 'SHA256:' . rtrim(base64_encode(hash('sha256', $raw, true)), '=');

        $this->line("Host-Schlüssel: {$type}");
        $this->line("OpenSSH (zum Abgleich mit `ssh-keygen -lf`): {$openssh}");
        $this->newLine();
        $this->info('In die .env eintragen:');
        $this->line("BACKUP_REMOTE_HOST_FINGERPRINT=\"{$flysystem}\"");
        $this->newLine();
        $this->warn('Vorher auf Server B prüfen, dass der OpenSSH-Wert übereinstimmt:');
        $this->line('  ssh-keygen -lf /etc/ssh/ssh_host_' . match ($type) {
            'ssh-ed25519' => 'ed25519',
            'ssh-rsa' => 'rsa',
            default => 'ecdsa',
        } . '_key.pub -E sha256');

        return self::SUCCESS;
    }
}
