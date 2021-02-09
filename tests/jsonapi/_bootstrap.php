<?php

// Here you can initialize variables that will be available to your tests

global $STUDIP_BASE_PATH, $ABSOLUTE_URI_STUDIP, $CACHING_ENABLE, $CACHING_FILECACHE_PATH, $SYMBOL_SHORT, $TMP_PATH, $UPLOAD_PATH;

// common set-up, usually done by lib/bootstraph.php and
// config/config_local.inc.php when run on web server
if (!isset($STUDIP_BASE_PATH)) {
    $STUDIP_BASE_PATH = dirname(dirname(__DIR__));
    $ABSOLUTE_PATH_STUDIP = $STUDIP_BASE_PATH.'/public/';
    $UPLOAD_PATH = $STUDIP_BASE_PATH.'/data/upload_doc';
    $TMP_PATH = $TMP_PATH ?: '/tmp';
}

// set include path
$inc_path = ini_get('include_path');
$inc_path .= PATH_SEPARATOR.$STUDIP_BASE_PATH;
$inc_path .= PATH_SEPARATOR.$STUDIP_BASE_PATH.'/config';
ini_set('include_path', $inc_path);

// config
$CACHING_ENABLE = false;
//$CACHING_FILECACHE_PATH = '/tmp';

date_default_timezone_set('Europe/Berlin');

require 'config.inc.php';

require 'lib/functions.php';
require 'lib/language.inc.php';
require 'lib/visual.inc.php';
require 'lib/calendar_functions.inc.php';
require 'lib/dates.inc.php';

// Setup autoloading
require 'lib/classes/StudipAutoloader.php';
StudipAutoloader::register();

// General classes folders
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/models');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/models/resources');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/classes');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/classes', 'Studip');

// Plugins
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/plugins/core');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/plugins/db');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/plugins/engine');

StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/calendar');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/calendar', 'Studip\\Calendar');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/calendar/lib');

StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/filesystem');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/migrations');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/modules');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/phplib');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/raumzeit');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/resources');

StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/activities', 'Studip\\Activity');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/classes/calendar');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/classes/globalsearch');
StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/lib/classes/visibility');

StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].'/vendor/oauth-php/library');

// Messy file names
StudipAutoloader::addClassLookups(
    array(
        'StudipPlugin' => $GLOBALS['STUDIP_BASE_PATH'].'/lib/plugins/core/StudIPPlugin.class.php',
        'messaging' => $GLOBALS['STUDIP_BASE_PATH'].'/lib/messaging.inc.php',
    )
);

$GLOBALS['_fullname_sql'] = array();
$GLOBALS['_fullname_sql']['full'] = "TRIM(CONCAT(title_front,' ',Vorname,' ',Nachname,IF(title_rear!='',CONCAT(', ',title_rear),'')))";
$GLOBALS['_fullname_sql']['full_rev'] = "TRIM(CONCAT(Nachname,', ',Vorname,IF(title_front!='',CONCAT(', ',title_front),''),IF(title_rear!='',CONCAT(', ',title_rear),'')))";
$GLOBALS['_fullname_sql']['no_title'] = "CONCAT(Vorname ,' ', Nachname)";
$GLOBALS['_fullname_sql']['no_title_rev'] = "CONCAT(Nachname ,', ', Vorname)";
$GLOBALS['_fullname_sql']['no_title_short'] = "CONCAT(Nachname,', ',UCASE(LEFT(TRIM(Vorname),1)),'.')";
$GLOBALS['_fullname_sql']['no_title_motto'] = "CONCAT(Vorname ,' ', Nachname,IF(motto!='',CONCAT(', ',motto),''))";
$GLOBALS['_fullname_sql']['full_rev_username'] = "TRIM(CONCAT(Nachname,', ',Vorname,IF(title_front!='',CONCAT(', ',title_front),''),IF(title_rear!='',CONCAT(', ',title_rear),''),' (',username,')'))";

SimpleORMap::expireTableScheme();

/**
 * @deprecated
 */
class DB_Seminar extends DB_Sql
{
    public function __construct($query = false)
    {
        parent::__construct($query);
    }
}

require_once __DIR__.'/../../composer/autoload.php';
