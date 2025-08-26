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

# Seeding

Um den Admin zu seeden folgenden Befehl ausführen
	
	php artisan db:seed --class=AdminUserSeeder	