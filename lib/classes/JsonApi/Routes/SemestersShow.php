<?php

namespace JsonApi\Routes;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Displays a single semester.
 */
class SemestersShow extends JsonApiController
{
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$semester = \Semester::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        return $this->getContentResponse($semester);
    }
}
