<?php
/**
 * This class observes changes in user data and updates ILIAS users
 *
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 4.3
 */
class IliasCourseObserver
{
    /**
     * Observe course updates.
     */
    public static function initialize()
    {
        NotificationCenter::addObserver(self::class, 'observeIliasCourse', 'CourseDidDelete');
    }

    /**
     * Remove course data for all ILIAS instances
     *
     * @param Course $course  the observed user
     */
    public static function observeIliasCourse($event, Course $course)
    {
        switch ($event) {
            case 'CourseDidDelete':
                foreach (Config::get()->ILIAS_INTERFACE_SETTINGS as $ilias_index => $ilias_config) {
                    if ($ilias_config['delete_ilias_courses']) {
                        $ilias = new ConnectedIlias($ilias_index);
                        $ilias->deleteCourse($course);
                    }
                }
                break;
        }
    }
}