# Server-Umzug: PetriLog auf Ubuntu 26.04 LTS

Ziel: Server A (petrilog.com, derzeit Ubuntu 25.04 ohne Sicherheits-Updates) durch ein neues Droplet mit
**Ubuntu 26.04 LTS** und **PHP 8.4 aus dem PPA** ersetzen. Server B (korbitsch.at, Backups) bleibt unverändert.

**Prinzip:** Das neue System wird komplett vorbereitet und getestet, während der alte Server normal weiterläuft.
Die eigentliche Umstellung dauert nur kurz (Wartungsmodus → letztes Backup → Einspielen → DNS). Bis der neue Server
sich bewährt hat, bleibt der alte als Rückfallebene bestehen.

| Phase | Dauer | Ausfall |
|---|---|---|
| 1–9 Neuen Server einrichten und testen | ½–1 Tag | keiner |
| 10 Umstellung | ca. 30–60 Min. | ja (Wartungsseite) |
| 11 Nacharbeiten, alten Server abbauen | 1–2 Wochen später | keiner |

Alle Befehle als **root**, sofern nicht anders angegeben. `ALT` = alter Server, `NEU` = neues Droplet.

---

## 0. Am Tag davor

- [ ] **DNS-TTL senken:** Beim DNS-Anbieter von petrilog.com den A-Record (und `www`) auf **TTL 300** (5 Minuten)
      stellen. Erst nach Ablauf der alten TTL (oft 1 Tag) wirkt die Umstellung später schnell.
- [ ] **DigitalOcean-Snapshot** vom ALT-Droplet anlegen (Notfall-Rückfall auf den kompletten Stand).
- [ ] Prüfen, dass das Backup läuft: `runuser -u deployer -- php /var/www/petrilog.com/current/artisan backup:status`

---

## 1. Konfiguration vom alten Server sichern (ALT)

Alles, was nicht im Git-Repo steht, in ein Archiv packen. **Das Archiv enthält Passwörter und Schlüssel** – nur
direkt von Server zu Server kopieren und nach dem Umzug löschen.

```bash
php -i | grep -E "upload_max_filesize|post_max_size|memory_limit|max_execution_time" > /root/php-limits.txt
```

```bash
mysql -e "SELECT user, host, plugin FROM mysql.user" > /root/mysql-users.txt
```

```bash
tar czf /root/umzug-config.tgz \
  /var/www/petrilog.com/shared/.env \
  /etc/nginx/sites-available \
  /etc/supervisor/conf.d \
  /etc/msmtprc \
  /etc/cron.d/petrilog-scheduler /etc/cron.d/petrilog-backup \
  /etc/letsencrypt \
  /home/deployer/.ssh \
  /root/php-limits.txt /root/mysql-users.txt
```

Nicht vorhandene Pfade meldet `tar` als Warnung – das ist in Ordnung (z. B. wenn `petrilog-backup` noch fehlt).

Außerdem notieren (für Schritt 4):

```bash
grep -E '^(DB_DATABASE|DB_USERNAME|BACKUP_DB_USERNAME|BACKUP_RESTORE_TEST_DATABASE)=' /var/www/petrilog.com/shared/.env
```

---

## 2. Neues Droplet anlegen (DigitalOcean)

- [ ] **Image:** Ubuntu **26.04 LTS**, gleiche Region wie ALT, mindestens gleiche Größe.
- [ ] **SSH-Key** deines PCs hinterlegen.
- [ ] **Backups/Monitoring** von DigitalOcean nach Wunsch aktivieren.
- [ ] Neue IP notieren → im Folgenden `NEU_IP`.

Archiv vom ALT auf NEU kopieren (auf ALT ausführen):

```bash
scp /root/umzug-config.tgz root@NEU_IP:/root/
```

---

## 3. Grundsystem (NEU)

```bash
apt update && apt full-upgrade -y
```

```bash
timedatectl set-timezone Europe/Vienna
```

Automatische Sicherheits-Updates:

```bash
apt install -y unattended-upgrades && dpkg-reconfigure -plow unattended-upgrades
```

Firewall (SSH, HTTP, HTTPS):

```bash
ufw allow OpenSSH && ufw allow 'Nginx Full' && ufw --force enable
```

`'Nginx Full'` gibt es erst nach Schritt 3b – sonst `ufw allow 80,443/tcp`.

### 3b. Pakete

PHP 8.4 aus dem PPA von Ondřej Surý (aktuelle Patch-Versionen, z. B. 8.4.27):

```bash
apt install -y software-properties-common && add-apt-repository -y ppa:ondrej/php && apt update
```

```bash
apt install -y php8.4-fpm php8.4-cli php8.4-mysql php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-gd php8.4-intl php8.4-bcmath php8.4-readline php8.4-opcache
```

Webserver, Datenbank, Werkzeuge:

```bash
apt install -y nginx mysql-server supervisor acl git unzip msmtp msmtp-mta certbot python3-certbot-nginx
```

Bildoptimierung (Spatie Media Library):

```bash
apt install -y jpegoptim optipng pngquant gifsicle webp
```

Composer:

```bash
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
```

Node.js (für das Server-Side-Rendering, `inertia-ssr`):

```bash
curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && apt install -y nodejs
```

Kontrolle:

```bash
php -v && mysql --version && node -v && composer -V
```

### 3c. PHP-Limits wie auf ALT

Werte aus `/root/php-limits.txt` (im Archiv) übernehmen, z. B.:

```bash
cat > /etc/php/8.4/fpm/conf.d/99-petrilog.ini <<'EOF'
upload_max_filesize = 64M
post_max_size = 64M
memory_limit = 512M
max_execution_time = 120
EOF
```

```bash
systemctl restart php8.4-fpm
```

---

## 4. MySQL (NEU)

Datenbank und Benutzer wie auf ALT anlegen – **Namen und Passwörter aus der alten `.env`** (Schritt 1).
MySQL 8.4 kennt `mysql_native_password` nicht mehr, Standard ist `caching_sha2_password`.

```bash
mysql
```

```sql
CREATE DATABASE petrilog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- App-Benutzer (DB_USERNAME / DB_PASSWORD)
CREATE USER 'petrilog'@'localhost' IDENTIFIED BY 'PASSWORT_AUS_DER_ENV';
GRANT ALL PRIVILEGES ON petrilog_db.* TO 'petrilog'@'localhost';

-- Backup-Benutzer (BACKUP_DB_USERNAME / BACKUP_DB_PASSWORD), nur lesend
CREATE USER 'petrilog_backup'@'localhost' IDENTIFIED BY 'PASSWORT_AUS_DER_ENV';
GRANT SELECT, SHOW VIEW, TRIGGER, LOCK TABLES, EVENT ON petrilog_db.* TO 'petrilog_backup'@'localhost';

-- Test-Datenbank für den sonntäglichen Wiederherstellungstest (BACKUP_RESTORE_TEST_DATABASE)
CREATE DATABASE petrilog_db_restore_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON petrilog_db_restore_test.* TO 'petrilog'@'localhost';

FLUSH PRIVILEGES;
```

Nutzt die alte `.env` den Benutzer `root` für die App, besser hier einen eigenen App-Benutzer anlegen und
`DB_USERNAME`/`DB_PASSWORD` in der neuen `.env` entsprechend ändern. Die genauen Rechte für Backup und
Wiederherstellungstest stehen in `docs/backup.md` (Abschnitt 2.2).

---

## 5. Benutzer `deployer` (NEU)

```bash
adduser --disabled-password --gecos "" deployer
```

SSH-Schlüssel aus dem Archiv übernehmen (Deploy-Zugang vom PC und Backup-Schlüssel für Server B):

```bash
mkdir -p /root/umzug && tar xzf /root/umzug-config.tgz -C /root/umzug
```

```bash
cp -a /root/umzug/home/deployer/.ssh /home/deployer/ && chown -R deployer:deployer /home/deployer/.ssh && chmod 700 /home/deployer/.ssh && chmod 600 /home/deployer/.ssh/*
```

Damit sind `authorized_keys` (Deploy vom PC), der Backup-Schlüssel `petrilog_backup` und `known_hosts` übernommen.

Erlaubte `sudo`-Befehle für den Deploy (siehe `deploy.php`):

```bash
cat > /etc/sudoers.d/deployer <<'EOF'
deployer ALL=(root) NOPASSWD: /usr/bin/systemctl reload php8.4-fpm, /usr/bin/systemctl reload nginx, /usr/bin/supervisorctl restart inertia-ssr
EOF
```

```bash
chmod 440 /etc/sudoers.d/deployer && visudo -c
```

Projektverzeichnis:

```bash
mkdir -p /var/www/petrilog.com/shared && chown -R deployer:deployer /var/www/petrilog.com
```

`.env` übernehmen:

```bash
cp /root/umzug/var/www/petrilog.com/shared/.env /var/www/petrilog.com/shared/.env && chown deployer:deployer /var/www/petrilog.com/shared/.env && chmod 640 /var/www/petrilog.com/shared/.env
```

Anschließend in der `.env` prüfen/anpassen: DB-Zugangsdaten (Schritt 4), `MYSQL_PATH`/`BACKUP_*_PATH` falls gesetzt
(auf Ubuntu `/usr/bin`), `BACKUP_LOCAL_PATH=/var/www/petrilog.com/shared/storage/backups`.

---

## 6. nginx und HTTPS (NEU)

Site-Konfiguration vom ALT übernehmen:

```bash
cp /root/umzug/etc/nginx/sites-available/* /etc/nginx/sites-available/
```

```bash
ln -sf /etc/nginx/sites-available/petrilog.com /etc/nginx/sites-enabled/petrilog.com && rm -f /etc/nginx/sites-enabled/default
```

Im Server-Block prüfen: `root /var/www/petrilog.com/current/public;`, PHP-Socket `unix:/run/php/php8.4-fpm.sock`,
`client_max_body_size` passend zu `upload_max_filesize`.

**Zertifikate übernehmen** statt neu auszustellen – so funktioniert HTTPS sofort nach der DNS-Umstellung:

```bash
cp -a /root/umzug/etc/letsencrypt /etc/
```

```bash
nginx -t && systemctl reload nginx
```

Die automatische Verlängerung (`certbot.timer`) läuft ab der DNS-Umstellung auf NEU.

---

## 7. Erster Deploy (PC)

In `deploy.php` die neue IP eintragen:

```php
host('NEU_IP')
```

Dann vom PC aus:

```bash
dep deploy
```

Deployer legt `releases/`, `shared/storage` und den Symlink `current` an, führt `composer install`, die Migrationen
(auf der noch leeren Datenbank) und `storage:link` aus und lädt das gebaute Frontend hoch.

Prüfen, ob der Webserver-Benutzer schreiben darf (Deployer setzt das per ACL):

```bash
getfacl /var/www/petrilog.com/shared/storage | grep www-data
```

---

## 8. Dienste: Queue, SSR, Cron, Mail (NEU)

**Supervisor** (Queue-Worker als `www-data`, SSR) aus dem Archiv übernehmen und prüfen:

```bash
cp /root/umzug/etc/supervisor/conf.d/*.conf /etc/supervisor/conf.d/
```

In `petrilog-queue.conf` muss `user=www-data` stehen (siehe Chat/Doku zum Queue-Worker), Log-Datei für `www-data`
beschreibbar machen:

```bash
touch /var/www/petrilog.com/shared/storage/logs/queue.log && chown www-data:www-data /var/www/petrilog.com/shared/storage/logs/queue.log
```

```bash
supervisorctl reread && supervisorctl update && supervisorctl status
```

Erwartet: `inertia-ssr` und `petrilog-queue:petrilog-queue_00` mit `RUNNING`.

**Mail (msmtp)** für die Backup-Benachrichtigungen:

```bash
cp /root/umzug/etc/msmtprc /etc/msmtprc && chown root:deployer /etc/msmtprc && chmod 640 /etc/msmtprc
```

**Cron** (Scheduler als `www-data`, Backup):

```bash
cp /root/umzug/etc/cron.d/petrilog-scheduler /etc/cron.d/ && chmod 644 /etc/cron.d/petrilog-scheduler
```

```bash
cp /var/www/petrilog.com/current/scripts/backup/petrilog-backup.cron /etc/cron.d/petrilog-backup && chmod 644 /etc/cron.d/petrilog-backup
```

Bis zur Umstellung (Schritt 10) das Backup auf NEU noch **nicht** laufen lassen – sonst landen leere Backups von
NEU auf Server B. Vorübergehend deaktivieren:

```bash
sed -i 's/^15 3/#15 3/' /etc/cron.d/petrilog-backup
```

---

## 9. Testen ohne DNS-Umstellung

Auf deinem PC die Domain vorübergehend auf NEU zeigen lassen. Datei `C:\Windows\System32\drivers\etc\hosts`
als Administrator öffnen und ergänzen:

```
NEU_IP  petrilog.com www.petrilog.com
```

Jetzt öffnet *nur dein PC* petrilog.com auf dem neuen Server (mit dem übernommenen Zertifikat). Prüfen:

- [ ] Startseite, Login, Registrierung (leere Datenbank – Testkonto anlegen)
- [ ] Fang mit Foto eintragen → Vorschaubild entsteht (Queue läuft)
- [ ] `runuser -u deployer -- php /var/www/petrilog.com/current/artisan backup:doctor` → alles ✔
- [ ] `runuser -u www-data -- php /var/www/petrilog.com/current/artisan schedule:list` → `demo:reset` geplant

Danach die Zeile in der `hosts`-Datei wieder entfernen.

---

## 10. Umstellung

Ab hier ist die Website kurz nicht erreichbar. Ruhige Uhrzeit wählen (z. B. abends).

**ALT – Wartungsmodus und letztes Backup:**

```bash
runuser -u deployer -- php /var/www/petrilog.com/current/artisan down --retry=60
```

```bash
bash /var/www/petrilog.com/current/scripts/backup/petrilog-backup.sh
```

Das Backup liegt danach lokal auf ALT (und auf Server B). Dateiname anzeigen:

```bash
runuser -u deployer -- php /var/www/petrilog.com/current/artisan backup:list
```

Backup samt Prüfsumme nach NEU kopieren (auf ALT):

```bash
scp /var/www/petrilog.com/shared/storage/backups/petrilog-backup-DATUM.zip* root@NEU_IP:/var/www/petrilog.com/shared/storage/backups/
```

**NEU – einspielen:**

```bash
chown -R deployer:deployer /var/www/petrilog.com/shared/storage/backups
```

```bash
runuser -u deployer -- php /var/www/petrilog.com/current/artisan backup:verify petrilog-backup-DATUM.zip
```

```bash
runuser -u deployer -- php /var/www/petrilog.com/current/artisan backup:restore-production petrilog-backup-DATUM.zip --confirm=petrilog_db --skip-safety-backup
```

`--skip-safety-backup`, weil NEU bisher nur Testdaten enthält. Die Wiederherstellung spielt Datenbank **und** Fotos
ein und prüft beides.

```bash
runuser -u deployer -- php /var/www/petrilog.com/current/artisan migrate --force && runuser -u deployer -- php /var/www/petrilog.com/current/artisan config:cache
```

```bash
supervisorctl restart all
```

**DNS umstellen:** A-Record von `petrilog.com` und `www` auf `NEU_IP`.

Wirkt innerhalb von ca. 5 Minuten (TTL aus Schritt 0). Prüfen:

```bash
nslookup petrilog.com
```

**Backup auf NEU aktivieren:**

```bash
sed -i 's/^#15 3/15 3/' /etc/cron.d/petrilog-backup
```

Und auf **ALT** das Backup deaktivieren, damit nicht zwei Server auf Server B sichern:

```bash
mv /etc/cron.d/petrilog-backup /root/petrilog-backup.cron.deaktiviert
```

```bash
mv /etc/cron.d/petrilog-scheduler /root/petrilog-scheduler.cron.deaktiviert
```

Hinweis: Sitzungen werden bewusst nicht gesichert – nach dem Umzug müssen sich alle neu anmelden.

---

## 11. Nacharbeiten

Sofort:

- [ ] petrilog.com im Browser: Login, Fang mit Foto, Fang-Detail mit Karte, PDF-Fangmeldung
- [ ] Stripe: Dashboard → Webhooks → letzte Zustellungen erfolgreich? (Die URL bleibt gleich, nur der Server ist neu.)
- [ ] Test-Mail: Passwort-Zurücksetzen an dich selbst
- [ ] `certbot renew --dry-run` → Verlängerung funktioniert auf NEU

Am nächsten Morgen:

- [ ] Backup-Mail angekommen? `runuser -u deployer -- php /var/www/petrilog.com/current/artisan backup:status`
- [ ] Demo-Konto zurückgesetzt (3 Fänge)?
- [ ] Admin-Dashboard ohne Warnungen (Queue, Backup)

Nach 1–2 Wochen ohne Probleme:

- [ ] DNS-TTL wieder hochsetzen (z. B. 3600)
- [ ] ALT ausschalten, noch ein paar Tage stehen lassen, dann Droplet und Snapshot löschen
- [ ] Archiv mit Zugangsdaten löschen – auf NEU und (falls noch da) auf ALT:

```bash
rm -rf /root/umzug /root/umzug-config.tgz
```

---

## Rückfall, falls auf NEU etwas schiefgeht

1. DNS-A-Record zurück auf die alte IP.
2. Auf ALT: `runuser -u deployer -- php /var/www/petrilog.com/current/artisan up`
3. Cron-Dateien auf ALT zurückverschieben (`/root/*.cron.deaktiviert` → `/etc/cron.d/`).

ALT ist bis zum Wartungsmodus unverändert – es geht also höchstens das verloren, was in der Zwischenzeit auf NEU
eingetragen wurde.
