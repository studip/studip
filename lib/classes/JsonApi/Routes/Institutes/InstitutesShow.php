<?php

namespace JsonApi\Routes\Institutes;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Zeigt eine bestimmte Einrichtung an.
 */
class InstitutesShow extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$institute = \Institute::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        return $this->getContentResponse($institute);
    }
}
