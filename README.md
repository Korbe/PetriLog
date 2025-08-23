Neccesary Dep on Server apt install jpegoptim optipng pngquant gifsicle svgo webp


# SSR Server

sudo apt-get install supervisor

Dann eine neue Config erstellen etc/supervisor/conf.d/inertia-ssr.conf

[program:inertia-ssr]
directory=/var/www/petrilog.korbitsch.at/current
command=php artisan inertia:start-ssr
autostart=true
autorestart=true
user=deployer
redirect_stderr=true
stdout_logfile=/var/www/petrilog.korbitsch.at/shared/logs/inertia-ssr.log

mkdir logs im shared folder

Dann:

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start inertia-ssr