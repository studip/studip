<?php
/**
 * lti.php - LTI 1.1 single sign on controller
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class LtiController extends AuthenticatedController
{
    /**
     * Callback function being called before an action is executed.
     */
    public function before_filter(&$action, &$args)
    {
        // enforce LTI SSO login
        Request::set('sso', 'lti');

        parent::before_filter($action, $args);
    }

    /**
     * Redirect to enrollment action for the given course, if needed.
     */
    public function index_action($course_id = null)
    {
        $course_id = Request::option('custom_cid', $course_id);
        $course_id = Request::option('custom_course', $course_id);

        if ($course_id) {
            $this->redirect('course/enrolment/apply/' . $course_id);
        } else {
            $this->redirect('start');
        }
    }
}
