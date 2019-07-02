<?php
// Include composer's autoload
require __DIR__ . '/../composer/autoload.php';

// Setup autoloading
require 'lib/classes/StudipAutoloader.php';
StudipAutoloader::register();

// General classes folders
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/models');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/classes');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/classes', 'Studip');

// Plugins
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/plugins/core');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/plugins/db');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/plugins/engine');

// Specialized folders
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/classes/admission');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/classes/admission/userfilter');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/classes/auth_plugins');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/classes/exportdocument');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/classes/globalsearch');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/classes/helpbar');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/classes/searchtypes');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/classes/sidebar');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/classes/visibility');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/classes/coursewizardsteps');

StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/calendar');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/exceptions');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/filesystem');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/migrations');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/modules');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/navigation');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/phplib');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/raumzeit');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/activities', 'Studip\\Activity');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/evaluation/classes');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/evaluation/classes/db');

StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/resources/lib');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/extern/lib');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/calendar/lib');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/elearning');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/lib/ilias_interface');

// Classes in /app
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/app/models');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/app/models', 'Studip');

// Messy file names
StudipAutoloader::addClassLookups([
    'email_validation_class' => $GLOBALS['STUDIP_BASE_PATH'] . '/lib/phplib/email_validation.class.php',
    'messaging'              => $GLOBALS['STUDIP_BASE_PATH'] . '/lib/messaging.inc.php',
    'MediaProxy'             => $GLOBALS['STUDIP_BASE_PATH'] . '/app/models/media_proxy.php',
    'MyRealmModel'           => $GLOBALS['STUDIP_BASE_PATH'] . '/app/models/my_realm.php',
    'StudygroupModel'        => $GLOBALS['STUDIP_BASE_PATH'] . '/app/models/studygroup.php',
    'StudipPlugin'           => $GLOBALS['STUDIP_BASE_PATH'] . '/lib/plugins/core/StudIPPlugin.class.php',
    'PluginAdministration'   => $GLOBALS['STUDIP_BASE_PATH'] . '/app/models/plugin_administration.php',
    'CalendarScheduleModel'  => $GLOBALS['STUDIP_BASE_PATH'] . '/app/models/calendar/schedule.php',
    'MVVController'          => $GLOBALS['STUDIP_BASE_PATH'] . '/app/controllers/module/mvv_controller.php'
]);

// Trails
$trails_classes = [
    'Trails_Dispatcher', 'Trails_Response', 'Trails_Controller',
    'Trails_Inflector', 'Trails_Flash',
    'Trails_Exception', 'Trails_DoubleRenderError', 'Trails_MissingFile',
    'Trails_RoutingError', 'Trails_UnknownAction', 'Trails_UnknownController',
    'Trails_SessionRequiredException',
];
StudipAutoloader::addClassLookup(
    $trails_classes,
    $GLOBALS['STUDIP_BASE_PATH'] . '/vendor/trails/trails.php'
);
StudipAutoloader::addClassLookup(
    'StudipController',
    $GLOBALS['STUDIP_BASE_PATH'] . '/app/controllers/studip_controller.php'
);
StudipAutoloader::addClassLookup(
    'AuthenticatedController',
    $GLOBALS['STUDIP_BASE_PATH'] . '/app/controllers/authenticated_controller.php'
);
StudipAutoloader::addClassLookup(
    'PluginController',
    $GLOBALS['STUDIP_BASE_PATH'] . '/app/controllers/plugin_controller.php'
);

// Vendor
StudipAutoloader::addClassLookups([
    'PasswordHash' => $GLOBALS['STUDIP_BASE_PATH'] . '/vendor/phpass/PasswordHash.php',
    'TCPDF'        => $GLOBALS['STUDIP_BASE_PATH'] . '/vendor/tcpdf/tcpdf.php',
    'DocBlock'     => $GLOBALS['STUDIP_BASE_PATH'] . '/vendor/docblock-parser/docblock-parser.php',
]);
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/vendor/mishal-iless/lib/ILess', 'ILess');
