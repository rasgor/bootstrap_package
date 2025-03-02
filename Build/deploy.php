<?php

/*
 * This file is part of the package bk2k/bootstrap-package.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Deployer;

require 'recipe/common.php';

// TYPO3
desc('Prepare Bootstrap Package');
task('typo3:prepare', function () {
    run('rm -rf {{release_path}}/../tmp');
    run('mkdir -p {{release_path}}/../tmp/extensions/bootstrap_package');
    run('mv {{release_path}}/{,.[^.]}* {{release_path}}/../tmp/extensions/bootstrap_package');
    run('mkdir -p {{release_path}}/extensions');
    run('mv {{release_path}}/../tmp/extensions {{release_path}}');
    run('rm -rf {{release_path}}/../tmp');
});
desc('Finish TYPO3 Deployment');
task('typo3:finish', function () {
    run('cd {{release_path}} && {{bin/php}} ./bin/typo3 extension:setup');
    run('cd {{release_path}} && {{bin/php}} ./bin/typo3 cache:flush');
    run('cd {{release_path}} && {{bin/php}} ./bin/typo3 cache:warmup');
    run('cd {{release_path}} && {{bin/php}} ./bin/typo3 upgrade:run');
});

// Main
 task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'typo3:prepare',
    'deploy:shared',
    'deploy:vendors',
    'deploy:symlink',
    'typo3:finish',
    'deploy:unlock',
    'cleanup',
])->desc('Deploy your project');
after('deploy', 'success');

// If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Shared Directories and Files
set('shared_dirs', [
    'config',
    'web/fileadmin',
    'web/typo3temp',
    'web/uploads'
]);
set('shared_files', [
    'composer.json',
    'web/.htaccess',
    'web/typo3conf/AdditionalConfiguration.php',
    'web/typo3conf/LocalConfiguration.php',
    'web/typo3conf/PackageStates.php'
]);

// Set Writeable files
set('writable_dirs', [
    'config',
    'web/fileadmin',
    'web/typo3temp',
    'web/typo3conf',
    'web/uploads'
]);

// Misc
set('allow_anonymous_stats', false);

// Hosts
host(getenv('SSH_HOST'))
    ->set('repository', 'https://github.com/benjaminkott/bootstrap_package')
    ->user(getenv('SSH_USER'))
    ->port('22')
    ->set('keep_releases', '2')
    ->set('bin/php', 'php')
    ->set('deploy_path', '~/html/{{application}}')
    ->set('application', 'bootstrappackage')
    ->set('ssh_type', 'native')
    ->set('http_user', getenv('SSH_USER'))
    ->set('bin/composer', 'composer');
