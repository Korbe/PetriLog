# Backups & Wiederherstellung

PetriLog sichert **Datenbank und hochgeladene Dateien gemeinsam** in einer verschlüsselten Datei. Nur so passen
nach einer Wiederherstellung IDs, Fremdschlüssel und Fotos wieder zusammen. Der Wiki-Export
(Development → Wiki herunterladen) ist **kein** Backup: Er enthält keine User, Fänge oder Fotos.

| Was | Wo |
|---|---|
| Befehle | `php artisan backup:*` |
| Weboberfläche | Admin → Development → Datenbank-Backups |
| Konfiguration | `config/backup.php`, Werte aus `.env` |
| Lokale Backups | `storage/backups/` (Rechte 0700, nicht öffentlich erreichbar) |
| Protokoll | `storage/logs/backup-YYYY-MM-DD.log`, Tabelle `backup_runs`, auf dem Zweitserver `petrilog-backup.log` |

---

## 1. Was ein Backup enthält

```
petrilog-backup-2026-10-17_011500.zip        (AES-256, Name in UTC)
├─ manifest.json        Tabellen mit exakten Zeilenzahlen, SHA-256 jeder Datei, Migrationsstand, Code-Revision
├─ database.sql         mysqldump der gesamten Datenbank
└─ storage/app/public/… Fang-Fotos, Fischbilder, Wappen (Spatie Media Library: {media_id}/{datei})
   storage/app/private/…
petrilog-backup-2026-10-17_011500.zip.sha256 (prüfbar mit `sha256sum -c`)
```

**Datenbank** – `mysqldump` mit:

- `--single-transaction`: konsistenter InnoDB-Schnappschuss, während die App weiterläuft (alle Tabellen sind InnoDB), ohne Tabellensperren.
- `--create-options`, `--add-drop-table` und `--triggers`: Tabellen, Primärschlüssel, Indizes, Fremdschlüssel-Constraints, Trigger und `AUTO_INCREMENT`-Zähler.
- `--skip-extended-insert`: eine Zeile pro `INSERT`. So stehen im Manifest die *exakten* Zeilenzahlen des Schnappschusses, und der Wiederherstellungstest kann sie 1:1 vergleichen.
- Von `sessions`, `cache`, `cache_locks` und `pulse_*` wird **nur die Struktur** gesichert (`backup.database.structure_only_tables`). Alte Sitzungen und veralteter Cache würden nach einer Wiederherstellung schaden; nach dem Einspielen sind deshalb alle abgemeldet. `jobs` und `failed_jobs` werden vollständig gesichert.
- `--set-gtid-purged=OFF`: Der Dump lässt sich in jede Instanz und jeden Datenbanknamen importieren.

**Dateien** – alle Dateien aus `storage/app/public` und `storage/app/private`. Erfasst die Media-Tabelle ein Foto, dessen Datei fehlt, steht das als Hinweis im Backup (auch Abweichungen in der Groß-/Kleinschreibung, die unter Linux zum Problem werden).

**Konsistenz** – Die Datei entsteht als `*.zip.part`. Erst wenn jede enthaltene Datei entschlüsselt und gegen das Manifest gehasht wurde, wird sie umbenannt und die `.sha256` geschrieben. Abgebrochene oder beschädigte Backups gelten nie als erfolgreich.

> Dateien, die *während* des Backups hochgeladen werden, landen ggf. ohne DB-Zeile im Archiv (harmlos). Wird zwischen DB-Schnappschuss und Dateikopie ein Foto gelöscht, fehlt es im Archiv. Das meldet der Hinweis im Manifest.

---

## 2. Einrichtung auf dem Produktivserver

### 2.1 `.env`

```dotenv
# Pflicht in Produktion – ohne Passwort bricht backup:run ab
BACKUP_ENCRYPTION_PASSWORD="<mindestens 32 zufällige Zeichen>"

# Optional: eigener, rein lesender MySQL-User nur für den Dump (empfohlen, siehe 2.2)
BACKUP_DB_USERNAME=petrilog_backup
BACKUP_DB_PASSWORD="<passwort>"

# Optional: Pfade, falls mysqldump/mysql nicht im PATH liegen
#BACKUP_MYSQLDUMP_PATH=/usr/bin/mysqldump
#BACKUP_MYSQL_PATH=/usr/bin/mysql

# Lokale Ablage (Standard: storage/backups) und Aufbewahrung
#BACKUP_LOCAL_PATH=/var/www/petrilog.com/shared/storage/backups
BACKUP_LOCAL_KEEP=1
BACKUP_KEEP_DAILY=7
BACKUP_KEEP_WEEKLY=5
BACKUP_KEEP_MONTHLY=12

# Isolierte Datenbank für Wiederherstellungstests (niemals die produktive!)
BACKUP_RESTORE_TEST_DATABASE=petrilog_restore_test
#BACKUP_RESTORE_DB_USERNAME=petrilog_restore
#BACKUP_RESTORE_DB_PASSWORD="<passwort>"
```

> **Das Verschlüsselungspasswort zusätzlich außerhalb des Servers verwahren** (Passwort-Manager, ausgedruckt im Safe). Ohne dieses Passwort lässt sich *kein* Backup wiederherstellen, auch nicht vom Zweitserver.

### 2.2 MySQL-Benutzer mit minimalen Rechten

```sql
-- Nur lesen: reicht für mysqldump --single-transaction
CREATE USER 'petrilog_backup'@'localhost' IDENTIFIED BY '<passwort>';
GRANT SELECT, SHOW VIEW, TRIGGER, LOCK TABLES ON petrilog.* TO 'petrilog_backup'@'localhost';

-- Nur für Wiederherstellungstests: ausschließlich auf die Test-Datenbank
CREATE USER 'petrilog_restore'@'localhost' IDENTIFIED BY '<passwort>';
GRANT ALL PRIVILEGES ON petrilog_restore_test.* TO 'petrilog_restore'@'localhost';
FLUSH PRIVILEGES;
```

Zugangsdaten landen nie in der Kommandozeile (`ps`), in Umgebungsvariablen von Kindprozessen oder in Logs. Für jeden Aufruf von `mysqldump`/`mysql` wird eine temporäre Optionsdatei mit Rechten 0600 geschrieben (`--defaults-extra-file`) und sofort wieder gelöscht. Fehlermeldungen werden vor dem Loggen um das Passwort bereinigt.

### 2.3 Voraussetzungen prüfen

```bash
cd /var/www/petrilog.com/current
php artisan migrate --force      # legt die Tabelle backup_runs an
php artisan backup:doctor
```

`backup:doctor` prüft `mysqldump` und `mysql`, die Datenbankverbindung, ZIP/AES-256, den Backup-Ordner (beschreibbar und *nicht* unter `public/`), den freien Speicher und den Zweitserver (Fingerprint, Schreibrecht).

---

## 3. Automatisierung (Cron)

Backups laufen unabhängig vom Browser. Variante A ist empfohlen:

**A) Eigener Cronjob** – Vorlage: [`scripts/backup/petrilog-backup.cron`](../scripts/backup/petrilog-backup.cron)

```bash
sudo cp scripts/backup/petrilog-backup.cron /etc/cron.d/petrilog-backup
sudo chmod 644 /etc/cron.d/petrilog-backup
```

Täglich läuft [`scripts/backup/petrilog-backup.sh`](../scripts/backup/petrilog-backup.sh), sonntags zusätzlich `backup:restore-test --drop`. Das Skript:

- wechselt selbst zu `deployer` (Besitzer der App) und läuft damit ohne Root-Rechte, auch wenn cron es als root startet,
- ruft `php artisan backup:run` auf (Erstellen, Prüfen, SFTP-Übertragung, Aufräumen),
- prüft das Backup zusätzlich unabhängig auf dem Zweitserver per `ssh … sha256sum -c` (abschaltbar mit `REMOTE_SHELL_CHECK=0` bei reinem SFTP-Chroot),
- loggt nach `/var/log/petrilog-backup.log` und schickt per `msmtp` eine Mail bei Fehlern (mit erklärtem Exit-Code) und optional bei Erfolg (`MAIL_ON_SUCCESS=0` = nur Fehler).

Es enthält **keine Passwörter**; alles kommt aus der `.env`.

**B) Laravel-Scheduler** – nur wenn ohnehin `* * * * * php artisan schedule:run` läuft:

```dotenv
BACKUP_SCHEDULE=true
BACKUP_SCHEDULE_TIME=03:15   # Europe/Vienna
```

Nicht beide Varianten gleichzeitig aktivieren. Parallele Läufe verhindert ohnehin eine Dateisperre (`storage/backups/.backup.lock`). Ein zweiter Lauf beendet sich mit Exit-Code 3.

### Exit-Codes

| Code | Bedeutung |
|---|---|
| 0 | Erfolg (Hinweise möglich, z. B. fehlende Bilddateien) |
| 1 | Allgemeiner Fehler |
| 2 | Konfiguration (Passwort fehlt, Datei nicht gefunden, ungültiger Name) |
| 3 | Es läuft bereits ein Backup |
| 4 | Datenbank (Dump fehlgeschlagen/unvollständig, Server nicht erreichbar) |
| 5 | Archiv (Schreiben, Speicherplatz) |
| 6 | Prüfung fehlgeschlagen (beschädigt, falsches Passwort) |
| 7 | Übertragung zum Zweitserver |
| 8 | Wiederherstellung |

---

## 4. Zweitserver (SFTP)

Der Produktivserver lädt jedes Backup per SFTP hoch:

1. Upload als `*.zip.part`.
2. SHA-256 der Datei **auf dem Zweitserver** berechnen und mit dem lokalen Wert vergleichen.
3. Erst bei Übereinstimmung auf den endgültigen Namen umbenennen und `.sha256` ablegen.

Lokale Backups werden erst gelöscht, wenn ihre Übertragung bestätigt in `backup_runs` steht.

### 4.1 Backup-Server vorbereiten

```bash
# Eigener Benutzer ohne Shell, nur SFTP, eingesperrt in seinen Ordner
sudo adduser --disabled-password --shell /usr/sbin/nologin petrilog-backup
sudo mkdir -p /srv/backups/petrilog-chroot/backups
sudo chown root:root /srv/backups/petrilog-chroot && sudo chmod 755 /srv/backups/petrilog-chroot
sudo chown petrilog-backup: /srv/backups/petrilog-chroot/backups && sudo chmod 700 /srv/backups/petrilog-chroot/backups
```

`/etc/ssh/sshd_config` auf dem Backup-Server:

```
Match User petrilog-backup
    ChrootDirectory /srv/backups/petrilog-chroot
    ForceCommand internal-sftp
    AllowTcpForwarding no
    X11Forwarding no
    PasswordAuthentication no
```

### 4.2 SSH-Schlüssel (auf dem Produktivserver, als `deployer`)

```bash
ssh-keygen -t ed25519 -N '' -C 'petrilog-backup' -f ~/.ssh/petrilog_backup
chmod 600 ~/.ssh/petrilog_backup
# Öffentlichen Schlüssel auf dem Backup-Server eintragen:
#   /home/petrilog-backup/.ssh/authorized_keys  (Rechte 600, Besitzer petrilog-backup)

# Host-Fingerprint des Backup-Servers ermitteln (gegen Man-in-the-Middle).
# Flysystem erwartet ein eigenes Format (Hex, MD5 bei RSA bzw. SHA-512 sonst) – nicht „SHA256:…“:
php artisan backup:fingerprint backup.example.com
# → den angezeigten OpenSSH-Wert auf dem Backup-Server gegenprüfen (ssh-keygen -lf …), dann die Zeile in die .env
```

### 4.3 `.env`

```dotenv
BACKUP_REMOTE_ENABLED=true
BACKUP_REMOTE_HOST=backup.example.com
BACKUP_REMOTE_PORT=22
BACKUP_REMOTE_USERNAME=petrilog-backup
BACKUP_REMOTE_PRIVATE_KEY=/home/deployer/.ssh/petrilog_backup
BACKUP_REMOTE_HOST_FINGERPRINT="ab:cd:…"    # Ausgabe von php artisan backup:fingerprint
BACKUP_REMOTE_PATH=/backups                 # relativ zum Chroot
BACKUP_REMOTE_PRUNE=false                   # Aufräumen übernimmt der Backup-Server selbst (4.4)
```

Danach `php artisan backup:doctor` (prüft Verbindung, Fingerprint und Schreibrecht) und `php artisan backup:run`.

### 4.4 Prüfung & Aufbewahrung auf dem Backup-Server

**PetriLog-Aufbewahrung:**
- **Server A** behält das neueste Backup (`BACKUP_LOCAL_KEEP=1`). Ältere werden erst nach bestätigter Übertragung gelöscht; schlägt die Übertragung fehl, bleiben sie liegen.
- **Server B** hält gekennzeichnete Plätze: `taeglich/` (neuestes), `woechentlich-sonntag/` (letzter Sonntag) und `monatlich/` (je das erste Backup des aktuellen und des Vormonats).
- **Sonntags** läuft automatisch ein Wiederherstellungstest mit dem frischen Backup. Das Ergebnis kommt per Mail.

Für PetriLog auf korbitsch.at ist das [`scripts/backup-server/petrilog-remote-rotate.sh`](../scripts/backup-server/petrilog-remote-rotate.sh): die angepasste Version des bisherigen Rotations-Skripts. Es versteht das neue Format und lässt die alten Tagesordner auslaufen. Als allgemeinere Variante gibt es [`scripts/backup-server/verify-and-prune.sh`](../scripts/backup-server/verify-and-prune.sh). Beide laufen **auf dem Backup-Server**:

- prüft jedes neue Backup unabhängig per `sha256sum -c`,
- meldet, wenn das neueste Backup älter als 30 Stunden ist,
- löscht liegengebliebene `*.part`-Dateien,
- setzt die Aufbewahrung durch (Standard: 7 täglich, 5 wöchentlich, 12 monatlich).

```bash
sudo install -m 750 verify-and-prune.sh /opt/petrilog/verify-and-prune.sh
# crontab des Besitzers des Backup-Ordners:
30 5 * * * KEEP_DAILY=7 KEEP_WEEKLY=5 KEEP_MONTHLY=12 /opt/petrilog/verify-and-prune.sh /srv/backups/petrilog-chroot/backups >> /var/log/petrilog-backup-check.log 2>&1
```

So braucht der Produktivserver **kein Löschrecht**: Selbst ein kompromittierter Produktivserver kann bestehende Backups nicht entfernen. Soll der Produktivserver doch selbst aufräumen, setze `BACKUP_REMOTE_PRUNE=true`.

---

## 5. Wiederherstellung

### 5.1 Wiederherstellung testen (isoliert, gefahrlos)

```bash
php artisan backup:list --remote                 # 1. Backup auswählen
php artisan backup:verify petrilog-backup-….zip  # 2. Integrität & Vollständigkeit
php artisan backup:restore-test petrilog-backup-….zip   # 3.–5.
```

`backup:restore-test` schreibt **nur** in `BACKUP_RESTORE_TEST_DATABASE` und verweigert den Namen der produktiven Datenbank. Die Dateien landen in `storage/backups/restore-test/…`. Danach wird geprüft:

| Prüfung | Ergebnis bei Fehler |
|---|---|
| Alle Tabellen vorhanden | Fehler |
| Zeilenzahl jeder Tabelle exakt wie im Schnappschuss | Fehler |
| Fremdschlüssel: keine verwaisten Datensätze | Fehler |
| `AUTO_INCREMENT` jeder Tabelle > höchste ID | Fehler |
| Bilder ohne zugehörigen Datensatz (polymorph, ohne FK) | Hinweis |
| Bilddateien zu allen Media-Einträgen vorhanden | Hinweis |
| Migrationsstand gegenüber dem aktuellen Code | Hinweis |
| Funktionsprobe (User, neuester Fang inkl. Fischart) | Fehler, falls Abfrage scheitert |

Die Test-Datenbank bleibt zur Kontrolle bestehen; `--drop` entfernt sie wieder, `--keep-files` behält die Dateien. Über die Weboberfläche (Button „Wiederherstellung testen“) wird die Test-Datenbank immer wieder entfernt.

Ein Backup vom Zweitserver testen:

```bash
scp petrilog-backup@backup.example.com:/backups/petrilog-backup-….zip* /tmp/
php artisan backup:restore-test /tmp/petrilog-backup-….zip
```

### 5.2 Notfall: produktiv wiederherstellen

```bash
cd /var/www/petrilog.com/current
php artisan backup:verify /pfad/petrilog-backup-….zip
php artisan backup:restore-production /pfad/petrilog-backup-….zip
#   → fragt den Datenbanknamen zur Bestätigung ab (nicht-interaktiv: --confirm=<datenbankname>)
```

Ablauf:

1. Backup vollständig prüfen. Ein beschädigtes Backup wird nie eingespielt.
2. Sicherheits-Backup des aktuellen Stands erstellen (überspringen mit `--skip-safety-backup`, wenn die Datenbank zerstört ist).
3. Wartungsmodus einschalten (`php artisan down`).
4. Dump einspielen. Tabellen werden mit ihren ursprünglichen IDs, Constraints und `AUTO_INCREMENT`-Werten neu angelegt.
5. Dateien zurückschreiben: identische Dateien werden übersprungen, alle anderen kommen an ihren ursprünglichen Pfad. Damit passen die Fotos wieder zu den Fängen.
6. Alle Prüfungen aus 5.1 ausführen und bei Bedarf `php artisan migrate`.
7. Caches leeren und den Wartungsmodus beenden. Bei Fehlern bleibt die App im Wartungsmodus.

Auf einem **komplett neuen Server**:

1. Code deployen, `.env` inklusive `BACKUP_ENCRYPTION_PASSWORD` einrichten.
2. Eine leere Datenbank anlegen.
3. `php artisan backup:restore-production <datei> --skip-safety-backup`.
4. `php artisan storage:link`.

### 5.3 Ohne Laravel (letzter Ausweg)

Das ZIP lässt sich mit 7-Zip oder `7z x` (AES-256) und dem Verschlüsselungspasswort öffnen. `database.sql` mit `mysql <db> < database.sql` einspielen und `storage/` an den ursprünglichen Ort kopieren.

---

## 6. Weboberfläche

Admin → **Development** → **Datenbank-Backups**. Der ganze Bereich verlangt eine erneute Passwort-Eingabe.

- **Backup erstellen & herunterladen**: läuft im Hintergrund über die Queue (ein Queue-Worker muss laufen) und lädt das fertige, verschlüsselte ZIP automatisch herunter.
  - Diese Backups werden **nicht** auf den Backup-Server übertragen (`backup:run --download`) und verdrängen das aufbewahrte nächtliche Backup nicht.
  - Die Kopie auf dem Server löscht der nächste nächtliche Lauf (sofern sie mindestens eine Stunde alt ist).
- **Prüfen** und **Wiederherstellung testen** (immer isoliert): ebenfalls im Hintergrund, mit Status im Verlauf.
- **Download**: Die Datei bleibt verschlüsselt, jeder Download wird protokolliert.
- **Backup hochladen**: z. B. eine Datei von Server B oder ein zuvor heruntergeladenes Backup.
  - Der Upload läuft in 8-MB-Teilstücken und ist damit unabhängig von `upload_max_filesize` und nginx `client_max_body_size`. Das Maximum ist `BACKUP_UPLOAD_MAX_MB` (Standard 5 GB).
  - Danach wird die Datei vollständig geprüft. Ungültige oder **mit einem anderen `BACKUP_ENCRYPTION_PASSWORD` verschlüsselte** Dateien werden automatisch wieder entfernt.
- **Wiederherstellen** (produktiv): nur für geprüfte Backups, mit Eingabe des Datenbanknamens und einer Bestätigung. Ablauf wie in 5.2:
  1. Sicherheits-Backup des aktuellen Stands.
  2. Website-Sperre: Alle Anfragen bekommen eine Wartungsseite, die sich selbst neu lädt. Bewusst nicht `artisan down`, das würde den Queue-Worker anhalten.
  3. Wiederherstellung und Prüfung.
  4. Danach sind alle abgemeldet.

  Schlägt etwas fehl, bleibt die Sperre bestehen. Aufheben durch Löschen von `storage/backups/.restore-in-progress.json`; nach 3 Stunden gilt sie ohnehin als verwaist. Ist die Website selbst nicht mehr erreichbar, geht alles weiterhin per Kommandozeile (5.2).

Alle Development-Werkzeuge sind nur für Admins erreichbar (`isAdmin`-Middleware). Neue Werkzeuge werden in `App\Http\Controllers\Admin\DevelopmentController::tools()` ergänzt.

---

## 7. Wenn etwas hängt (ohne Datenbank-Zugriff)

Alle Befehle auf Server A als root.

**1. Lagebild:**

```bash
runuser -u deployer -- php /var/www/petrilog.com/current/artisan backup:status
```

Zeigt, ob gerade ein Backup läuft (mit Prozess-ID), offene Aufträge, das letzte erfolgreiche Backup, Fehler der letzten 24 Stunden, Speicherplatz und Queue-Stau. Hängengebliebene Statuseinträge werden dabei automatisch aufgeräumt. Exit-Code 0 = in Ordnung, 1 = Warnung.

**2. Selbstheilung (passiert automatisch, auch beim Öffnen der Backup-Seite):**

| Situation | Reaktion |
|---|---|
| Backup-Prozess abgestürzt oder beendet (Neustart, Deploy) | sofort als abgebrochen markiert (Backup-Sperre ist frei) |
| Auftrag seit 2 Std. nicht gestartet (kein Queue-Worker) | als fehlgeschlagen markiert |
| Vorgang seit 6 Std. nicht fertig | als abgebrochen markiert |
| `mysqldump` hängt | wird nach `BACKUP_DUMP_TIMEOUT` (Standard 1 Std.) beendet |

**3. Ein Backup läuft wirklich, hängt aber** (`backup:status` zeigt „Backup läuft: ja (Prozess-ID …)" seit Stunden):

```bash
ps -fp <Prozess-ID>
```

```bash
kill <Prozess-ID>
```

Danach erkennt `backup:status` den Abbruch und räumt den Eintrag auf.

**4. Das Admin-Dashboard warnt**, wenn das letzte erfolgreiche Backup älter als 26 Stunden ist. Das passiert z. B., wenn der Cron gar nicht läuft und deshalb auch keine Fehler-Mail kommt.

Ein laufendes Backup blockiert die Website nicht: Der Dump liest mit `--single-transaction` und `--skip-lock-tables` und sperrt keine Tabellen.

---

## 8. Hinweise

- Der ältere Befehl `backup:restore` (lädt Backups mit Root-Passwort per SFTP von „Server A“ und **löscht** dabei lokal `storage/app/public`) ist unverändert vorhanden, wird aber nicht mehr empfohlen. Nutze stattdessen `backup:restore-test` bzw. `backup:restore-production` mit einer heruntergeladenen Datei.
- Queue: Erstellt die Weboberfläche ein Backup, muss `retry_after` der Queue-Verbindung (Standard 90 s) größer sein als die Backup-Dauer. Sonst kann ein zweiter Worker den Auftrag erneut starten (die Dateisperre verhindert dann einen doppelten Lauf). Für große Datenbestände Backups primär per Cron ausführen.
- Speicherbedarf: Pro Backup wird etwa die doppelte Datenbankgröße plus die Größe von `storage/` kurzzeitig benötigt (Dump + Archiv). `backup:run` prüft das vorher.
