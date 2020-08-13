<?php

/**
 * @author Nils Gehrke <nils.gehrke@uni-goettingen.de>
 */
class Feedback
{
    /**
     * Returns the html code for feedback elements for a given range, if the module is activated within a course
     *
     * @return string
     */
    public static function getHTML(string $range_id, string $range_type)
    {
        if (!$range_id) {
            return null;
        }
        $course_id = null;
        if (is_subclass_of($range_type, \FeedbackRange::class)) {
            $range_object = $range_type::find($range_id);
            if ($range_object) {
                $course_id = $range_object->getRangeCourseId();
            }
        }
        if ($course_id && Feedback::isActivated($course_id) && Feedback::hasRangeAccess($range_id, $range_type)) {
            return '<div class="feedback-elements" for="' . $range_id . '" type="' . $range_type . '" context="' . $course_id . '"></div>';
        } else {
            return null;
        }
    }
    /**
     * Returns activation status of the feedback module in currently active course
     *
     * @param string $course_id  optional; use this course_id instead of the current context
     *
     * @return boolean
     */
    public static function isActivated(string $course_id = null): bool
    {
        $course_id          = $course_id ?? Context::getId();
        $plugin_manager     = PluginManager::getInstance();
        $feedback_module    = $plugin_manager->getPluginInfo('FeedbackModule');

        return $plugin_manager->isPluginActivated($feedback_module['id'], $course_id) ?? false;
    }

    /**
     * Returns admin permission of current user within given course
     *
     * @param string $course_id  the course
     * @param string $user_id    optional; use this ID instead of $GLOBALS['user']->id
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function hasAdminPerm($course_id, string $user_id = null): bool
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        $admin_perm_level =  CourseConfig::get($course_id)->FEEDBACK_ADMIN_PERM;
        $admin_perm = $GLOBALS['perm']->have_studip_perm($admin_perm_level, $course_id, $user_id);

        return $admin_perm;
    }

    /**
     * Returns create permission of current user within given course
     *
     * @param string $course_id  the course
     * @param string $user_id    optional; use this ID instead of $GLOBALS['user']->id
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function hasCreatePerm($course_id, string $user_id = null): bool
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        $create_perm_level =  CourseConfig::get($course_id)->FEEDBACK_CREATE_PERM;
        $create_perm = $GLOBALS['perm']->have_studip_perm($create_perm_level, $course_id, $user_id);

        return $create_perm;
    }

    /**
     * Returns range access permission of current user for given range
     *
     * @param string $range_id
     * @param string $range_type
     * @param string $user_id    optional; use this ID instead of $GLOBALS['user']->id
     *
     * @return boolean
     */
    public static function hasRangeAccess($range_id, $range_type, string $user_id = null): bool
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        $range = $range_type::find($range_id);
        return $range->isRangeAccessible($user_id);
    }

}
