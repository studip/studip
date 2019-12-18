<?php

namespace JsonApi\Providers;

/**
 * Diese Klasse konfiguriert die JSON-API in der Slim-Applikation um
 * die Zuordnung von Schemata zu Stud.IP-Model-Klassen und setzt das
 * URL-Prefix für die interne Generierung von URIs.
 */
class JsonApiConfig implements \Pimple\ServiceProviderInterface
{
    /** Config key for schema list */
    const SCHEMAS = 'json-api-integration-schemas';

    /** Config key for URL prefix that will be added to all document links which have $treatAsHref flag set to false */
    const JSON_URL_PREFIX = 'json-api-integration-urlPrefix';

    /**
     * Diese Methode wird automatisch aufgerufen, wenn diese Klasse dem
     * Dependency Container der Slim-Applikation hinzugefügt wird.
     *
     * Hier werden die Schema-Abbildungen und das JSON-API-URL-Präfix gesetzt.
     *
     * @param \Pimple\Container $container der Dependency Container
     */
    public function register(\Pimple\Container $container)
    {
        $container[self::SCHEMAS] = new \JsonApi\SchemaMap();
        $container[self::JSON_URL_PREFIX] = rtrim(\URLHelper::getUrl('jsonapi.php/v1'), '/');
    }
}
