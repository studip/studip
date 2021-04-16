<?php
// "Rules"/definitions for critical changes in 5.0
return [
    // https://develop.studip.de/trac/ticket/11250
    'userMayAccessRange' => '#{yellow:Changed} - Use #{yellow:isAccessibleToUser} instead',
    'userMayEditRange' => '#{yellow:Changed} - Use #{yellow:isEditableByUser} instead',
    'userMayAdministerRange' => '#{red:Removed}',

    // UTF8-Encode/Decode legacy functions
    'studip_utf8encode' => '#{red:Removed} - Use utf8_encode().',
    'studip_utf8decode' => '#{red:Removed} - Use utf8_decode().',

    // JSON encode/decode legacy functions
    'studip_json_decode' => '#{red:Deprecated} - Use json_decode() and pay attention to the second parameter.',
    'studip_json_encode' => '#{red:Deprecated} - Use json_encode().',

    // https://develop.studip.de/trac/ticket/10806
    'SemesterData' => '#{red:Removed} - Use #{yellow:Semester model} instead',

    // https://develop.studip.de/trac/ticket/10786
    'StatusgroupsModel' => '#{red:Removed} - Use #{yellow:Statusgruppen model} instead',

    // https://develop.studip.de/trac/ticket/10796
    'StudipNullCache' => '#{red:Removed} - Use #{yellow:StudipMemoryCache} instead',

    // https://develop.studip.de/trac/ticket/10838
    'getDeputies' => '#{red:Removed} - Use #{yellow:Deputy::findDeputies()} instead',
    'getDeputyBosses' => '#{red:Removed} - Use #{yellow:Deputy::findDeputyBosses()} instead',
    '/(?<!Deputy::)addDeputy/' => '#{red:Removed} - Use #{yellow:Deputy::addDeputy()} instead',
    '/deleteDeputy(?=\()/' => '#{red:Removed} - Use #{yellow:Deputy model} instead',
    'deleteAllDeputies' => '#{red:Removed} - Use #{yellow:Deputy::deleteByRange_id} instead',
    '/(?<!Deputy::)isDeputy/' => '#{red:Removed} - Use #{yellow:Deputy::isDeputy()} instead',
    'setDeputyHomepageRights' => '#{red:Removed} - Use #{yellow:Deputy model} instead',
    'getValidDeputyPerms' => '#{red:Removed} - Use #{yellow:Deputy::getValidPerms()} instead',
    'isDefaultDeputyActivated' => '#{red:Removed} - Use #{yellow:Deputy::isActivated()} instead',
    'getMyDeputySeminarsQuery' => '#{red:Removed} - Use #{yellow:Deputy::getMySeminarsQuery()} instead',
    'isDeputyEditAboutActivated' => '#{red:Removed} - Use #{yellow:Deputy::isEditActivated()} instead',

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
