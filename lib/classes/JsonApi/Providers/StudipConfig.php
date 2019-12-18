<?php

namespace JsonApi\Providers;

/**
 * Diese Klasse konfiguriert die JSON-API in der Slim-Applikation um
 * die Zuordnung von Schemata zu Stud.IP-Model-Klassen und setzt das
 * URL-Prefix für die interne Generierung von URIs.
 */
class StudipConfig implements \Pimple\ServiceProviderInterface
{
    /**
     * Diese Methode wird automatisch aufgerufen, wenn diese Klasse dem
     * Dependency Container der Slim-Applikation hinzugefügt wird.
     *
     * Hier werden die Schema-Abbildungen und das JSON-API-URL-Präfix gesetzt.
     *
     * @param \Pimple\Container $container der Dependency Container
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function register(\Pimple\Container $container)
    {
        $container['studip-system-object'] = function () {
            return new \JsonApi\Models\Studip($container);
        };

        $container['studip-current-user'] = function () {
            if ($user = $GLOBALS['user']) {
                return $user->getAuthenticatedUser();
            }

            return null;
        };
    }
}
