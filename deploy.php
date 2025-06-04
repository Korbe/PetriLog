<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'https://github.com/Korbe/PetriLog.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

host('217.154.67.144')
    ->set('ssh_multiplexing', false)
    ->set('remote_user', 'deployer')
    ->set('deploy_path', '/var/www/petrilog.korbitsch.at');

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
    run('sudo systemctl restart php8.3-fpm');
});

after('deploy:vendors', 'npm:install');
after('npm:install', 'npm:build');
after('deploy:success', 'fpm:restart');

after('deploy:setup', 'deploy:unlock');
after('deploy:failed', 'deploy:unlock');
