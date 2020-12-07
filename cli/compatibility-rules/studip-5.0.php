<?php
// "Rules"/definitions for critical changes in 5.0
return [
    'userMayAdministerRange' => '#{Changed} - Use #{yellow:userMayManageRange} instead',

    // https://develop.studip.de/trac/ticket/10806
    'SemesterData' => '#{red:Removed} - Use #{yellow:Semester model} instead',

    // https://develop.studip.de/trac/ticket/10786
    'StatusgroupsModel' => '#{red:Removed} - Use #{yellow:Statusgruppen model} instead',

    // https://develop.studip.de/trac/ticket/10796
    'StudipNullCache' => '#{red:Removed} - Use #{yellow:StudipMemoryCache} instead',

    // https://develop.studip.de/trac/ticket/10870
    'get_config' => '#{red:Deprecated} - Use #{yellow:Config::get()} instead.',

    // https://develop.studip.de/trac/ticket/10919
    'RESTAPI\\RouteMap' => '#{red:Deprecated} - Use the #{yellow:JSONAPI} instead.',

    // https://develop.studip.de/trac/ticket/10878
    'Leafo\\ScssPhp' => 'Library was replaced by #{yellow:scssphp/scssphp}',
    'sfYamlParser'   => 'Library was replaced by #{yellow:symfony/yaml}',
    'DocBlock::of'   => 'Library was replaced by #{yellow:gossi/docblock}',

    'vendor/idna_convert' => 'Remove include/require. Will be autoloaded.',
    'vendor/php-htmldiff' => 'Remove include/require. Will be autoloaded.',
    'vendor/HTMLPurifier' => 'Remove include/require. Will be autoloaded.',
    'vendor/phplot'       => 'Remove include/require. Will be autoloaded.',
    'vendor/phpCAS'       => 'Remove include/require. Will be autoloaded.',
    'vendor/phpxmlrpc'    => 'Remove include/require. Will be autoloaded.',

    // https://develop.studip.de/trac/ticket/10964
    'periodicalPushData'                           => '#{red:Removed} - Use #{yellow:STUDIP.JSUpdater.register()} instead',
    '/UpdateInformtion::setInformation\(.+\..+\)/' => '#{red:Removed} - Use #{yellow:STUDIP.JSUpdater.register()} instead',
];
