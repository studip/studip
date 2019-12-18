<?php

namespace JsonApi\Routes;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\JsonApiController;

/**
 * List all the semesters.
 */
class SemestersIndex extends JsonApiController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    public function __invoke(Request $request, Response $response, $args)
    {
        list($offset, $limit) = $this->getOffsetAndLimit();
        $semesters = \Semester::getAll();

        return $this->getPaginatedContentResponse(
            array_slice($semesters, $offset, $limit),
            count($semesters)
        );
    }
}
