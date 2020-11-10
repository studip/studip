<?php

namespace JsonApi\Routes\Institutes;

use User;

class Authority
{
    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canIndexInstitutesOfUser(User $observer, User $user)
    {
        return $GLOBALS['perm']->have_perm('admin', $observer->id)
            || $observer->id === $user->id;
    }
}
