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

task('nginx:reload', function () {
    run('sudo systemctl reload nginx');
});

task('ssr:restart', function () {
    run('sudo supervisorctl restart inertia-ssr');
});

task('build:fix-permissions', function () {
    run('sudo chown -R deployer:deployer {{release_path}}');
});

task('queue:restart', function () {
    run('php {{release_path}}/artisan queue:restart');
});

// task('queue:supervisorctl:restart', function () {
//     run('sudo supervisorctl restart petrilog-queue');
// });



after('deploy:vendors', 'npm:install');
after('npm:install', 'npm:build');
//after('npm:build', 'build:fix-permissions');
after('deploy:success', 'fpm:restart');
after('deploy:success', 'nginx:reload');
after('deploy:success', 'ssr:restart');
after('deploy:success', 'queue:restart');
// after('deploy:success', 'queue:supervisorctl:restart');

after('deploy:setup', 'deploy:unlock');
after('deploy:failed', 'deploy:unlock');


