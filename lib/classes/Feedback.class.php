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
    public static function getHTML($range_id, $range_type)
    {
        if (Feedback::isActivated() && Feedback::hasRangeAccess($range_id, $range_type)) {
            return '<div class="feedback-elements" for="' . $range_id . '" type="' . $range_type . '"></div>';
        } else {
            return null;
        }
    }
    /**
     * Returns activation status of the feedback module in currently active course
     *
     * @return boolean
     */
    public static function isActivated()
    {
        $course_id          = Context::getId();
        $plugin_manager     = PluginManager::getInstance();
        $feedback_module    = $plugin_manager->getPluginInfo('FeedbackModule');

        return $plugin_manager->isPluginActivated($feedback_module['id'], $course_id);
    }

    /**
     * Returns admin permission of current user within given course
     *
     * @return boolean
     */
    public static function hasAdminPerm($course_id)
    {
        $admin_perm_level =  CourseConfig::get($course_id)->FEEDBACK_ADMIN_PERM;
        $admin_perm = $GLOBALS['perm']->have_studip_perm($admin_perm_level, $course_id);

        return $admin_perm;
    }

    /**
     * Returns create permission of current user within given course
     *
     * @return boolean
     */
    public static function hasCreatePerm($course_id)
    {
        $create_perm_level =  CourseConfig::get($course_id)->FEEDBACK_CREATE_PERM;
        $create_perm = $GLOBALS['perm']->have_studip_perm($create_perm_level, $course_id);

        return $create_perm;
    }

    /**
     * Returns range access permission of current user for given range
     *
     * @return boolean
     */
    public static function hasRangeAccess($range_id, $range_type)
    {
        $range = $range_type::find($range_id);
        return $range->isRangeAccessible();
    }

}
