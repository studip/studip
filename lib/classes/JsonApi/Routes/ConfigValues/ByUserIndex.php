<?php

namespace JsonApi\Routes\ConfigValues;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ByUserIndex extends JsonApiController
{
    use HelperTrait;

    protected $allowedIncludePaths = [];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($range = \User::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        if (!Authority::canShowConfigValue($this->getUser($request), $range)) {
            throw new AuthorizationFailedException();
        }
        $configuration = $range->getConfiguration();

        return $this->getContentResponse(
            array_map(function ($field) use ($range) {
                return $this->findOrFakeConfigValue($range, $field);
            }, $configuration->getFields($range->getRangeType()))
        );
    }
}
