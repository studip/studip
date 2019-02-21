<?php
/**
 * change_view.php - contains Course_ChangeViewController
 *
 * This controller realises a redirector for administrative pages
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.2
 */
class Course_ChangeViewController extends AuthenticatedController
{
    // see Trails_Controller#before_filter
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->course_id = Course::findCurrent()->id;
    }

    /**
     * Sets the current course into participant view.
     * Only available for tutor upwards.
     *
     * @throws Trails_Exception Someone with unfitting rights tried to call here.
     */
    public function set_changed_view_action()
    {
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->course_id)) {
            throw new Trails_Exception(400);
        }
        $_SESSION["seminar_change_view_{$this->course_id}"] = 'autor';
        $this->relocate('course/overview');
    }

    /**
     * Resets a course currently in participant view to normal view
     * with real rights.
     *
     * @throws Trails_Exception Someone with unfitting rights tried to call here.
     */
    public function reset_changed_view_action()
    {
        /*
         * We need to check the real database entry here because $perm would
         * only return the simulated rights.
         */
        if (!CourseMember::findByCourseAndStatus($this->course_id, ['tutor', 'dozent'])) {
            throw new Trails_Exception(400);
        }
        unset($_SESSION["seminar_change_view_{$this->course_id}"]);
        $this->relocate('course/management');
    }
}
