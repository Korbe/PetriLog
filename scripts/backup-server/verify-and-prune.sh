#!/usr/bin/env bash
#
# Läuft auf dem BACKUP-SERVER (nicht auf dem Produktivserver).
#
#  1. prüft jede neue Backup-Datei unabhängig vom Produktivserver per `sha256sum -c`
#  2. setzt die Aufbewahrung (täglich/wöchentlich/monatlich) selbst durch
#
# So braucht der Produktivserver kein Löschrecht auf dem Backup-Server: Selbst ein kompromittierter
# Produktivserver kann vorhandene Backups nicht löschen (BACKUP_REMOTE_PRUNE=false lassen).
#
# Cron (als Besitzer des Backup-Ordners, z. B. täglich 05:30):
#   30 5 * * * /opt/petrilog/verify-and-prune.sh /srv/backups/petrilog >> /var/log/petrilog-backup-check.log 2>&1
#
# Exit-Codes: 0 OK · 1 mindestens ein Backup beschädigt · 2 Aufruf-/Konfigurationsfehler · 3 kein aktuelles Backup

set -euo pipefail

DIR="${1:?Aufruf: verify-and-prune.sh <backup-ordner>}"
KEEP_DAILY="${KEEP_DAILY:-7}"
KEEP_WEEKLY="${KEEP_WEEKLY:-5}"
KEEP_MONTHLY="${KEEP_MONTHLY:-12}"
MAX_AGE_HOURS="${MAX_AGE_HOURS:-30}"   # Alarm, wenn das neueste Backup älter ist
PATTERN='petrilog-backup-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]_[0-9][0-9][0-9][0-9][0-9][0-9].zip'

log() { echo "$(date -u +%Y-%m-%dT%H:%M:%SZ) $*"; }

[ -d "$DIR" ] || { log "FEHLER Ordner $DIR existiert nicht"; exit 2; }
cd "$DIR"

# Nur ein Lauf gleichzeitig
exec 9>"$DIR/.verify.lock"
flock -n 9 || { log "Läuft bereits – übersprungen"; exit 0; }

status=0

# 1. Integrität: jede Datei mit ihrer .sha256 prüfen (bereits geprüfte merken)
touch .verified
for file in $(ls -1 $PATTERN 2>/dev/null | sort); do
    grep -qxF "$file" .verified && continue
    if [ ! -f "$file.sha256" ]; then
        log "FEHLER $file: .sha256 fehlt"; status=1; continue
    fi
    if sha256sum --status -c "$file.sha256"; then
        echo "$file" >> .verified
        log "OK $file"
    else
        log "FEHLER $file: Prüfsumme stimmt nicht – Datei beschädigt"; status=1
    fi
done

# Liegengebliebene Teil-Uploads (älter als 1 Tag) entfernen – sie gelten nie als Backup
find "$DIR" -maxdepth 1 -name '*.zip.part' -mtime +1 -print -delete | sed 's/^/ENTFERNT Teil-Upload /' || true

# 2. Aktualität
newest=$(ls -1 $PATTERN 2>/dev/null | sort | tail -n1 || true)
if [ -z "$newest" ]; then
    log "FEHLER keine Backups vorhanden"; exit 3
fi
age_hours=$(( ( $(date +%s) - $(stat -c %Y "$newest") ) / 3600 ))
if [ "$age_hours" -gt "$MAX_AGE_HOURS" ]; then
    log "WARNUNG neuestes Backup $newest ist ${age_hours} h alt"; status=3
fi

# 3. Aufbewahrung (Großvater-Vater-Sohn): je Tag/ISO-Woche/Monat das neueste behalten
declare -A keep=()
keep["$newest"]=1
select_buckets() {
    local limit=$1 format=$2 count=0
    declare -A seen=()
    for file in $(ls -1 $PATTERN | sort -r); do
        stamp="${file#petrilog-backup-}"; stamp="${stamp%.zip}"          # 2026-10-17_031500
        day="${stamp%_*}"
        bucket=$(date -u -d "$day" +"$format")
        [ -n "${seen[$bucket]:-}" ] && continue
        [ "$count" -ge "$limit" ] && break
        seen[$bucket]=1; keep["$file"]=1; count=$((count + 1))
    done
}
select_buckets "$KEEP_DAILY"   '%Y-%m-%d'
select_buckets "$KEEP_WEEKLY"  '%G-W%V'
select_buckets "$KEEP_MONTHLY" '%Y-%m'

for file in $(ls -1 $PATTERN | sort); do
    if [ -z "${keep[$file]:-}" ]; then
        rm -f -- "$file" "$file.sha256"
        sed -i "/^${file//./\\.}\$/d" .verified
        log "GELÖSCHT $file (Aufbewahrung)"
    fi
done

log "Fertig: ${#keep[@]} Backups behalten"
exit $status
