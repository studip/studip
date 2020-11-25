<?php
// "Rules"/definitions for critical changes in 5.0
return [
    'SemesterData'      => '#{red:Removed} - Use #{yellow:Semester model} instead',
    'StatusgroupsModel' => '#{red:Removed} - Use #{yellow:Statusgruppen model} instead',
    'StudipNullCache'   => '#{red:Removed} - Use #{yellow:StudipMemoryCache} instead',

    'get_config'        => '#{red:Deprecated} - Use #{yellow:Config::get()} instead.',
    'RESTAPI\\RouteMap' => '#{red:Deprecated} - Use the #{yellow:JSONAPI} instead.',

    // Vendor changes
    'Leafo\\ScssPhp' => 'Library was replaced by #{yellow:scssphp/scssphp}',
    'sfYamlParser'   => 'Library was replaced by #{yellow:symfony/yaml}',
    'DocBlock::of'   => 'Library was replaced by #{yellow:gossi/docblock}',

    'vendor/idna_convert' => 'Remove include/require. Will be autoloaded.',
    'vendor/php-htmldiff' => 'Remove include/require. Will be autoloaded.',
    'vendor/HTMLPurifier' => 'Remove include/require. Will be autoloaded.',
    'vendor/phplot'       => 'Remove include/require. Will be autoloaded.',
    'vendor/phpCAS'       => 'Remove include/require. Will be autoloaded.',
    'vendor/phpxmlrpc'    => 'Remove include/require. Will be autoloaded.',

    'periodicalPushData' => '#{red:Removed} - Use #{yellow:STUDIP.JSUpdater.register()} instead',
];
