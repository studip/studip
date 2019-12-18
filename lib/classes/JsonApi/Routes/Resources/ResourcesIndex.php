<?php

namespace JsonApi\Routes\Resources;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Models\Resources\ResourcesObject;

class ResourcesIndex extends JsonApiController
{
    protected $allowedIncludePaths = ['category'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!Authority::canIndexResources($this->getUser($request))) {
            throw new AuthorizationFailedException();
        }

        $resources = ResourcesObject::findAll();

        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($resources, $offset, $limit),
            count($resources)
        );
    }
}
