<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Block;
use Courseware\UserProgress;
use JsonApi\Errors\RecordNotFoundException;

trait UserProgressesHelper
{
    private function findWithId(string $userProgressId)
    {
        list($userId, $blockId) = explode('_', $userProgressId);
        if (!($user = \User::find($userId)) || !($block = Block::find($blockId))) {
            throw new RecordNotFoundException();
        }

        return UserProgress::getUserProgress($user, $block);
    }
}
