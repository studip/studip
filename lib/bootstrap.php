<?php
# Lifter010: TODO
/*
 * Copyright (c) 2009  Stud.IP CoreGroup
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

namespace Studip {
    const ENV = 'development';
}
// use default namespace for the remaining lines
namespace {

    //software version - please leave it as it is!
    $SOFTWARE_VERSION = '4.4.alpha-svn';

    global $PHP_SELF, $STUDIP_BASE_PATH;

    $PHP_SELF = $_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
    $STUDIP_BASE_PATH = realpath(dirname(__FILE__) . '/..');

    set_include_path(
        $STUDIP_BASE_PATH
        . PATH_SEPARATOR . $STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'config'
        . PATH_SEPARATOR . get_include_path()
    );

    $ABSOLUTE_PATH_STUDIP = $STUDIP_BASE_PATH . '/public/';

    $CANONICAL_RELATIVE_PATH_STUDIP = dirname($_SERVER['PHP_SELF']);
    if (DIRECTORY_SEPARATOR != '/') {
        $CANONICAL_RELATIVE_PATH_STUDIP = str_replace(DIRECTORY_SEPARATOR, '/', $CANONICAL_RELATIVE_PATH_STUDIP);
    }
    // CANONICAL_RELATIVE_PATH_STUDIP should end with a '/'
    if (substr($CANONICAL_RELATIVE_PATH_STUDIP,-1) != "/"){
        $CANONICAL_RELATIVE_PATH_STUDIP .= "/";
    }

    $ABSOLUTE_URI_STUDIP = "";

    // automagically compute ABSOLUTE_URI_STUDIP if $_SERVER['SERVER_NAME'] is set
    if (isset($_SERVER['SERVER_NAME'])) {
        // work around possible bug in lighttpd
        if (mb_strpos($_SERVER['SERVER_NAME'], ':') !== false) {
            list($_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT']) =
                explode(':', $_SERVER['SERVER_NAME']);
        }

        $ABSOLUTE_URI_STUDIP = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        $ABSOLUTE_URI_STUDIP .= '://'.$_SERVER['SERVER_NAME'];

        if ($_SERVER['HTTPS'] == 'on' && $_SERVER['SERVER_PORT'] != 443 ||
            $_SERVER['HTTPS'] != 'on' && $_SERVER['SERVER_PORT'] != 80) {
            $ABSOLUTE_URI_STUDIP .= ':'.$_SERVER['SERVER_PORT'];
        }

        $ABSOLUTE_URI_STUDIP .= $CANONICAL_RELATIVE_PATH_STUDIP;
    }

    // default ASSETS_URL, customize if required
    $GLOBALS['ASSETS_URL'] = $ABSOLUTE_URI_STUDIP . 'assets/';

    // if in dev mode and webpack dev server is running, adjust assets url
    if (Studip\ENV === 'development') {
        $wds_config = json_decode(
            file_get_contents("{$STUDIP_BASE_PATH}/config/webpack.dev-server.config.json")
        );

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (@socket_connect($socket, $wds_config->host, $wds_config->port)) {
            socket_close($socket);

            $assets_url = sprintf(
                "%s://%s:%u/%s/",
                $wds_config->protocol,
                $wds_config->host,
                $wds_config->port,
                basename(realpath(__DIR__ . '/..'))
            );

            $probe_headers = get_headers("{$assets_url}images/logos/studip-logo.svg");
            if (strpos($probe_headers[0], '200') !== false) {
                $GLOBALS['ASSETS_URL'] = $assets_url;
            }
        }
    }

    require 'lib/classes/StudipFileloader.php';
    StudipFileloader::load('config_defaults.inc.php config_local.inc.php', $GLOBALS, compact('STUDIP_BASE_PATH', 'ABSOLUTE_URI_STUDIP', 'ASSETS_URL', 'CANONICAL_RELATIVE_PATH_STUDIP'), true);

    require __DIR__ . '/bootstrap-autoload.php';

    // construct absolute URL for ASSETS_URL
    if ($GLOBALS['ASSETS_URL'][0] === '/') {
        $host = preg_replace('%^([a-z]+:/*[^/]*).*%', '$1', $GLOBALS['ABSOLUTE_URI_STUDIP']);
        $GLOBALS['ASSETS_URL'] = $host . $GLOBALS['ASSETS_URL'];
    } else if (!preg_match('/^[a-z]+:/', $GLOBALS['ASSETS_URL'])) {
        $GLOBALS['ASSETS_URL'] = $GLOBALS['ABSOLUTE_URI_STUDIP'] . $GLOBALS['ASSETS_URL'];
    }

    require 'config.inc.php';

    require 'lib/phplib/page_open.php';
    require_once 'lib/functions.php';
    require_once 'lib/language.inc.php';
    require_once 'lib/visual.inc.php';
    require_once 'lib/deputies_functions.inc.php';

    //setup default logger
    Log::get()->setHandler($GLOBALS['TMP_PATH'] . '/studip.log');
    if (Studip\ENV == 'development') {
        Log::get()->setLogLevel(Log::DEBUG);
    } else {
        Log::get()->setLogLevel(Log::ERROR);
    }

    // set assets url
    Assets::set_assets_url($GLOBALS['ASSETS_URL']);

    // globale template factory anlegen
    require_once 'vendor/flexi/lib/flexi.php';
    $GLOBALS['template_factory'] =
        new Flexi_TemplateFactory($STUDIP_BASE_PATH . '/templates');
    Renderable::setTemplateFactory($GLOBALS['template_factory']);


    // set default pdo connection
    try {
        DBManager::getInstance()
            ->setConnection('studip',
                'mysql:host=' . $GLOBALS['DB_STUDIP_HOST'] .
                ';dbname=' . $GLOBALS['DB_STUDIP_DATABASE'] .
                ';charset=utf8mb4',
                $GLOBALS['DB_STUDIP_USER'],
                $GLOBALS['DB_STUDIP_PASSWORD']);
    } catch (PDOException $exception) {
        header('HTTP/1.1 500 Internal Server Error');
        die(sprintf('database connection %s failed', 'mysql:host=' . $GLOBALS['DB_STUDIP_HOST'] .
        ';dbname=' . $GLOBALS['DB_STUDIP_DATABASE']));
    }
    // set slave connection
    if (isset($GLOBALS['DB_STUDIP_SLAVE_HOST'])) {
        try {
            DBManager::getInstance()
                ->setConnection('studip-slave',
                    'mysql:host=' . $GLOBALS['DB_STUDIP_SLAVE_HOST'] .
                    ';dbname=' . $GLOBALS['DB_STUDIP_SLAVE_DATABASE'] .
                    ';charset=utf8mb4',
                    $GLOBALS['DB_STUDIP_SLAVE_USER'],
                    $GLOBALS['DB_STUDIP_SLAVE_PASSWORD']);
        } catch (PDOException $exception) {
            // if connection to slave fails, fall back to master instead
            DBManager::getInstance()->aliasConnection('studip', 'studip-slave');
        }
    } else {
        DBManager::getInstance()->aliasConnection('studip', 'studip-slave');
    }

    // set default exception handler
    // command line or http request?
    if (isset($_SERVER['REQUEST_METHOD'])) {
        set_exception_handler('studip_default_exception_handler');
    }

    // Prime autoloader if cache is enabled (this cannot be in autoloader's
    // bootstrap because the stud.ip cache needs to have a db conenction)
    if ($GLOBALS['CACHING_ENABLE']) {
        $cached = StudipCacheFactory::getCache()->read('STUDIP#autoloader-classes');
        if ($cached) {
            $class_lookup = json_decode($cached, true);
            if (is_array($class_lookup)) {
                $lookup_hash = md5($cached);
                StudipAutoloader::addClassLookups($class_lookup);
            }
        }

        register_shutdown_function(function () use ($lookup_hash) {
            $cached = json_encode(StudipAutoloader::$class_lookup, JSON_UNESCAPED_UNICODE);
            if (md5($cached) !== $lookup_hash) {
                StudipCacheFactory::getCache()->write(
                    'STUDIP#autoloader-classes',
                    $cached,
                    7 * 24 * 60 * 60
                );
            }
        });
    }

    // set default time zone
    date_default_timezone_set(Config::get()->DEFAULT_TIMEZONE ? : @date_default_timezone_get());

    // sample the request time and number of db queries every tenth time
    register_shutdown_function(function ($timer) {
        $timer('core.request_time', 0.1);

        $query_count = DBManager::get()->query_count;
        Metrics::gauge('core.database.queries', $query_count, 0.1);
    }, Metrics::startTimer());

    //include 'tools/debug/StudipDebugPDO.class.php';

    /**
     * @deprecated
     */
    class DB_Seminar extends DB_Sql
    {
        public function __construct($query = false)
        {
            $this->Host = $GLOBALS['DB_STUDIP_HOST'];
            $this->Database = $GLOBALS['DB_STUDIP_DATABASE'];
            $this->User = $GLOBALS['DB_STUDIP_USER'];
            $this->Password = $GLOBALS['DB_STUDIP_PASSWORD'];
            parent::__construct($query);
        }
    }

    require_once 'lib/resources/resourcesFunc.inc.php';
    require_once 'lib/resources/lib/list_assign.inc.php';

    if (Config::get()->EXTERN_ENABLE) {
        require_once $GLOBALS['STUDIP_BASE_PATH'] . '/lib/extern/extern_config.inc.php';
        require_once $GLOBALS['STUDIP_BASE_PATH'] . '/lib/extern/lib/extern_functions.inc.php';
    }

    if (Config::get()->CALENDAR_ENABLE) {
        require_once 'lib/calendar_functions.inc.php';
    }

    if (Config::get()->SOAP_ENABLE) {
        require_once 'lib/soap/StudipSoapClient' . (Config::get()->SOAP_USE_PHP5 ? '_PHP5' : '' ) . '.class.php';
    }

    if (Config::Get()->ILIAS_INTERFACE_ENABLE) {
        require_once 'lib/ilias_interface/IliasUserObserver.php';
    }

    // set dummy navigation until db is ready
    Navigation::setRootNavigation(new Navigation(''));

    // set up default page layout
    PageLayout::initialize();

    // init notification observers
    Studip\Activity\ActivityObserver::initialize();
    FilesSearch\NotificationObserver::initialize();
    if (Config::Get()->ILIAS_INTERFACE_ENABLE) {
        IliasUserObserver::initialize();
    }

    //Besser hier globale Variablen definieren...
    $GLOBALS['_fullname_sql'] = [];
    $GLOBALS['_fullname_sql']['full'] = "TRIM(CONCAT(title_front,' ',Vorname,' ',Nachname,IF(title_rear!='',CONCAT(', ',title_rear),'')))";
    $GLOBALS['_fullname_sql']['full_rev'] = "TRIM(CONCAT(Nachname,', ',Vorname,IF(title_front!='',CONCAT(', ',title_front),''),IF(title_rear!='',CONCAT(', ',title_rear),'')))";
    $GLOBALS['_fullname_sql']['no_title'] = "CONCAT(Vorname ,' ', Nachname)";
    $GLOBALS['_fullname_sql']['no_title_rev'] = "CONCAT(Nachname ,', ', Vorname)";
    $GLOBALS['_fullname_sql']['no_title_short'] = "CONCAT(Nachname,', ',UCASE(LEFT(TRIM(Vorname),1)),'.')";
    $GLOBALS['_fullname_sql']['no_title_motto'] = "CONCAT(Vorname ,' ', Nachname,IF(motto!='',CONCAT(', ',motto),''))";
    $GLOBALS['_fullname_sql']['full_rev_username'] = "TRIM(CONCAT(Nachname,', ',Vorname,IF(title_front!='',CONCAT(', ',title_front),''),IF(title_rear!='',CONCAT(', ',title_rear),''),' (',username,')'))";

    //Initialize $SEM_TYPE and $SEM_CLASS arrays
    $GLOBALS['SEM_CLASS'] = SemClass::getClasses();
    $GLOBALS['SEM_TYPE'] = SemType::getTypes();

    // set up global navigation
    Navigation::setRootNavigation(new StudipNavigation(''));

    /* set default umask to a sane value */
    umask(022);

    /*mail settings
    ----------------------------------------------------------------*/
    if ($GLOBALS['MAIL_TRANSPORT']) {
        $mail_transporter_name = mb_strtolower($GLOBALS['MAIL_TRANSPORT']) . '_message';
    } else {
        $mail_transporter_name = 'smtp_message';
    }
    include 'vendor/email_message/email_message.php';
    include 'vendor/email_message/' . $mail_transporter_name . '.php';
    $mail_transporter_class = $mail_transporter_name . '_class';
    $mail_transporter = new $mail_transporter_class;
    if ($mail_transporter_name == 'smtp_message') {
        include 'vendor/email_message/smtp.php';
        $mail_transporter->localhost = ($GLOBALS['MAIL_LOCALHOST'] == "") ? $_SERVER["SERVER_NAME"] : $GLOBALS['MAIL_LOCALHOST'];
        $mail_transporter->smtp_host = ($GLOBALS['MAIL_HOST_NAME'] == "") ? $_SERVER["SERVER_NAME"] : $GLOBALS['MAIL_HOST_NAME'];
        if (is_array($GLOBALS['MAIL_SMTP_OPTIONS'])) {
            foreach ($GLOBALS['MAIL_SMTP_OPTIONS'] as $key => $value) {
                $mail_transporter->{"smtp_$key"} = $value;
            }
            if ($mail_transporter->smtp_user !== '') {
                include 'vendor/sasl/sasl.php';
            }
        }
    }
    $mail_transporter->default_charset = 'UTF-8';
    $mail_transporter->SetBulkMail((int)$GLOBALS['MAIL_BULK_DELIVERY']);
    StudipMail::setDefaultTransporter($mail_transporter);
    unset($mail_transporter);
}
