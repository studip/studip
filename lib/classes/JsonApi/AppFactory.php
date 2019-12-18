<?php

namespace JsonApi;

use Slim\App;
use StudipPlugin;
use JsonApi\Middlewares\RemoveTrailingSlashes;

/**
 * Diese Klasse erstellt eine neue Slim-Applikation und konfiguriert
 * diese rudimentär vor.
 *
 * Dabei werden im `Dependency Container` der Slim-Applikation unter
 * dem Schlüssel `plugin` das Stud.IP-Plugin vermerkt und außerdem
 * eingestellt, dass Fehler des Slim-Frameworks detailliert angezeigt
 * werden sollen, wenn sich Stud.IP im Modus `development` befindet.
 *
 * Darüber hinaus wird eine Middleware installiert, die alle Requests umleitet,
 * die mit einem Schrägstrich enden (und zwar jeweils auf das Pendant
 * ohne Schrägstrich).
 *
 * @see http://www.slimframework.com/
 * @see \Studip\ENV
 * @see \JsonApi\Middlewares\RemoveTrailingSlashes
 */
class AppFactory
{
    /**
     * Diese Factory-Methode erstellt die Slim-Applikation und
     * konfiguriert diese wie oben angegeben.
     *
     * @return \Slim\App die erstellte Slim-Applikation
     */
    public function makeApp()
    {
        $app = new App();
        $app = $this->configureContainer($app);
        $app->add(new RemoveTrailingSlashes());

        return $app;
    }

    // hier wird der Container konfiguriert
    private function configureContainer($app)
    {
        $container = $app->getContainer();
        $container['settings']['displayErrorDetails'] = defined('\\Studip\\ENV') && \Studip\ENV === 'development';

        $container->register(new Providers\StudipConfig());
        $container->register(new Providers\StudipServices());

        return $app;
    }
}
