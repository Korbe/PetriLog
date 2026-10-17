#!/usr/bin/env bash
#
# PetriLog – nächtliches Backup von Server A (petrilog.com) auf Server B (korbitsch.at)
#
# Die eigentliche Arbeit macht `php artisan backup:run`:
#   1. konsistenter DB-Schnappschuss (mysqldump --single-transaction) + storage/app/public + storage/app/private
#   2. ein AES-256-verschlüsseltes ZIP, jede Datei per SHA-256 geprüft
#   3. Upload per SFTP (SSH-Schlüssel) auf Server B, SHA-256 dort nachgerechnet, erst dann freigegeben
#   4. lokales Aufräumen – nur Backups, die nachweislich auf Server B liegen
#
# Dieses Skript ergänzt nur Betriebsdinge: Sperre, Logging, Mail bei Fehler/Erfolg und eine zweite,
# unabhängige Prüfung direkt auf Server B (sha256sum -c über SSH).
#
# Keine Passwörter hier! DB-Zugang und Verschlüsselungspasswort stehen in /var/www/petrilog.com/shared/.env
# (siehe docs/backup.md).
#
# Aufruf (als root aus cron möglich – das Skript wechselt selbst zum App-Benutzer):
#   bash /var/www/petrilog.com/current/scripts/backup/petrilog-backup.sh

set -Eeuo pipefail

# ==============================
# Konfiguration
# ==============================
APP_USER="${APP_USER:-deployer}"                       # Besitzer der App (liest .env und storage/)
APP_DIR="${APP_DIR:-/var/www/petrilog.com/current}"
PHP_BIN="${PHP_BIN:-php}"
LOG_FILE="${LOG_FILE:-/var/log/petrilog-backup.log}"

# Mail (msmtp)
EMAIL="${EMAIL:-info@petrilog.com}"
MAIL_ON_SUCCESS="${MAIL_ON_SUCCESS:-1}"               # 0 = nur bei Fehlern mailen

# Zweite, unabhängige Prüfung auf Server B per SSH (benötigt Shell-Zugang für den Backup-User).
# Bei einem reinen SFTP-Chroot-User (internal-sftp) auf 0 setzen – dann prüft verify-and-prune.sh auf Server B.
REMOTE_SHELL_CHECK="${REMOTE_SHELL_CHECK:-1}"
REMOTE_USER="${REMOTE_USER:-backupuser}"
REMOTE_HOST="${REMOTE_HOST:-korbitsch.at}"
REMOTE_DIR="${REMOTE_DIR:-/backups/petrilog}"          # muss BACKUP_REMOTE_PATH in der .env entsprechen
SSH_KEY="${SSH_KEY:-/home/${APP_USER}/.ssh/petrilog_backup}"

HOSTNAME_SHORT="$(hostname)"

# ==============================
# Als App-Benutzer laufen (nicht als root)
# ==============================
if [ "$(id -u)" -eq 0 ]; then
    # Logdatei einmalig für den App-Benutzer beschreibbar machen
    touch "$LOG_FILE" && chown "$APP_USER": "$LOG_FILE" && chmod 640 "$LOG_FILE"
    exec runuser -u "$APP_USER" -- env \
        APP_USER="$APP_USER" APP_DIR="$APP_DIR" PHP_BIN="$PHP_BIN" LOG_FILE="$LOG_FILE" EMAIL="$EMAIL" \
        MAIL_ON_SUCCESS="$MAIL_ON_SUCCESS" REMOTE_SHELL_CHECK="$REMOTE_SHELL_CHECK" REMOTE_USER="$REMOTE_USER" \
        REMOTE_HOST="$REMOTE_HOST" REMOTE_DIR="$REMOTE_DIR" SSH_KEY="$SSH_KEY" \
        bash "$0" "$@"
fi

# ==============================
# Logging (alles wird geloggt)
# ==============================
if ! touch "$LOG_FILE" 2>/dev/null; then
    LOG_FILE="/var/www/petrilog.com/shared/storage/logs/backup-cron.log"
fi
exec > >(tee -a "$LOG_FILE") 2>&1

log() { echo "$(date '+%Y-%m-%d %H:%M:%S') $*"; }

send_mail() {
    local subject="$1" body="$2"
    if command -v msmtp >/dev/null 2>&1; then
        printf 'Subject: %s\nContent-Type: text/plain; charset=UTF-8\n\n%s\n' "$subject" "$body" | msmtp "$EMAIL" \
            || log "WARNUNG: Mail konnte nicht gesendet werden"
    else
        log "WARNUNG: msmtp nicht installiert – keine Mail"
    fi
}

# Bedeutung der Exit-Codes von backup:run (docs/backup.md)
explain_code() {
    case "$1" in
        2) echo "Konfiguration (z. B. BACKUP_ENCRYPTION_PASSWORD fehlt, SSH-Schlüssel nicht lesbar)" ;;
        3) echo "Es lief bereits ein Backup" ;;
        4) echo "Datenbank (mysqldump fehlgeschlagen oder unvollständig)" ;;
        5) echo "Archiv (Schreiben fehlgeschlagen oder zu wenig Speicherplatz)" ;;
        6) echo "Prüfung fehlgeschlagen (Backup beschädigt)" ;;
        7) echo "Übertragung auf $REMOTE_HOST fehlgeschlagen – Backup liegt nur lokal" ;;
        8) echo "Wiederherstellungstest fehlgeschlagen – das Backup wurde zwar übertragen, lässt sich aber nicht sauber einspielen. Bitte prüfen!" ;;
        *) echo "Allgemeiner Fehler" ;;
    esac
}

fail() {
    local code="$1" reason="$2"
    log "==== Backup FEHLGESCHLAGEN (Code $code): $reason ===="
    send_mail "Backup FEHLGESCHLAGEN auf $HOSTNAME_SHORT (Code $code)" "Backup auf $HOSTNAME_SHORT am $(date) ist fehlgeschlagen.

Grund: $reason

Letzte Logzeilen:
$(tail -n 30 "$LOG_FILE")

Diagnose:  cd $APP_DIR && php artisan backup:doctor
Nachholen: cd $APP_DIR && php artisan backup:run"
    exit "$code"
}

trap 'fail 1 "Unerwarteter Fehler in Zeile $LINENO: $BASH_COMMAND"' ERR

# ==============================
# Nur ein Lauf gleichzeitig
# ==============================
exec 9>"/tmp/petrilog-backup.lock"
if ! flock -n 9; then
    log "Ein Backup läuft bereits – übersprungen"
    exit 0
fi

log "==== Backup gestartet auf $HOSTNAME_SHORT ===="

cd "$APP_DIR"

# ==============================
# 1–4: Backup erstellen, prüfen, übertragen, aufräumen
# ==============================
# Sonntags (oder mit RESTORE_TEST=1) das neue Backup zusätzlich testweise wiederherstellen –
# in eine eigene Test-Datenbank, die echte Datenbank bleibt unberührt
RUN_ARGS=(--trigger=schedule --no-ansi)
if [ "${RESTORE_TEST:-}" = "1" ] || [ "$(date +%u)" = "7" ]; then
    RUN_ARGS+=(--restore-test)
    RESTORE_TESTED=1
    log "Heute mit Wiederherstellungstest"
fi

set +e
OUTPUT="$("$PHP_BIN" artisan backup:run "${RUN_ARGS[@]}" 2>&1)"
CODE=$?
set -e
echo "$OUTPUT"

if [ "$CODE" -ne 0 ]; then
    fail "$CODE" "$(explain_code "$CODE")"
fi

# Dateiname des neuen Backups aus der Ausgabe („✔ petrilog-backup-…zip (… MB, …)“)
BACKUP_FILE="$(echo "$OUTPUT" | grep -oE 'petrilog-backup-[0-9]{4}-[0-9]{2}-[0-9]{2}_[0-9]{6}\.zip' | head -n1 || true)"
if [ -z "$BACKUP_FILE" ]; then
    fail 1 "Backup gemeldet, aber kein Dateiname in der Ausgabe gefunden"
fi

# Ohne konfigurierten Zweitserver liegt das Backup nur auf diesem Server
if echo "$OUTPUT" | grep -q 'Zweitserver ist nicht konfiguriert'; then
    fail 7 "BACKUP_REMOTE_ENABLED ist nicht aktiv – Backup $BACKUP_FILE liegt nur auf $HOSTNAME_SHORT"
fi

# ==============================
# 5: Zweite, unabhängige Prüfung direkt auf Server B
# ==============================
if [ "$REMOTE_SHELL_CHECK" = "1" ]; then
    log "Prüfe $BACKUP_FILE unabhängig auf $REMOTE_HOST …"
    if ssh -i "$SSH_KEY" -o BatchMode=yes -o StrictHostKeyChecking=yes -o ConnectTimeout=20 \
        "$REMOTE_USER@$REMOTE_HOST" "cd '$REMOTE_DIR' && sha256sum --status -c '$BACKUP_FILE.sha256'"; then
        log "OK: SHA-256 auf $REMOTE_HOST bestätigt"
    else
        fail 6 "SHA-256-Prüfung von $BACKUP_FILE auf $REMOTE_HOST fehlgeschlagen"
    fi
fi

# ==============================
# Erfolgs-Mail
# ==============================
WARNINGS="$(echo "$OUTPUT" | grep '⚠' || true)"
log "==== Backup erfolgreich abgeschlossen: $BACKUP_FILE ===="

RESTORE_SUMMARY=""
if [ -n "${RESTORE_TESTED:-}" ]; then
    # Tabelle aus der Ausgabe des Tests (Tabellen, Datensätze, Fremdschlüssel, Dateien, Funktionsprobe)
    RESTORE_SUMMARY="$(echo "$OUTPUT" | grep -E '^\s+\| (Tabellen|Datensätze|Fremdschlüssel|AUTO_INCREMENT|Dateien|Fehlende|Migrationen|Funktionsprobe)' | sed -E 's/^\s+\| //; s/\s+\|\s+/: /; s/\s+\|$//' || true)"
fi

# Wiederherstellungstest wird immer gemeldet – auch mit MAIL_ON_SUCCESS=0
if [ "$MAIL_ON_SUCCESS" = "1" ] || [ -n "$WARNINGS" ] || [ -n "${RESTORE_TESTED:-}" ]; then
    SUBJECT="Backup erfolgreich auf $HOSTNAME_SHORT"
    [ -n "${RESTORE_TESTED:-}" ] && SUBJECT="Backup + Wiederherstellungstest erfolgreich auf $HOSTNAME_SHORT"
    send_mail "$SUBJECT${WARNINGS:+ (mit Hinweisen)}" "Das Backup auf $HOSTNAME_SHORT am $(date) wurde erfolgreich erstellt, verschlüsselt und auf $REMOTE_HOST übertragen.

Datei: $BACKUP_FILE
Ziel:  $REMOTE_USER@$REMOTE_HOST:$REMOTE_DIR
${RESTORE_SUMMARY:+
Wiederherstellungstest (in eine separate Test-Datenbank, die danach wieder gelöscht wurde):
$RESTORE_SUMMARY
}${WARNINGS:+
Hinweise:
$WARNINGS}"
fi
