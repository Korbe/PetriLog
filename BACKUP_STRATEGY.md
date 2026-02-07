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

## Backup-Script auf Server A

#!/bin/bash
set -euo pipefail


#### Lokale Backup-Konfiguration

DB=""
MYSQL_USER=""
MYSQL_PASSWORD=""

BACKUP_ROOT="/var/www/petrilog.com/backup"
DATE=$(date +%Y-%m-%d)
BACKUP_DIR="$BACKUP_ROOT/$DATE"

MEDIA_SRC="/var/www/petrilog.com/shared/storage/app/public"
MEDIA_ZIP="$BACKUP_DIR/media.zip"


#### Mail-Konfiguration

EMAIL="info@petrilog.com"
HOSTNAME=$(hostname)


#### Remote Server Konfiguration

REMOTE_USER=""
REMOTE_HOST=""
REMOTE_DIR="/backups/petrilog/$DATE"


#### Fehlerhandler

error_handler() {
    echo -e "Subject: Backup FAILED auf $HOSTNAME\n\nDas Backup auf $HOSTNAME am $(date) hat nicht geklappt." | \
    msmtp "$EMAIL"
    exit 1
}
trap error_handler ERR


#### Backup-Verzeichnis erstellen

mkdir -p "$BACKUP_DIR" || error_handler


#### DB Struktur sichern

mysqldump -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" \
  --no-data --add-drop-table \
  --ignore-table=${DB}.jobs \
  --ignore-table=${DB}.failed_jobs \
  "$DB" > "$BACKUP_DIR/structure.sql" || error_handler


### DB Daten sichern

mysqldump -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" \
  --no-create-info \
  --ignore-table=${DB}.cache \
  --ignore-table=${DB}.cache_locks \
  --ignore-table=${DB}.pulse \
  --ignore-table=${DB}.pulse_aggregates \
  --ignore-table=${DB}.pulse_entries \
  --ignore-table=${DB}.pulse_values \
  --ignore-table=${DB}.sessions \
  "$DB" > "$BACKUP_DIR/data.sql" || error_handler


### Media-Dateien sichern

if [ -d "$MEDIA_SRC" ]; then
    cd "$MEDIA_SRC" || error_handler
    zip -rq "$MEDIA_ZIP" . || error_handler
else
    echo "Media-Verzeichnis nicht gefunden: $MEDIA_SRC"
fi


### Alte Backups lokal rotieren (7 Tage)

cd "$BACKUP_ROOT" || error_handler
ls -1dt */ | tail -n +8 | xargs -r rm -rf || error_handler


### Kopie auf Remote Server via SSH/SCP

ssh "$REMOTE_USER@$REMOTE_HOST" "mkdir -p $REMOTE_DIR" || error_handler
scp "$BACKUP_DIR"/* "$REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR/" || error_handler


### Prüfen, ob Dateien erfolgreich kopiert wurden

REMOTE_FILES=("$REMOTE_DIR/structure.sql" "$REMOTE_DIR/data.sql" "$REMOTE_DIR/media.zip")

for f in "${REMOTE_FILES[@]}"; do
    LOCAL_SIZE=$(stat -c%s "$BACKUP_DIR/$(basename $f)")
    REMOTE_SIZE=$(ssh "$REMOTE_USER@$REMOTE_HOST" "stat -c%s $f")
    if [ "$LOCAL_SIZE" -ne "$REMOTE_SIZE" ]; then
        echo "Fehler: Datei $f hat auf Remote andere Größe ($REMOTE_SIZE) als lokal ($LOCAL_SIZE)!"
        error_handler
    fi
done


### Erfolgs-Mail

echo -e "Subject: Backup erfolgreich auf $HOSTNAME\n\nDas Backup auf $HOSTNAME am $(date) wurde erfolgreich erstellt.\n\nDateien:\n$BACKUP_DIR/structure.sql\n$BACKUP_DIR/data.sql\n$MEDIA_ZIP\n\nKopie auf Remote Server: $REMOTE_HOST:$REMOTE_DIR" | \
msmtp "$EMAIL"

echo "Backup für $DATE erfolgreich abgeschlossen."


## Cronjob auf Server A
sudo crontab -e

0 0 * * * /var/www/petrilog/backup/backup.sh >> /path/petrilog-backup.log 2>&1

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
sudo crontab -e

30 0 * * * /backups/petrilog/petrilog-remote-rotate.sh >> /backups/petrilog/petrilog-backup-cleanup.log 2>&1
