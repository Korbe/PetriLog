#!/bin/bash
#
# Läuft auf SERVER B (korbitsch.at) – sortiert neue PetriLog-Backups in drei gekennzeichnete Plätze:
#
#   /backups/petrilog/taeglich/               → immer das neueste Backup
#   /backups/petrilog/woechentlich-sonntag/   → das Backup vom letzten Sonntag
#   /backups/petrilog/monatlich/              → je das erste Backup des aktuellen und des Vormonats
#
# „taeglich“ und „woechentlich-sonntag“ enthalten genau ein Backup, „monatlich“ zwei (+ .sha256).
# Ein Platz wird erst ersetzt, wenn das neue Backup die
# SHA-256-Prüfung bestanden hat – ein beschädigtes Backup verdrängt nie ein gutes.
# Gleiche Backups in mehreren Plätzen sind Hardlinks und belegen den Speicher nur einmal.
#
# Server A lädt in /backups/petrilog/ hoch; dieses Skript räumt den Ordner danach wieder leer.
#
# Cron (als backupuser), täglich nach dem Upload von Server A (03:15):
#   30 5 * * * /bin/bash /home/backupuser/petrilog-remote-rotate.sh
set -euo pipefail

# ==============================
# Konfiguration
# ==============================
BACKUP_ROOT="/backups/petrilog"
LOGFILE="/backups/petrilog/petrilog-backup-cleanup.log"
KEEP_OLD_FOLDERS=3   # alte Tagesordner (bisheriges Format), bis sie auslaufen
KEEP_MONTHS=2        # Monatsplatz: aktueller Monat + Vormonat

DAILY="$BACKUP_ROOT/taeglich"
WEEKLY="$BACKUP_ROOT/woechentlich-sonntag"
MONTHLY="$BACKUP_ROOT/monatlich"
FAILED="$BACKUP_ROOT/beschaedigt"
PATTERN='petrilog-backup-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]_[0-9][0-9][0-9][0-9][0-9][0-9].zip'

log() { echo "$(date '+%Y-%m-%d %H:%M:%S') $*" >> "$LOGFILE"; }

cd "$BACKUP_ROOT" || { echo "Backup-Verzeichnis nicht gefunden!" >> "$LOGFILE"; exit 1; }
mkdir -p "$DAILY" "$WEEKLY" "$MONTHLY" "$FAILED"
echo "========================================" >> "$LOGFILE"
log "Rotation gestartet"

# Nur ein Lauf gleichzeitig
exec 9>"$BACKUP_ROOT/.rotate.lock"
flock -n 9 || { log "Läuft bereits – übersprungen"; exit 0; }

STATUS=0

# Legt ein Backup in einen Platz und ersetzt den bisherigen Inhalt
place() {   # $1 = Datei, $2 = Platz-Ordner, $3 = Bezeichnung
    local file=$1 slot=$2 label=$3
    rm -f -- "$slot"/*.zip "$slot"/*.zip.sha256
    ln -f -- "$file" "$slot/$file"
    cp -f -- "$file.sha256" "$slot/$file.sha256"
    log "$label: $file"
}

# Erstes Backup eines Monats in den Monatsplatz – ältere Monate über KEEP_MONTHS hinaus entfernen
place_monthly() {   # $1 = Datei, $2 = Monat (JJJJ-MM)
    local file=$1 month=$2
    ls "$MONTHLY"/petrilog-backup-"$month"-*.zip >/dev/null 2>&1 && return 0   # Monat schon vorhanden
    ln -f -- "$file" "$MONTHLY/$file"
    cp -f -- "$file.sha256" "$MONTHLY/$file.sha256"
    log "Monatlich: $file"
    for old in $(ls -1 "$MONTHLY"/$PATTERN 2>/dev/null | sort -r | tail -n +$((KEEP_MONTHS + 1))); do
        rm -f -- "$old" "$old.sha256"
        log "Monatlich entfernt (älter als $KEEP_MONTHS Monate): $(basename "$old")"
    done
}

# ==============================
# 1. Neue Uploads prüfen und einsortieren (älteste zuerst)
# ==============================
for f in $(ls -1 $PATTERN 2>/dev/null | sort); do
    if [ ! -f "$f.sha256" ] || ! sha256sum --status -c "$f.sha256"; then
        mv -f -- "$f" "$FAILED/"; [ -f "$f.sha256" ] && mv -f -- "$f.sha256" "$FAILED/"
        log "FEHLER: $f ist beschädigt oder .sha256 fehlt – nach beschaedigt/ verschoben, kein Platz ersetzt"
        STATUS=1
        continue
    fi

    day=$(echo "$f" | grep -oE '[0-9]{4}-[0-9]{2}-[0-9]{2}')

    place "$f" "$DAILY" "Täglich"
    [ "$(date -d "$day" +%u)" = "7" ] && place "$f" "$WEEKLY" "Wöchentlich (Sonntag)"
    place_monthly "$f" "${day:0:7}"

    # Aus dem Upload-Ordner entfernen – die Plätze halten Hardlinks
    rm -f -- "$f" "$f.sha256"
done

# Abgebrochene Uploads (*.part) nach einem Tag entfernen – sie gelten nie als Backup
find "$BACKUP_ROOT" -maxdepth 1 -name '*.zip.part' -mtime +1 -print0 | while IFS= read -r -d '' p; do
    rm -f -- "$p"; log "Abgebrochener Upload gelöscht: $(basename "$p")"
done

# Beschädigte Dateien nach 14 Tagen entfernen
find "$FAILED" -type f -mtime +14 -delete

# ==============================
# 2. Alte Tagesordner (bisheriges Format) auslaufen lassen
# ==============================
OLD_FOLDERS=( $(ls -1d [0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]/ 2>/dev/null | sort -r || true) )   # || true: ohne alte Ordner liefert ls Code 2
if [ "${#OLD_FOLDERS[@]}" -gt "$KEEP_OLD_FOLDERS" ]; then
    for d in "${OLD_FOLDERS[@]:$KEEP_OLD_FOLDERS}"; do
        rm -rf -- "$d"
        log "Alter Backup-Ordner gelöscht: $d"
    done
fi

# ==============================
# 3. Kontrolle: Ist das tägliche Backup frisch?
# ==============================
NEWEST=$(ls -1 "$DAILY"/$PATTERN 2>/dev/null | head -n1 || true)
if [ -z "$NEWEST" ]; then
    log "WARNUNG: Noch kein Backup im Platz „taeglich“"
    STATUS=1
else
    AGE_H=$(( ( $(date +%s) - $(stat -c %Y "$NEWEST") ) / 3600 ))
    if [ "$AGE_H" -gt 30 ]; then
        log "WARNUNG: Tägliches Backup $(basename "$NEWEST") ist ${AGE_H} Stunden alt – kam heute nichts an?"
        STATUS=1
    fi
fi

log "Stand – täglich: $(ls -1 "$DAILY" | grep -c '\.zip$' || true), sonntags: $(ls -1 "$WEEKLY" | grep -c '\.zip$' || true), monatlich: $(ls -1 "$MONTHLY" | grep -c '\.zip$' || true)"
log "Rotation abgeschlossen (Status $STATUS)"
echo "" >> "$LOGFILE"
exit $STATUS
