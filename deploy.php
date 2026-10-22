<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'https://github.com/Korbe/PetriLog.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// npm install/build can easily run longer than Deployer's 300s default,
// which then tries to kill the remote process; with ssh_multiplexing off
// that kill can fail with "kill -9 -" (no PID captured). Give it more room.
set('default_timeout', 1800);

host('207.154.223.201')
    ->set('ssh_multiplexing', false)
    ->set('remote_user', 'deployer')
    ->set('deploy_path', '/var/www/petrilog.com');

// Hooks

// Build assets locally instead of on the (memory-constrained) server and
// upload the build output. Avoids npm install/build ever running remotely.
task('assets:build', function () {
    runLocally('npm install');
    runLocally('npm run build');
});

task('assets:upload', function () {
    // upload() needs rsync, which Windows doesn't ship with. scp is already
    // proven to work here (same connection ssh/dep itself uses), so use that.
    $target = currentHost()->connectionString();
    run('mkdir -p {{release_path}}/public/build {{release_path}}/bootstrap/ssr');
    runLocally("scp -r public/build/. {$target}:{{release_path}}/public/build/");
    runLocally("scp -r bootstrap/ssr/. {$target}:{{release_path}}/bootstrap/ssr/");
});

task('fpm:restart', function () {
    run('sudo systemctl reload php8.4-fpm');
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



after('deploy:vendors', 'assets:build');
after('assets:build', 'assets:upload');
//after('assets:upload', 'build:fix-permissions');
after('deploy:success', 'fpm:restart');
after('deploy:success', 'nginx:reload');
after('deploy:success', 'ssr:restart');
after('deploy:success', 'queue:restart');
// after('deploy:success', 'queue:supervisorctl:restart');

after('deploy:setup', 'deploy:unlock');
after('deploy:failed', 'deploy:unlock');


