<?php
/**
 * NotificationsController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse <fuhse@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 */

require_once 'app/controllers/authenticated_controller.php';


class NotificationsController extends AuthenticatedController
{
    /**
     * Sets up the controller
     *
     * @param String $action Which action shall be invoked
     * @param Array $args Arguments passed to the action method
     */
    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        //PageLayout::setHelpKeyword('Basis.InteraktionWhosOnline');
        PageLayout::setTitle(_('Persönliche Benachrichtigungen'));
        Navigation::activateItem('/messaging');
        SkipLinks::addIndex(_('Wer ist online?'), 'layout_content', 100);
    }

    /**
     * Displays the online list.
     **/
    public function all_action()
    {
        $this->notifications = PersonalNotifications::getMyNotifications(false, $GLOBALS['user']->id);
    }
}
