<?php

namespace JsonApi\Routes\ConfigValues;

use User;

class Authority
{
    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canShowConfigValue(User $user, ?\Range $range)
    {
        // Sonderfall: globale Range
        if (!$range) {
            return $GLOBALS['perm']->have_perm('root', $user->id);
        }

        return $range->isEditableByUser($user->id);
    }

    public static function canEditConfigValue(User $user, ?\Range $range)
    {
        return self::canShowConfigValue($user, $range);
    }
}
