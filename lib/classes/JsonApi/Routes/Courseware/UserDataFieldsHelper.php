<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Block;
use Courseware\UserDataField;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

trait UserDataFieldsHelper
{
    private function findWithId(string $udfId)
    {
        list($userId, $blockId) = explode('_', $udfId);
        if (!($user = \User::find($userId)) || !($block = Block::find($blockId))) {
            throw new RecordNotFoundException();
        }
        return UserDataField::getUserDataField($user, $block);
    }
}
