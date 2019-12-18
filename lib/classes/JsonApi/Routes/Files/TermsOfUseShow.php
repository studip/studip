<?php

namespace JsonApi\Routes\Files;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class TermsOfUseShow extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$terms = \ContentTermsOfUse::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowTermsOfUse($this->getUser($request), $terms)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($terms);
    }
}
