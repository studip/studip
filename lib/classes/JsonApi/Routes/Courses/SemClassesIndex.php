<?php

namespace JsonApi\Routes\Courses;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\JsonApiController;

class SemClassesIndex extends JsonApiController
{
    protected $allowedIncludePaths = [
        'sem-types',
    ];
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        list($offset, $limit) = $this->getOffsetAndLimit();
        $semClasses = \SemClass::getClasses();

        return $this->getPaginatedContentResponse(
            array_slice($semClasses, $offset, $limit),
            count($semClasses)
        );
    }
}
