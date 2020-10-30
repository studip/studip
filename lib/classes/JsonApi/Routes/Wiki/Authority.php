<?php

namespace JsonApi\Routes\Wiki;

class Authority
{
    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canIndexWiki(\User $user, $range)
    {
        if (!($range instanceof \Course || $range instanceof \Institute)) {
            return false;
        }

        return $GLOBALS['perm']->have_studip_perm('user', $range->id, $user->id);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canShowWiki(\User $user, \WikiPage $wikiPage)
    {
        return $wikiPage->isVisibleTo($user);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canCreateWiki(\User $user, $range)
    {
        return \WikiPage::build(['range_id' => $range->id])->isCreatableBy($user);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canUpdateWiki(\User $user, \WikiPage $wikiPage)
    {
        return $wikiPage->isEditableBy($user);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canDeleteWiki(\User $user, \WikiPage $wikiPage)
    {
        return $GLOBALS['perm']->have_studip_perm('tutor', $wikiPage->range_id, $user->id);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canUpdateParent(\User $user, \WikiPage $wikiPage)
    {
        return self::canUpdateWiki($user, $wikiPage);
    }
}
