<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\NonJsonApiController;
use Courseware\Block;
use Courseware\Container;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Copy a courseware block in a courseware container
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */

class BlocksCopy extends NonJsonApiController
{
    public function __invoke(Request $request, Response $response, $args)
    {

        $data = $request->getParsedBody()['data'];

        $block = \Courseware\Block::find($data['block']['id']);
        $container = \Courseware\Container::find($data['parent_id']);

        if (!Authority::canCreateBlocks($user = $this->getUser($request), $container)) {
            throw new AuthorizationFailedException();
        }

        $new_block = $this->copyBlock($user, $block, $container);


        return $response->withJson($new_block);

    }

    private function copyBlock(\User $user, \Courseware\Block $remote_block, \Courseware\Container $container)
    {

        $block = $remote_block->copy($user, $container);

        $this->updateContainer($container, $block);

        return $block;
    }

    private function updateContainer(\Courseware\Container $container, \Courseware\Block $block)
    {
        //TODO update section block ids
        return true;
    }
}