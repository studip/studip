<?php

namespace JsonApi\Routes\ConfigValues;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConfigValuesShow extends JsonApiController
{
    use HelperTrait;

    protected $allowedIncludePaths = [];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        list($rangeId, $field) = $this->parseId($args['id']);
        $range = $this->getRange($rangeId);
        if (!Authority::canShowConfigValue($this->getUser($request), $range)) {
            throw new AuthorizationFailedException();
        }
        $configValue = $this->findOrFakeConfigValue($range, $field);

        return $this->getContentResponse($configValue);
    }
}
