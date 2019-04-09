<?php
namespace {
    StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . '/vendor/oauth-php/library');
    StudipAutoloader::addClassLookup('DocBlock', $GLOBALS['STUDIP_BASE_PATH'] . '/vendor/docblock-parser/docblock-parser.php');

    // Set base url for URLHelper class
    URLHelper::setBaseUrl($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']);
}

namespace RESTAPI {
    use Studip, OAuthStore;

    // Define api version
    const VERSION = '2';

    $router = Router::getInstance();

    // Register JSON content renderer
    $router->registerRenderer(new Renderer\JSONRenderer, true);

    // If in development mode, register debug content renderer
    if (defined('Studip\\ENV') && Studip\ENV === 'development') {
        $router->registerRenderer(new Renderer\DebugRenderer);
    }

    OAuthStore::instance('PDO', [
        'dsn' => 'mysql:host=' . $GLOBALS['DB_STUDIP_HOST']
                   . ';dbname=' . $GLOBALS['DB_STUDIP_DATABASE'],
        'username' => $GLOBALS['DB_STUDIP_USER'],
        'password' => $GLOBALS['DB_STUDIP_PASSWORD']
    ]);

    // Register default consumers
    Consumer\Base::addType('http', 'RESTAPI\\Consumer\\HTTP');
    Consumer\Base::addType('studip', 'RESTAPI\\Consumer\\Studip');
    Consumer\Base::addType('oauth', 'RESTAPI\\Consumer\\OAuth');

    // $router->registerConsumer('oauth', new Consumer\OAuth);
    // $router->registerConsumer('basic', new Consumer\HTTP);
    // $router->registerConsumer('studip', new Consumer\Studip);
}
