<?php

namespace JsonApi\Routes\Courses;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\JsonApiController;

class SemTypesIndex extends JsonApiController
{
    protected $allowedIncludePaths = [
        'sem-class',
    ];
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        list($offset, $limit) = $this->getOffsetAndLimit();
        $semTypes = \SemType::getTypes();

        return $this->getPaginatedContentResponse(
            array_slice($semTypes, $offset, $limit),
            count($semTypes)
        );
    }
}
