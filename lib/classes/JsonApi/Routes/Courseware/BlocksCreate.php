<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Container;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Block as BlockSchema;
use JsonApi\Schemas\Courseware\Container as ContainerSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Create a block in a container.
 */
class BlocksCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $container = $this->getContainerFromJson($json);
        if (!Authority::canCreateBlocks($user = $this->getUser($request), $container)) {
            throw new AuthorizationFailedException();
        }
        $block = $this->createBlock($user, $json, $container);

        return $this->getCreatedResponse($block);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        if (BlockSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }

        if (self::arrayHas($json, 'data.id')) {
            return 'New document must not have an `id`.';
        }

        if (!self::arrayHas($json, 'data.attributes.block-type')) {
            return 'Missing `block-type` attribute.';
        }

        $blockType = self::arrayGet($json, 'data.attributes.block-type');
        if (!$this->validateBlockType($blockType)) {
            return 'Invalid `block-type` attribute.';
        }

        if (!self::arrayHas($json, 'data.relationships.container')) {
            return 'Missing `container` relationship.';
        }

        if (!$this->getContainerFromJson($json)) {
            return 'Invalid `container` relationship.';
        }
    }

    private function validateBlockType(string $blockType)
    {
        return \Courseware\BlockTypes\BlockType::isBlockType($blockType);
    }

    private function getContainerFromJson($json)
    {
        if (!$this->validateResourceObject($json, 'data.relationships.container', ContainerSchema::TYPE)) {
            return null;
        }

        $containerId = self::arrayGet($json, 'data.relationships.container.data.id');

        return \Courseware\Container::find($containerId);
    }

    private function createBlock(\User $user, array $json, \Courseware\Container $container)
    {
        $get = function ($key, $default = '') use ($json) {
            return self::arrayGet($json, $key, $default);
        };

        $block = \Courseware\Block::build([
            'container_id'    => $container->id,
            'owner_id'        => $user->id,
            'editor_id'       => $user->id,
            'edit_blocker_id' => $user->id,
            'position'        => $container->countBlocks(),
            'block_type'      => $get('data.attributes.block-type'),
            'payload'         => '',
            'visible'         => 1,
        ]);

        $payload = $get('data.attributes.payload');
        if ($payload) {
            if (!$block->type->validatePayload($payload)) {
                throw new UnprocessableEntityException('Invalid payload for this `block-type`.');
            }
            $block->type->setPayload($payload);
        }

        $block->store();

        return $block;
    }
}
