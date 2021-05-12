<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Block as BlockSchema;
use JsonApi\Schemas\Courseware\UserProgress as UserProgressSchema;
use JsonApi\Schemas\User as UserSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update a user progress.
 */
class UserProgressesUpdate extends JsonApiController
{
    use ValidationTrait, UserProgressesHelper;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $resource = $this->findWithId($args['id']);
        $json = $this->validate($request, $resource);
        if (!Authority::canUpdateUserProgress($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $userProgress = $this->updateUserProgress($json, $resource);

        return $this->getContentResponse($userProgress);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     * @SuppressWarnings(CyclomaticComplexity)
     * @SuppressWarnings(NPathComplexity)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (UserProgressSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }
        if (self::arrayGet($json, 'data.id') !== $data->id) {
            return 'Mismatch in document `id`.';
        }

        if (!($grade = self::arrayGet($json, 'data.attributes.grade'))) {
            return 'Missing `grade` attribute.';
        }
        if (!is_float($grade) && $grade != 1) {
            return 'Attribute `grade` must be a floating point number.';
        }
        if ($grade < 0 || $grade > 1) {
            return 'Attribute `grade` must be a floating point number between 0 and 1.';
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

    private function updateUserProgress(array $json, \Courseware\UserProgress $resource)
    {
        $resource->grade = self::arrayGet($json, 'data.attributes.grade', '');
        $resource->store();

        return $resource;
    }
}
