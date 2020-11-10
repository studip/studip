<?php

namespace JsonApi\Routes\Courses;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class SemClassesShow extends JsonApiController
{
    protected $allowedIncludePaths = [
        'sem-types',
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $semClasses = \SemClass::getClasses();

        if (!isset($semClasses[$args['id']])) {
            throw new RecordNotFoundException();
        }

        return $this->getContentResponse($semClasses[$args['id']]);
    }
}
