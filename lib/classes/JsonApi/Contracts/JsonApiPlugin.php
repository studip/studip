<?php

namespace JsonApi\Contracts;

/**
 * Stud.IP-Plugins, die dieses Interface implementieren, können
 * JSON-API-Routen zu Verfügung stellen.
 */
interface JsonApiPlugin
{
    /**
     * In dieser Methode können Plugins eigene autorisierte Routen
     * eintragen lassen.
     *
     * Dazu müssen am übergebenen \Slim\App-Objekt die Methoden
     * \Slim\App::get, \Slim\App::post, \Slim\App::put,
     * \Slim\App::delete oder \Slim\App::patch aufgerufen werden.
     *
     * Beispiel:
     *     class Blubber ... implements JsonApiPlugin
     *     {
     *         public function registerAuthenticatedRoutes(\Slim\App $app)
     *         {
     *             $app->get('/blubbers', BlubbersIndex::class);
     *         }
     *         [...]
     *     }
     *
     * @param \Slim\App $app die Slim-Applikation, in der das Plugin
     *                       Routen eintragen möchte
     */
    public function registerAuthenticatedRoutes(\Slim\App $app);

    /**
     * In dieser Methode können Plugins eigene Routen ohne Autorisierung
     * eintragen lassen.
     *
     * Dazu müssen am übergebenen \Slim\App-Objekt die Methoden
     * \Slim\App::get, \Slim\App::post, \Slim\App::put,
     * \Slim\App::delete oder \Slim\App::patch aufgerufen werden.
     *
     * Beispiel:
     *     class Blubber ... implements JsonApiPlugin
     *     {
     *         public function registerUnauthenticatedRoutes(\Slim\App $app)
     *         {
     *             $app->get('/blubbers', BlubbersIndex::class);
     *         }
     *         [...]
     *     }
     *
     * @param \Slim\App $app die Slim-Applikation, in der das Plugin
     *                       Routen eintragen möchte
     */
    public function registerUnauthenticatedRoutes(\Slim\App $app);

    /**
     * In dieser Methode können Plugins Schemata den verwendeten
     * Model-Klassen (also in der Regel SORM-Klassen) zuordnen.
     *
     * Wenn man in einer JSON-API-Route (als zum Beispiel einem
     * Unterklasse von \JsonApi\JsonApiController), Models
     * zurückgeben möchte, müssen für diese Models Schemata hinterlegt
     * worden sein.
     *
     * Beispiel:
     *     class Blubber ... implements JsonApiPlugin
     *     {
     *         public function registerSchema()
     *         {
     *             return [
     *                \BlubberPosting::class => \BlubberPostingSchema::class
     *             ];
     *         }
     *         [...]
     *     }
     *
     * @return array ein Array von Zuordnungen von Model-Klassennamen
     *               zu den entsprechenden Schema-Klassennamen
     */
    public function registerSchemas(): array;
}
