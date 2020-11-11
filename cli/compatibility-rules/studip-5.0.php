<?php
// "Rules"/definitions for critical changes in 5.0
return [
    'ScheduleDidUpdate' => 'Parameters for Notification have changed. You will now receive a #{yellow:Schedule object} instead of user_id and values array.',
    'ScheduleDidCreate' => 'Parameters for Notification have changed. You will now receive a #{yellow:Schedule object} instead of user_id and values array.',

    'SemesterData'      => '#{red:Removed} - use #{yellow:Semester model} instead',
    'StatusgroupsModel' => '#{red:Removed} - use #{yellow:Statusgruppen model} instead',
    'StudipNullCache'   => '#{red:Removed} - use #{yellow:StudipMemoryCache} instead',

    'get_config' => '#{red:Deprecated} - use #{yellow:Config::get()} instead',

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

];
