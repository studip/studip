<?php

namespace JsonApi\Routes\Courseware;

use Courseware\BlockComment;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Block as BlockSchema;
use JsonApi\Schemas\Courseware\BlockComment as BlockCommentSchema;
use JsonApi\Schemas\User as UserSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update a comment on a block
 */
class BlockCommentsUpdate extends JsonApiController
{
    use ValidationTrait, UserProgressesHelper;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = BlockComment::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        $json = $this->validate($request, $resource);
        if (!Authority::canUpdateBlockComment($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $blockComment = $this->updateBlockComment($json, $resource);

        return $this->getContentResponse($blockComment);
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
        if (BlockCommentSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }
        if (self::arrayGet($json, 'data.id') !== $data->id) {
            return 'Mismatch in document `id`.';
        }

        if (!($comment = self::arrayGet($json, 'data.attributes.comment'))) {
            return 'Missing `comment` attribute.';
        }
        if (!is_string($comment)) {
            return 'Attribute `comment` must be a string.';
        }
        if ($comment == '') {
            return 'Attribute `comment` must not be empty.';
        }

        if (self::arrayHas($json, 'data.relationships.user')) {
            if (!($user = $this->getUserFromJson($json))) {
                return 'Invalid `user` relationship.';
            }
            if ($user->id !== $data['user_id']) {
                return 'Cannot update `user` relationship.';
            }
        }

        if (self::arrayHas($json, 'data.relationships.block')) {
            if (!($block = $this->getBlockFromJson($json))) {
                return 'Invalid `block` relationship.';
            }
            if ($block->id !== $data['block_id']) {
                return 'Cannot update `block` relationship.';
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

    private function updateBlockComment(array $json, \Courseware\BlockComment $resource)
    {
        $resource->comment = self::arrayGet($json, 'data.attributes.comment', '');
        $resource->store();

        return $resource;
    }
}
