# Server Setup & Deployment Guide

## 1. User & SSH

### 1.1 User Deployer anlegen

	sudo adduser deployer

### 1.2 SSH-Zugang einrichten

	sudo mkdir -p /home/deployer/.ssh
	sudo chown deployer:deployer /home/deployer/.ssh
	sudo chmod 700 /home/deployer/.ssh

	sudo nano /home/deployer/.ssh/authorized_keys
	# Public SSH Key einfügen

	sudo chown deployer:deployer /home/deployer/.ssh/authorized_keys
	sudo chmod 600 /home/deployer/.ssh/authorized_keys

## 2. System Update & LEMP Stack

### 2.1 Update & Upgrade

	sudo apt update && sudo apt upgrade -y

### 2.2 Nginx installieren

	sudo apt install nginx -y
	sudo systemctl start nginx
	sudo systemctl enable nginx
	systemctl status nginx

### 2.3 MySQL installieren

	sudo apt install mysql-server -y
	sudo mysql_secure_installation

### 2.4 MySQL konfigurieren

	sudo mysql
	ALTER USER 'root'@'localhost' IDENTIFIED BY 'DeinSicheresPasswort';
	FLUSH PRIVILEGES;

### 2.5 Neue Datenbank anlegen

	sudo mysql -u root -p
	CREATE DATABASE petrilog_db;
	SHOW DATABASES;

## 3. PHP & Extensions

	sudo apt install php-fpm php-mysql php-cli php-mbstring php-bcmath php-curl php-xml php-zip php-gd unzip -y
	systemctl status php8.4-fpm

## 4. Nginx für Laravel konfigurieren

### 4.1 Server-Block erstellen

	sudo nano /etc/nginx/sites-available/petrilog.com

Server-Block:

	server {
		listen 80;
		server_name petrilog.com;
		root /var/www/petrilog.com/current/public;

		add_header X-Frame-Options "SAMEORIGIN";
		add_header X-XSS-Protection "1; mode=block";
		add_header X-Content-Type-Options "nosniff";

		index index.php index.html;
		charset utf-8;

		location / {
			try_files $uri $uri/ /index.php?$query_string;
		}

		location = /favicon.ico { access_log off; log_not_found off; }
		location = /robots.txt  { access_log off; log_not_found off; }

		error_page 404 /index.php;

		location ~ \.php$ {
			include snippets/fastcgi-php.conf;
			fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
			fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
			include fastcgi_params;
		}

		location ~ /\.(?!well-known).* {
			deny all;
		}
	}

### 4.2 Server-Block aktivieren

	sudo ln -s /etc/nginx/sites-available/petrilog.com /etc/nginx/sites-enabled/
	sudo nginx -t
	sudo systemctl reload nginx

## 5. SSL via Let’s Encrypt

### 5.1 Certbot & DigitalOcean Plugin installieren

	sudo apt update
	sudo apt install python3-certbot-dns-digitalocean -y

### 5.2 DigitalOcean API Token konfigurieren

	sudo nano /home/root/do.ini
	# Inhalt:
	dns_digitalocean_token = DEIN_DIGITALOCEAN_API_TOKEN
	chmod 600 /home/root/do.ini

### 5.3 SSL-Zertifikate erstellen

	sudo certbot certonly \
	--dns-digitalocean \
	--dns-digitalocean-credentials /home/root/do.ini \
	-d petrilog.com \
	-d '*.petrilog.com'

### 5.4 Nginx für SSL anpassen

Server für Weiterleitung HTTP → HTTPS:

	server {
		listen 80;
		server_name petrilog.com *.petrilog.com;
		return 301 https://$host$request_uri;
	}

Server für HTTPS:

	server {
		listen 443 ssl;
		server_name petrilog.com *.petrilog.com;

		ssl_certificate /etc/letsencrypt/live/petrilog.com/fullchain.pem;
		ssl_certificate_key /etc/letsencrypt/live/petrilog.com/privkey.pem;

		ssl_protocols TLSv1.2 TLSv1.3;
		ssl_ciphers HIGH:!aNULL:!MD5;
		ssl_prefer_server_ciphers on;
		ssl_session_cache shared:SSL:10m;

		root /var/www/petrilog.com/current;
		index index.html index.htm index.php;

		location / {
			try_files $uri $uri/ =404;
		}

		location ~ \.php$ {
			include snippets/fastcgi-php.conf;
			fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
		}

		access_log /var/log/nginx/petrilog.com.access.log;
		error_log /var/log/nginx/petrilog.com.error.log;
	}

### 5.5 Nginx prüfen & neu starten

	sudo nginx -t
	sudo systemctl restart nginx

### 5.6 Zertifikats-Erneuerung testen

	sudo certbot renew --dry-run

## 6. Swap-Speicher für Build-Prozesse

	sudo fallocate -l 2G /swapfile
	sudo chmod 600 /swapfile
	sudo mkswap /swapfile
	sudo swapon /swapfile

## 7. Deployment & Rechte

### 7.1 Rechte für Deploy-Ordner

	sudo chown -R deployer:deployer /var/www/petrilog.com

### 7.2 ACL für Deployer

	sudo apt-get install acl -y

### 7.3 Node.js installieren

	curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
	sudo apt install -y nodejs
	node -v
	npm -v

### 7.4 Rechte für sudo ohne Passwort

	sudo visudo -f /etc/sudoers.d/deployer

	Eintragen:
	deployer ALL=(ALL) NOPASSWD: /bin/systemctl restart php8.4-fpm
	deployer ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl

## 8. SSR Server via Supervisor

### 8.1 Supervisor installieren

	sudo apt-get install supervisor -y

### 8.2 Supervisor Config anlegen

	sudo nano /etc/supervisor/conf.d/inertia-ssr.conf

	Inhalt:
	[program:inertia-ssr]
	directory=/var/www/petrilog.com/current
	command=php artisan inertia:start-ssr
	autostart=true
	autorestart=true
	user=deployer
	redirect_stderr=true
	stdout_logfile=/var/www/petrilog.com/shared/logs/inertia-ssr.log

### 8.3 Log-Verzeichnis erstellen

	mkdir -p /var/www/petrilog.com/shared/logs

### 8.4 Supervisor starten

	sudo supervisorctl reread
	sudo supervisorctl update
	sudo supervisorctl start inertia-ssr

## 9. Image Processing Dependencies

	sudo apt install jpegoptim optipng pngquant gifsicle svgo webp -y

## Queue vorbereiten

Supervisor sorgt dafür, dass der Queue-Worker im Hintergrund läuft und nach einem Crash automatisch neu startet.

Beispiel-Konfiguration für Laravel:

Datei :
	
	/etc/supervisor/conf.d/petrilog-queue.conf:

Inhalt:

	[program:petrilog-queue]
	process_name=%(program_name)s_%(process_num)02d
	command=php /var/www/petrilog.com/current/artisan queue:work
	autostart=true
	autorestart=true
	user=deployer
	numprocs=1
	redirect_stderr=true
	stdout_logfile=/var/www/petrilog.com/storage/logs/queue.log


numprocs=1 → Anzahl paralleler Worker, je nach Last erhöhen

Nach Änderung laden:

	sudo supervisorctl reread
	sudo supervisorctl update
	sudo supervisorctl start petrilog-queue:*

