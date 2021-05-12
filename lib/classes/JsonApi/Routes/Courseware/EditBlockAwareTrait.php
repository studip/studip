<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\Errors\ConflictException;

trait EditBlockAwareTrait
{
    private function updateLockedResource(\User $user, \SimpleORMap $resource, callable $callback)
    {
        $dbm = \DBManager::get();
        $dbm->beginTransaction();

        if (!$resource::findOneBySql('id = ? AND edit_blocker_id = ?', [$resource->id, $user->id])) {
            $dbm->rollBack();
            throw new ConflictException();
        }

        $resource = $callback($user, $resource);

        $dbm->commit();

        return $resource;
    }

    private function deleteResource(\User $user, \SimpleORMap $resource)
    {
        $dbm = \DBManager::get();
        $dbm->beginTransaction();

        // check edit lock
        if (!$resource::findOneBySql('id = ? AND edit_blocker_id = ?', [$resource->id, $user->id])) {
            $dbm->rollBack();
            throw new ConflictException();
        }

        // now delete
        $resource->delete();
        $dbm->commit();
    }
}
