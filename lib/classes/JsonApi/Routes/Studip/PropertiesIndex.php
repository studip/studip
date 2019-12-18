<?php

namespace JsonApi\Routes\Studip;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\JsonApiController;

/**
 * List all properties.
 */
class PropertiesIndex extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $studip = $this->container['studip-system-object'];

        return $this->getContentResponse($studip->getProperties());
    }
}
