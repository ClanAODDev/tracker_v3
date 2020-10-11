<?php

namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'tracker_v3');

// Project repository
set('repository', 'git@github.com:clanaod/tracker_v3.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

set('ssh_multiplexing', true);

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', []);
set('allow_anonymous_stats', false);

// Hosts

host('162.248.89.152')
    ->user('guybrush')
    ->port(27922)
    ->stage('development')
    ->set('branch', 'development')
    ->forwardAgent(true)
    ->set('deploy_path', '/home/guybrush/tracker/v3_dev');

host('162.248.89.152')
    ->user('guybrush')
    ->port(27922)
    ->stage('production')
    ->set('branch', 'master')
    ->forwardAgent(true)
    ->set('deploy_path', '/home/guybrush/tracker/v3_prod');

// Tasks
task('artisan:optimize', function () {});

task('build', function () {
    run('cd {{release_path}} && build');
});

task('init:env', function () {
    upload('.env', '{{deploy_path}}/shared/');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Setup shared public disk that will be long-lived
before('deploy:symlink', 'deploy:public_disk');

// Migrate database before symlink new release.

before('deploy:symlink', 'artisan:migrate');

