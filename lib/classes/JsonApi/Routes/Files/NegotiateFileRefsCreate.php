<?php

namespace JsonApi\Routes\Files;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class NegotiateFileRefsCreate
{
    /**
     * Der Konstruktor.
     *
     * @param ContainerInterface $container der Dependency Container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $contentType = $request->getHeaderLine('Content-Type');
        if ('multipart/form-data' === substr($contentType, 0, strlen('multipart/form-data'))) {
            $route = new FileRefsCreateByUpload($this->container);
        } else {
            $route = new FileRefsCreate($this->container);
        }

        return $route($request, $response, $args);
    }
}
