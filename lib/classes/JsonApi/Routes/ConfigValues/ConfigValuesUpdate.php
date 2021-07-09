<?php

namespace JsonApi\Routes\ConfigValues;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\NotImplementedException;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\ConfigValue as ConfigValueSchema;
use JsonApi\JsonApiController;

class ConfigValuesUpdate extends JsonApiController
{
    use ValidationTrait;
    use HelperTrait;

    protected $allowedIncludePaths = [];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        [$rangeId, $field] = $this->parseId($args['id']);
        $range = $this->getRange($rangeId);
        if (!Authority::canEditConfigValue($this->getUser($request), $range)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->findOrFakeConfigValue($range, $field);

        // TODO: zunächst kann diese Route nur Konfigurationseinstellungen vom Typ bool ändern
        if ('boolean' !== $resource->entry['type'] && $resource->entry['field'] !== 'MY_COURSES_OPEN_GROUPS') {
            throw new NotImplementedException();
        }

        $json = $this->validate($request, $resource);
        $resource = $this->updateConfigValue($resource, $json);

        return $this->getContentResponse($resource);
    }

    protected function validateResourceDocument($json, $resource)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        if (ConfigValueSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }

        if (!self::arrayHas($json, 'data.id')) {
            return 'Document must have an `id`.';
        }

        if (self::arrayGet($json, 'data.id') !== $this->generateId($resource)) {
            return 'Mismatch between URI parameter and document `id`.';
        }

        if (!self::arrayHas($json, 'data.attributes.value')) {
            return 'The attribute `value` must exist.';
        }
    }

    private function updateConfigValue(\ConfigValue $resource, array $json): \ConfigValue
    {
        if (!($config = $resource->getConfigObject())) {
            throw new InvalidArgumentException('Invalid configuration object.');
        }
        $config->store($resource['field'], self::arrayGet($json, 'data.attributes.value'));

        return $resource;
    }
}
