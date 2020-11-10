<?php

namespace JsonApi\Routes\Courses;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class SemTypesBySemClassIndex extends JsonApiController
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
        $semClasses = \SemClass::getClasses();
        if (!isset($semClasses[$args['id']])) {
            throw new RecordNotFoundException();
        }
        $semClass = $semClasses[$args['id']];

        list($offset, $limit) = $this->getOffsetAndLimit();
        $semTypes = $semClass->getSemTypes();

        return $this->getPaginatedContentResponse(
            array_slice($semTypes, $offset, $limit),
            count($semTypes)
        );
    }
}
