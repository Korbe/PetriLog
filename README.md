# PetriLog 🎣

**PetriLog** ist eine moderne Webanwendung für Angler.  
Mit PetriLog kannst du deine Fänge dokumentieren, inklusive Gewicht, Länge, Fangdatum, Ort und Fotos.  
Die App ist mit **Laravel**, **Vue 3**, **Inertia.js** und **TailwindCSS** entwickelt und unterstützt auch **Server Side Rendering (SSR)**, damit Links auf sozialen Netzwerken wie Facebook oder Twitter korrekt Vorschauen anzeigen.

---

## 🚀 Features

- 📓 Fangbuch: Erfassung von Fischen mit Fotos, Gewicht, Länge, Datum, Ort  
- 🗺️ Kartenintegration (Google Maps Picker für Fangposition)  
- 🖼️ Medien-Upload (Fotos der Fänge)  
- 🔍 Öffentliche Fangseiten (mit Open Graph / Twitter Meta Tags)  
- 📱 Responsive Design mit TailwindCSS  
- ⚡ Inertia.js SPA + Laravel Backend  
- 🖥️ SSR-Support für bessere SEO und Social Media Previews  

---

## Voraussetzungen
- **PHP** 8.3+
- **Composer**
- **Node.js** 18+ (empfohlen 20+)
- **npm**
- **Datenbank** (MySQL/MariaDB/PostgreSQL)
- (Prod) **Supervisor** oder ähnlicher Prozess-Monitor
- (Optional) **Redis** für Cache/Queue

---

## Lokale Entwicklung

#### 1) Klonen

    git clone https://github.com/Korbe/PetriLog.git
    cd PetriLog

#### 2) Backend-Abhängigkeiten

    composer install

#### 3) Frontend-Abhängigkeiten

    npm install

#### 4) .env anlegen + App-Key

    cp .env.example .env
    php artisan key:generate

#### 5) DB konfigurieren und migrieren

    php artisan migrate
    php artisan db:seed

#### 6) Storage-Link für Bilder
    php artisan storage:link

#### 7) Dev-Server starten
    Laragon dev server starten (Virtualhost)
    npm run dev


# 📦 Server Setup & Deployment Notes

## 🖼️ Image Processing Dependencies

Für die Bildoptimierung sollten auf dem Server folgende Pakete installiert sein:

    apt install jpegoptim optipng pngquant gifsicle svgo webp

---

## ⚡ SSR Server einrichten

### 1. Supervisor installieren

	sudo apt-get install supervisor

### 2. Supervisor Config anlegen

Pfad:  
`/etc/supervisor/conf.d/inertia-ssr.conf`

Inhalt:

	[program:inertia-ssr]
	directory=/var/www/petrilog.korbitsch.at/current
	command=php artisan inertia:start-ssr
	autostart=true
	autorestart=true
	user=deployer
	redirect_stderr=true
	stdout_logfile=/var/www/petrilog.korbitsch.at/shared/logs/inertia-ssr.log

### 3. Log-Verzeichnis erstellen

Im **shared**-Ordner deines Deployment-Pfades:

	mkdir logs

### 4. Supervisor neu laden und starten

	sudo supervisorctl reread
	sudo supervisorctl update
	sudo supervisorctl start inertia-ssr

---

## 🚀 Deployer Integration

### Rechte für `sudo`-Befehle ohne Passwort

Damit Deployer bestimmte Befehle ohne Passwort ausführen darf, legst du eine eigene Datei unter `/etc/sudoers.d/` an:

	sudo visudo -f /etc/sudoers.d/deployer

Eintragen (für User `deployer`):

	deployer ALL=(ALL) NOPASSWD: /bin/systemctl restart php8.3-fpm
	deployer ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl

Damit kann dein Deployment-Skript folgende Kommandos ohne Passwort nutzen:

- `sudo systemctl restart php8.3-fpm`  
- `sudo supervisorctl restart inertia-ssr`

# Seeding

Um den Admin zu seeden folgenden Befehl ausführen
	
	php artisan db:seed --class=AdminUserSeeder	