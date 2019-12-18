<?php

namespace JsonApi\Routes\Institutes;

use User;

class Authority
{
    public static function canIndexInstitutesOfUser(User $observer, User $user)
    {
        return $observer->id === $user->id;
    }
}
