<?php

namespace JsonApi\Routes\Courseware;

use Courseware\UserDataField;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\ConflictException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Block as BlockSchema;
use JsonApi\Schemas\Courseware\UserDataField as UserDataFieldSchema;
use JsonApi\Schemas\User as UserSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update a user data field.
 */
class UserDataFieldsUpdate extends JsonApiController
{
    use ValidationTrait, UserDataFieldsHelper;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $resource = $this->findWithId($args['id']);
        $json = $this->validate($request, $resource);
        if (!Authority::canUpdateUserDataField($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $userDataField = $this->updateUserDataField($json, $resource);

        return $this->getContentResponse($userDataField);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (UserDataFieldSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }
        if (self::arrayGet($json, 'data.id') !== $data->id) {
            return 'Mismatch in document `id`.';
        }

        if (!self::arrayHas($json, 'data.attributes.payload')) {
            return 'Missing `payload` attribute.';
        }

        if (self::arrayHas($json, 'data.relationships.user')) {
            if (!($user = $this->getUserFromJson($json))) {
                return 'Invalid `user` relationship.';
            }
            if ($user->id !== $data['user_id']) {
                return 'Mismatch in user relationship.';
            }
        }

        if (self::arrayHas($json, 'data.relationships.block')) {
            if (!($block = $this->getBlockFromJson($json))) {
                return 'Invalid `block` relationship.';
            }
            if ($block->id !== $data['block_id']) {
                return 'Mismatch in block relationship.';
            }
        }
    }

    private function getBlockFromJson($json)
    {
        if (!$this->validateResourceObject($json, 'data.relationships.block', BlockSchema::TYPE)) {
            return null;
        }
        $blockId = self::arrayGet($json, 'data.relationships.block.data.id');

        return \Courseware\Block::find($blockId);
    }

    private function getUserFromJson($json)
    {
        if (!$this->validateResourceObject($json, 'data.relationships.user', UserSchema::TYPE)) {
            return null;
        }
        $userId = self::arrayGet($json, 'data.relationships.user.data.id');

        return \User::find($userId);
    }

    private function updateUserDataField(array $json, \Courseware\UserDataField $resource)
    {
        $resource->payload = self::arrayGet($json, 'data.attributes.payload', '');
        $resource->store();

        return $resource;
    }
}
