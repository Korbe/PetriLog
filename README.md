Neccesary Dep on Server apt install jpegoptim optipng pngquant gifsicle svgo webp


# SSR Server

# Install dependencie

    sudo apt-get install supervisor

Dann eine neue Config erstellen 

    etc/supervisor/conf.d/inertia-ssr.conf

Inhalt:

    [program:inertia-ssr]
    directory=/var/www/petrilog.korbitsch.at/current
    command=php artisan inertia:start-ssr
    autostart=true
    autorestart=true
    user=deployer
    redirect_stderr=true
    stdout_logfile=/var/www/petrilog.korbitsch.at/shared/logs/inertia-ssr.log

Im shared folder vom deployment path

    mkdir logs 

Danach

    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl start inertia-ssr


# For Deployer 

## Rights to execute sudo command on server for special commands
Du kannst deine eigenen Regeln sauber in einer separaten Datei unter /etc/sudoers.d/ ablegen.

    sudo visudo -f /etc/sudoers.d/deployer


Dann trägst du dort Folgendes ein (angenommen dein User heißt deployer):

    deployer ALL=(ALL) NOPASSWD: /bin/systemctl restart php8.3-fpm
    deployer ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl