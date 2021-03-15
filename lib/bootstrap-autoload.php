<?php
// Include composer's autoload
require __DIR__ . '/../composer/autoload.php';

// Setup autoloading
require 'lib/classes/StudipAutoloader.php';
StudipAutoloader::register();

// General classes folders
StudipAutoloader::addAutoloadPath('lib/models');
StudipAutoloader::addAutoloadPath('lib/models/resources');
StudipAutoloader::addAutoloadPath('lib/classes');
StudipAutoloader::addAutoloadPath('lib/classes', 'Studip');

// Plugins
StudipAutoloader::addAutoloadPath('lib/plugins/core');
StudipAutoloader::addAutoloadPath('lib/plugins/db');
StudipAutoloader::addAutoloadPath('lib/plugins/engine');

// Specialized folders
StudipAutoloader::addAutoloadPath('lib/classes/admission');
StudipAutoloader::addAutoloadPath('lib/classes/admission/userfilter');
StudipAutoloader::addAutoloadPath('lib/classes/auth_plugins');
StudipAutoloader::addAutoloadPath('lib/classes/calendar');
StudipAutoloader::addAutoloadPath('lib/classes/exportdocument');
StudipAutoloader::addAutoloadPath('lib/classes/globalsearch');
StudipAutoloader::addAutoloadPath('lib/classes/helpbar');
StudipAutoloader::addAutoloadPath('lib/classes/librarysearch/resultparsers');
StudipAutoloader::addAutoloadPath('lib/classes/librarysearch/searchmodules');
StudipAutoloader::addAutoloadPath('lib/classes/librarysearch');
StudipAutoloader::addAutoloadPath('lib/classes/searchtypes');
StudipAutoloader::addAutoloadPath('lib/classes/sidebar');
StudipAutoloader::addAutoloadPath('lib/classes/visibility');
StudipAutoloader::addAutoloadPath('lib/classes/coursewizardsteps');

StudipAutoloader::addAutoloadPath('lib/calendar');
StudipAutoloader::addAutoloadPath('lib/calendar', 'Studip\\Calendar');
StudipAutoloader::addAutoloadPath('lib/exceptions');
StudipAutoloader::addAutoloadPath('lib/exceptions/resources');
StudipAutoloader::addAutoloadPath('lib/filesystem');
StudipAutoloader::addAutoloadPath('lib/migrations');
StudipAutoloader::addAutoloadPath('lib/modules');
StudipAutoloader::addAutoloadPath('lib/navigation');
StudipAutoloader::addAutoloadPath('lib/phplib');
StudipAutoloader::addAutoloadPath('lib/raumzeit');
StudipAutoloader::addAutoloadPath('lib/resources');
StudipAutoloader::addAutoloadPath('lib/activities', 'Studip\\Activity');
StudipAutoloader::addAutoloadPath('lib/evaluation/classes');
StudipAutoloader::addAutoloadPath('lib/evaluation/classes/db');

StudipAutoloader::addAutoloadPath('lib/extern/lib');
StudipAutoloader::addAutoloadPath('lib/calendar/lib');
StudipAutoloader::addAutoloadPath('lib/elearning');
StudipAutoloader::addAutoloadPath('lib/ilias_interface');

// Messy file names
StudipAutoloader::addClassLookups([
    'email_validation_class' => 'lib/phplib/email_validation.class.php',
    'messaging'              => 'lib/messaging.inc.php',
    'StudipPlugin'           => 'lib/plugins/core/StudIPPlugin.class.php',
    'MVVController'          => 'app/controllers/module/mvv_controller.php'
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
    'vendor/trails/trails.php'
);
StudipAutoloader::addClassLookup(
    'StudipController',
    'app/controllers/studip_controller.php'
);
StudipAutoloader::addClassLookup(
    'AuthenticatedController',
    'app/controllers/authenticated_controller.php'
);
StudipAutoloader::addClassLookup(
    'PluginController',
    'app/controllers/plugin_controller.php'
);

// Vendor
StudipAutoloader::addClassLookups([
    'PasswordHash' => 'vendor/phpass/PasswordHash.php',
]);

// XMLRpc
StudipAutoloader::addClassLookup(
    ['xmlrpcval', 'xmlrpcmsg', 'xmlrpcresp', 'xmlrpc_client'],
    'composer/phpxmlrpc/phpxmlrpc/lib/xmlrpc.inc'
);
StudipAutoloader::addClassLookup(
    ['xmlrpc_server'],
    'composer/phpxmlrpc/phpxmlrpc/lib/xmlrpcs.inc'
);
