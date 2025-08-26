<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'https://github.com/Korbe/PetriLog.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

host('207.154.223.201')
    ->set('ssh_multiplexing', false)
    ->set('remote_user', 'deployer')
    ->set('deploy_path', '/var/www/petrilog.com');

// Hooks
task('npm:install', function () {
    cd('{{release_path}}');
    run('npm install');
});

task('npm:build', function () {
    cd('{{release_path}}');
    run('npm run build');
});

task('fpm:restart', function () {
    run('sudo systemctl restart php8.4-fpm');
});

task('ssr:restart', function () {
    run('sudo supervisorctl restart inertia-ssr');
});


after('deploy:vendors', 'npm:install');
after('npm:install', 'npm:build');
after('deploy:success', 'fpm:restart');
after('deploy:success', 'ssr:restart');

after('deploy:setup', 'deploy:unlock');
after('deploy:failed', 'deploy:unlock');

