<?php

namespace JsonApi\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * TODO.
 */
class DangerousRouteHandler
{

    /**
     * TODO.
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
        if (\Config::get()->getValue('JSONAPI_DANGEROUS_ROUTES_ALLOWED')) {
            return $next($request, $response);
        }

        return $response->withStatus(503)->write('Service Unavailable.');
    }
}
