# Petrilog Backup & Remote Rotation – Komplett-Anleitung

Dieses Setup erstellt automatische Backups deiner Laravel-App, verschiebt sie auf einen Remote-Server und hält dort nur die letzten 7 Backups. Zusätzlich werden Erfolgs- und Fehler-Mails verschickt. Alles läuft automatisch per Cronjob.

---

### Voraussetzungen

- Linux-Server A (lokal) mit Zugriff auf DB und Media  
- Linux-Server B (Remote) mit SFTP/SSH-Zugang  
- `msmtp` für Mails installiert  
- SSH-Keys für Passwortloser Zugriff auf Remote-Server  
- Cronjob-Zugriff (`crontab -e`)  

---

# Backup Script

    #!/bin/bash
    set -euo pipefail

    # ==============================
    # Logging (alles wird geloggt)
    # ==============================
    LOG_FILE="/var/log/petrilog-backup.log"
    exec > >(tee -a "$LOG_FILE") 2>&1

    # ==============================
    # Lokale Backup-Konfiguration
    # ==============================
    DB="petrilog_db"
    MYSQL_USER="root"
    MYSQL_PASSWORD='petrilog-mysql-pw'

    BACKUP_ROOT="/var/www/petrilog.com/backup"
    DATE=$(date +%Y-%m-%d)
    BACKUP_DIR="$BACKUP_ROOT/$DATE"

    MEDIA_SRC="/var/www/petrilog.com/shared/storage/app/public"
    MEDIA_ZIP="$BACKUP_DIR/media.zip"

    # ==============================
    # Mail-Konfiguration
    # ==============================
    EMAIL="info@petrilog.com"
    HOSTNAME=$(hostname)

    # ==============================
    # Remote Server Konfiguration
    # ==============================
    REMOTE_USER="backupuser"
    REMOTE_HOST="korbitsch.at"
    REMOTE_DIR="/backups/petrilog/$DATE"

    # ==============================
    # Fehlerhandler (verbessert)
    # ==============================
    error_handler() {
        local exit_code=$?
        local line_no=$1
        local last_command="$2"

        MESSAGE="Backup FAILED on $HOSTNAME

    Date: $(date)
    Exit Code: $exit_code
    Line: $line_no
    Command: $last_command

    Last 20 log lines:
    $(tail -n 20 "$LOG_FILE")"

        echo -e "Subject: Backup FAILED on $HOSTNAME\n\n$MESSAGE" | msmtp "$EMAIL"
        exit $exit_code
    }

    trap 'error_handler $LINENO "$BASH_COMMAND"' ERR

    echo "==== Backup gestartet: $(date) ===="

    # ==============================
    # Backup-Verzeichnis erstellen
    # ==============================
    mkdir -p "$BACKUP_DIR"

    # ==============================
    # 1️⃣ DB Struktur sichern
    # ==============================
    echo "Sichere DB Struktur..."
    mysqldump -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" \
    --no-data --add-drop-table \
    --ignore-table=${DB}.jobs \
    --ignore-table=${DB}.failed_jobs \
    "$DB" > "$BACKUP_DIR/structure.sql"

    # ==============================
    # 2️⃣ DB Daten sichern
    # ==============================
    echo "Sichere DB Daten..."
    mysqldump -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" \
    --no-create-info \
    --ignore-table=${DB}.cache \
    --ignore-table=${DB}.cache_locks \
    --ignore-table=${DB}.pulse \
    --ignore-table=${DB}.pulse_aggregates \
    --ignore-table=${DB}.pulse_entries \
    --ignore-table=${DB}.pulse_values \
    --ignore-table=${DB}.sessions \
    "$DB" > "$BACKUP_DIR/data.sql"

    # ==============================
    # 3️⃣ Media-Dateien sichern
    # ==============================
    echo "Sichere Media-Dateien..."
    if [ -d "$MEDIA_SRC" ]; then
        cd "$MEDIA_SRC"
        zip -rq "$MEDIA_ZIP" .
    else
        echo "WARNUNG: Media-Verzeichnis nicht gefunden: $MEDIA_SRC"
    fi

    # ==============================
    # 4️⃣ Alte Backups rotieren
    # ==============================
    echo "Lösche alte Backups..."
    cd "$BACKUP_ROOT"
    ls -1dt */ | tail -n +8 | xargs -r rm -rf

    # ==============================
    # 5️⃣ Upload auf Remote Server
    # ==============================
    echo "Kopiere Backup auf Remote Server..."
    ssh "$REMOTE_USER@$REMOTE_HOST" "mkdir -p $REMOTE_DIR"
    scp "$BACKUP_DIR"/* "$REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/"

    # ==============================
    # Verifikation der Dateien
    # ==============================
    echo "Überprüfe Dateien..."
    REMOTE_FILES=(
        "$REMOTE_DIR/structure.sql"
        "$REMOTE_DIR/data.sql"
        "$REMOTE_DIR/media.zip"
    )

    for f in "${REMOTE_FILES[@]}"; do
        LOCAL_FILE="$BACKUP_DIR/$(basename "$f")"

        if [ ! -f "$LOCAL_FILE" ]; then
            echo "WARNUNG: Datei fehlt lokal: $LOCAL_FILE"
            continue
        fi

        LOCAL_SIZE=$(stat -c%s "$LOCAL_FILE")
        REMOTE_SIZE=$(ssh "$REMOTE_USER@$REMOTE_HOST" "stat -c%s $f")

        if [ "$LOCAL_SIZE" -ne "$REMOTE_SIZE" ]; then
            echo "Fehler: Datei $f hat andere Größe (Remote: $REMOTE_SIZE / Lokal: $LOCAL_SIZE)"
            exit 1
        fi
    done

    # ==============================
    # Erfolgs-Mail
    # ==============================
    echo -e "Subject: Backup erfolgreich auf $HOSTNAME\n\nDas Backup auf $HOSTNAME am $(date) wurde erfolgreich erstellt." | msmtp "$EMAIL"

    echo "==== Backup erfolgreich abgeschlossen: $(date) ===="


## Cronjob auf Server A
sudo crontab -e

0 0 * * * /var/www/petrilog.com/backup/backup.sh >> /var/www/petrilog.com/backup/backup.log 2>&1

### Remote Cleanup-Script auf Server B

    #!/bin/bash
    set -euo pipefail

    BACKUP_ROOT="/backups/petrilog"
    LOGFILE="/backups/petrilog/petrilog-backup-cleanup.log"

    echo "========================================" >> "$LOGFILE"
    echo "Cleanup gestartet: $(date)" >> "$LOGFILE"

    cd "$BACKUP_ROOT" || { echo "Backup-Verzeichnis nicht gefunden!" >> "$LOGFILE"; exit 1; }

    BACKUP_FOLDERS=( $(ls -1dt */) )
    KEEP=7

    if [ "${#BACKUP_FOLDERS[@]}" -le "$KEEP" ]; then
        echo "Keine alten Backups zu löschen. ${#BACKUP_FOLDERS[@]} Ordner vorhanden." >> "$LOGFILE"
    else
        OLD_FOLDERS=( "${BACKUP_FOLDERS[@]:$KEEP}" )
        for f in "${OLD_FOLDERS[@]}"; do
            rm -rf "$f"
            echo "Altes Backup gelöscht: $f" >> "$LOGFILE"
        done
    fi

    echo "Cleanup abgeschlossen: $(date)" >> "$LOGFILE"
    echo "" >> "$LOGFILE"

## Cronjob auf Server B
auführbar machen
    chmod +x /backups/petrilog/petrilog-remote-rotate.sh

owner change
    chown backupuser:backupuser /backups/petrilog/petrilog-remote-rotate.sh
    chown backupuser:backupuser /backups/petrilog/petrilog-backup-cleanup.log

    sudo crontab -e

Jede Nacht um 30 nach Mitternacht wird gerotatet
30 0 * * * /backups/petrilog/petrilog-remote-rotate.sh >> /backups/petrilog/petrilog-backup-cleanup.log 2>&1
