<?php

namespace JsonApi;

use JsonApi\Errors\InternalServerError;
use JsonApi\Middlewares\Authentication;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface as SchemaContainerInterface;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * TODO.
 */
class NonJsonApiController
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        try {
            $response = $this->invoke($request, $response, $args);
        } catch (JsonApiException $exception) {
            $httpCode = $exception->getHttpCode();
            $errors = $exception->getErrors();
            $reason = count($errors) ? $errors[0]->getId() : '';

            $response = $response->withStatus($httpCode)->write($reason);
        }

        return $response;
    }

    public function invoke(Request $request, Response $response, $args)
    {
        throw new InternalServerError();
    }

    protected function getUser($request)
    {
        return $request->getAttribute(Authentication::USER_KEY, null);
    }

    protected function getSchema($resource)
    {
        return $this->container[SchemaContainerInterface::class]->getSchema($resource);
    }
}
