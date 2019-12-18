<?php

namespace JsonApi\Routes\Resources;

use User;
use JsonApi\Models\Resources\ResourcesObject;

class Authority
{
    public static function canIndexResources(User $observer)
    {
        return $GLOBALS['perm']->have_perm('autor', $observer->id);
    }

    public static function canIndexAssignments(User $observer, ResourcesObject $resource)
    {
        return $GLOBALS['perm']->have_perm('autor', $observer->id);
    }
}
