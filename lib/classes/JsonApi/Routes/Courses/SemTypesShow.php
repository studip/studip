<?php

namespace JsonApi\Routes\Courses;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class SemTypesShow extends JsonApiController
{
    protected $allowedIncludePaths = [
        'sem-class',
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $semTypes = \SemType::getTypes();

        if (!isset($semTypes[$args['id']])) {
            throw new RecordNotFoundException();
        }

        return $this->getContentResponse($semTypes[$args['id']]);
    }
}
