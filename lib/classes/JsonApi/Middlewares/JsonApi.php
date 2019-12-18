<?php

namespace JsonApi\Middlewares;

use JsonApi\Errors\JsonApiExceptionHandler;
use JsonApi\Providers\JsonApiConfig as JsonApiConfigProvider;
use JsonApi\Providers\JsonApiServices as JsonApiServiceProvider;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Diese Middleware sorgt dafür, dass alle von ihr versorgten
 * (JSON-API-)Routen auf die entsprechend benötigten JSON-API-Services
 * zugreifen können. Außerdem sorgt sie dafür, dass ein
 * JSON-API-spezifischer Exception-Handler registriert wird.
 */
class JsonApi
{
    /**
     * Der Konstruktor.
     *
     * @param \Slim\App     $app    die Slim-Applikation
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Hier wird der Dependency Container mit JSON-API-spezifischen
     * Services befüllt und ein JSON-API-spezifischer
     * Exception-Handler registriert.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  das
     *                                                           PSR-7 Request-Objekt
     * @param \Psr\Http\Message\ResponseInterface      $response das PSR-7
     *                                                           Response-Objekt
     * @param callable                                 $next     das nächste Middleware-Callable
     *
     * @return \Psr\Http\Message\ResponseInterface die neue Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $container = $this->app->getContainer();

        $container->register(new JsonApiConfigProvider());
        $container->register(new JsonApiServiceProvider());

        $this->registerExceptionHandler($container);

        // TODO: wie kriegt man das hier korrekt hin?
        $request->registerMediaTypeParser(
            'application/vnd.api+json',
            function ($input) {
                return json_decode($input, true);
            }
        );

        $response = $next($request, $response);

        return $response;
    }

    /**
     * Register exception handler.
     */
    private function registerExceptionHandler($container)
    {
        $previousHandler = null;
        if ($container['errorHandler']) {
            $previousHandler = $container['errorHandler'];
        }

        unset($container['errorHandler']);

        $container['errorHandler'] = function ($container) use ($previousHandler) {
            return new JsonApiExceptionHandler($container, $previousHandler);
        };
    }
}
