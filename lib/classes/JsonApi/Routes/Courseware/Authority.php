<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Block;
use Courseware\BlockComment;
use Courseware\BlockFeedback;
use Courseware\Container;
use Courseware\Instance;
use Courseware\StructuralElement;
use Courseware\UserDataField;
use Courseware\UserProgress;
use User;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Authority
{
    public static function canShowCoursewareInstance(User $user, Instance $resource)
    {
        return self::canShowStructuralElement($user, $resource->getRoot());
    }

    public static function canUpdateCoursewareInstance(User $user, Instance $resource)
    {
        return self::canUpdateStructuralElement($user, $resource->getRoot());
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canShowBlock(User $user, Block $resource)
    {
        if ($GLOBALS['perm']->have_perm('root')) {
            return true;
        }

        $struct = $resource->container->structural_element;

        if ('user' == $struct->range_type) {
            if ($user->id == $struct->range_id) {
                return true;
            } else {
                return false;
            }
        } elseif ($struct->range_type == 'course') {
            return $GLOBALS['perm']->have_studip_perm('user', $struct->course->id, $user->id) ||
                self::canUpdateStructuralElement($user, $struct) ||
                $struct->canRead($user);
        } else {
            return false; // should we throw an exeption here?
        }
    }

    public static function canIndexBlocks(User $user, Container $resource)
    {
        return self::canShowContainer($user, $resource);
    }

    public static function canCreateBlocks(User $user, Container $resource)
    {
        return self::canUpdateContainer($user, $resource);
    }

    public static function canUpdateBlock(User $user, Block $resource)
    {
        if ($resource->isBlocked()) {
            return $resource->getBlockerUserId() == $user->id;
        } else {
            return self::canUpdateContainer($user, $resource->container);
        }
    }

    public static function canDeleteBlock(User $user, Block $resource)
    {
        return self::canUpdateBlock($user, $resource);
    }

    public static function canUpdateEditBlocker(User $user, $resource)
    {
        return $resource->edit_blocker_id == '' || $resource->edit_blocker_id === $user->id;
    }

    public static function canShowContainer(User $user, Container $resource)
    {
        return self::canShowStructuralElement($user, $resource->getStructuralElement());
    }

    public static function canIndexContainers(User $user, StructuralElement $resource)
    {
        return self::canShowStructuralElement($user, $resource);
    }

    public static function canCreateContainer(User $user, StructuralElement $resource)
    {
        return self::canUpdateStructuralElement($user, $resource);
    }

    public static function canUpdateContainer(User $user, Container $resource)
    {
        return self::canUpdateStructuralElement($user, $resource->getStructuralElement());
    }

    public static function canDeleteContainer(User $user, Container $resource)
    {
        return self::canUpdateStructuralElement($user, $resource->getStructuralElement());
    }

    public static function canReorderBlocks(User $user, Container $resource)
    {
        return self::canUpdateContainer($user, $resource);
    }

    public static function canReorderContainers(User $user, StructuralElement $resource)
    {
        return self::canUpdateStructuralElement($user, $resource);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canShowStructuralElement(User $user, StructuralElement $resource)
    {
        if ($GLOBALS['perm']->have_perm('root')) {
            return true;
        }
        if ($resource->range_type == 'user') {
            if ($user->id == $resource->range_id) {
                return true;
            } else {
                return false;
            }
        } elseif ($resource->range_type == 'course') {
            return $GLOBALS['perm']->have_studip_perm('user', $resource->course->id, $user->id) ||
                self::canUpdateStructuralElement($user, $resource) ||
                $resource->canRead($user);
        } else {
            return false; // should we throw an exeption here?
        }
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canUpdateStructuralElement(User $user, StructuralElement $resource)
    {
        if ($GLOBALS['perm']->have_perm('root')) {
            return true;
        }

        $perm = false;

        if ($resource->user) {
            // check if user is owner of the courseware for this element
            $perm = $resource->user->id == $user->id;

            return $perm || $resource->canEdit($user);
        } elseif ($resource->course) {
            $perm = $GLOBALS['perm']->have_studip_perm(
                $resource->course->config->COURSEWARE_EDITING_PERMISSION,
                $resource->course->id,
                $user->id
            );

            return $perm || $resource->canEdit($user);
        }
    }

    public static function canCreateStructuralElement(User $user, StructuralElement $resource)
    {
        return self::canUpdateStructuralElement($user, $resource);
    }

    public static function canDeleteStructuralElement(User $user, StructuralElement $resource)
    {
        return self::canUpdateStructuralElement($user, $resource);
    }

    public static function canIndexBookmarks(User $user, Instance $resource)
    {
        return self::canShowCoursewareInstance($user, $resource);
    }

    public static function canUpdateBookmarks(User $user, Instance $resource)
    {
        return self::canShowCoursewareInstance($user, $resource);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canIndexStructuralElements(User $user)
    {
        return $GLOBALS['perm']->have_perm('root', $user->id);
    }

    public static function canReorderStructuralElements(User $user, $resource)
    {
        return self::canUpdateStructuralElement($user, $resource);
    }

    public static function canShowUserDataField(User $user, UserDataField $resource)
    {
        return  $user->id == $resource->user_id;;
    }

    public static function canUpdateUserDataField(User $user, UserDataField $resource)
    {
        return $user->id == $resource->user_id;
    }

    public static function canShowUserProgress(User $user, UserProgress $resource)
    {
        return $user->id == $resource->user_id;
    }

    public static function canUpdateUserProgress(User $user, UserProgress $resource)
    {
        return $user->id == $resource->user_id;
    }

    public static function canIndexBlockComments(User $user, Block $resource)
    {
        return self::canShowBlock($user, $resource);
    }

    public static function canShowBlockComment(User $user, BlockComment $resource)
    {
        return self::canShowBlock($user, $resource);
    }

    public static function canCreateBlockComment(User $user, Block $resource)
    {
        return self::canShowBlock($user, $resource);
    }

    public static function canUpdateBlockComment(User $user, BlockComment $resource)
    {
        return $user->id == $resource->user_id;
        // should dozent be able to update?
    }

    public static function canDeleteBlockComment(User $user, BlockComment $resource)
    {
        return self::canUpdateBlockComment($user, $resource);
    }

    public static function canIndexBlockFeedback(User $user, Block $resource)
    {
        return self::canUpdateStructuralElement($user, $resource->container->structural_element);
    }

    public static function canCreateBlockFeedback(User $user, Block $resource)
    {
        return self::canShowBlock($user, $resource);
    }

    public static function canShowBlockFeedback(User $user, BlockFeedback $resource)
    {
        return $resource->user_id === $user->id || self::canUpdateBlock($resource->block);
    }

    public static function canUploadStructuralElementsImage(User $user, StructuralElement $resource)
    {
        return self::canUpdateStructuralElement($user, $resource);
    }

    public static function canDeleteStructuralElementsImage(User $user, StructuralElement $resource)
    {
        return self::canUploadStructuralElementsImage($user, $resource);
    }
}
