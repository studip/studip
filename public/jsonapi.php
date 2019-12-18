<?php

use JsonApi\AppFactory;
use JsonApi\RouteMap;

require '../lib/bootstrap.php';
require '../composer/autoload.php';

\StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'].DIRECTORY_SEPARATOR.'vendor/oauth-php/library/');

page_open(
    [
        'sess' => 'Seminar_Session',
        'auth' => 'Seminar_Default_Auth',
        'perm' => 'Seminar_Perm',
        'user' => 'Seminar_User',
    ]
);

// Set base url for URLHelper class
URLHelper::setBaseUrl($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']);

// create app
$appFactory = new AppFactory();
$app = $appFactory->makeApp();

// add routes
$app->group('/v1', new RouteMap($app));

$app->run();
