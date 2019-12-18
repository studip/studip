<?php

namespace JsonApi\Routes;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\JsonApiController;

class DiscoveryIndex extends JsonApiController
{
    public function __invoke(Request $request, Response $response, $args)
    {
        $routes = $this->container->get('router')->getRoutes();

        return $this->getContentResponse($routes);
    }
}
