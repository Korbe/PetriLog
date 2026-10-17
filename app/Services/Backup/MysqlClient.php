<?php

namespace App\Services\Backup;

use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Ruft mysqldump/mysql auf, ohne Zugangsdaten in Kommandozeile, Umgebung oder Logs zu legen:
 * Benutzer und Passwort stehen nur in einer temporären Optionsdatei (Rechte 0600), die sofort wieder gelöscht wird.
 */
class MysqlClient
{
    public function __construct(
        private string $host,
        private int $port,
        private string $username,
        private ?string $password,
        private ?string $socket = null,
    ) {
    }

    public static function forConnection(string $connection, ?string $username = null, ?string $password = null): self
    {
        $config = config("database.connections.{$connection}");

        if (! $config || ! in_array($config['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            throw new BackupException("Backups unterstützen nur MySQL/MariaDB (Verbindung „{$connection}“).", BackupException::CONFIG);
        }

        return new self(
            host: (string) ($config['host'] ?? '127.0.0.1'),
            port: (int) ($config['port'] ?? 3306),
            username: $username ?: (string) $config['username'],
            password: $username ? $password : ($config['password'] ?? null),
            socket: ($config['unix_socket'] ?? '') ?: null,
        );
    }

    public static function dumpBinary(): string
    {
        if ($binary = config('backup.database.mysqldump_binary')) {
            return $binary;
        }

        // Neben dem konfigurierten mysql-Client liegt meist auch mysqldump
        if ($mysql = config('backup.database.mysql_binary')) {
            $candidate = dirname($mysql) . DIRECTORY_SEPARATOR . (PHP_OS_FAMILY === 'Windows' ? 'mysqldump.exe' : 'mysqldump');
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return 'mysqldump';
    }

    public static function clientBinary(): string
    {
        return config('backup.database.mysql_binary') ?: 'mysql';
    }

    /**
     * Führt einen MySQL-Client mit Optionsdatei aus. $arguments darf keine Zugangsdaten enthalten.
     *
     * @param resource|string|null $input
     */
    public function run(string $binary, array $arguments, int $timeout, $input = null): \Illuminate\Contracts\Process\ProcessResult
    {
        $optionsFile = $this->writeOptionsFile();

        try {
            // --defaults-extra-file muss die erste Option sein
            $command = [$binary, "--defaults-extra-file={$optionsFile}", ...$arguments];

            $process = Process::timeout($timeout);
            if ($input !== null) {
                $process = $process->input($input);
            }

            return $process->run($command);
        } finally {
            @unlink($optionsFile);
        }
    }

    public function version(): string
    {
        $result = $this->run(self::clientBinary(), ['--batch', '--skip-column-names', '-e', 'SELECT VERSION()'], 30);

        if (! $result->successful()) {
            throw new BackupException('MySQL nicht erreichbar: ' . $this->sanitize($result->errorOutput()), BackupException::DATABASE);
        }

        return trim($result->output());
    }

    /**
     * Entfernt mögliche Zugangsdaten aus Fehlermeldungen, bevor sie geloggt werden.
     */
    public function sanitize(string $message): string
    {
        $message = trim($message);

        if ($this->password !== null && $this->password !== '') {
            $message = str_replace($this->password, '***', $message);
        }

        return mb_substr($message, 0, 2000);
    }

    private function writeOptionsFile(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'petrilog-my');

        if ($path === false) {
            throw new RuntimeException('Temporäre MySQL-Optionsdatei konnte nicht angelegt werden.');
        }

        @chmod($path, 0600);

        $quote = fn(string $value) => '"' . addcslashes($value, "\\\"") . '"';
        $lines = ['[client]', 'user=' . $quote($this->username)];

        if ($this->password !== null && $this->password !== '') {
            $lines[] = 'password=' . $quote($this->password);
        }
        if ($this->socket) {
            $lines[] = 'socket=' . $quote($this->socket);
        } else {
            $lines[] = 'host=' . $quote($this->host);
            $lines[] = 'port=' . $this->port;
        }

        file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL);

        return $path;
    }
}
