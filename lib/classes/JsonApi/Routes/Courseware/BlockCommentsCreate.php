<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Block as BlockSchema;
use JsonApi\Schemas\Courseware\BlockComment as BlockCommentSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Create a comment on a block.
 */
class BlockCommentsCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $block = $this->getBlockFromJson($json);
        if (!Authority::canCreateBlockComment($user = $this->getUser($request), $block)) {
            throw new AuthorizationFailedException();
        }
        $blockComment = $this->createBlockComment($user, $json, $block);

        return $this->getCreatedResponse($blockComment);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (BlockCommentSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }
        if (self::arrayHas($json, 'data.id')) {
            return 'New document must not have an `id`.';
        }

        if (!self::arrayHas($json, 'data.attributes.comment')) {
            return 'Missing `comment` attribute.';
        }

        if (!self::arrayHas($json, 'data.relationships.block')) {
            return 'Missing `block` relationship.';
        }
        if (!$this->getBlockFromJson($json)) {
            return 'Invalid `block` relationship.';
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

    private function createBlockComment(\User $user, array $json, \Courseware\Block $block)
    {
        $blockComment = \Courseware\BlockComment::build([
            'block_id' => $block->id,
            'user_id' => $user->id,
            'comment' => self::arrayGet($json, 'data.attributes.comment', ''),
        ]);
        $blockComment->store();

        return $blockComment;
    }
}
