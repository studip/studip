<?php

/**
 * overview.php - contains RoomManagement_AdminController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2017
 * @category    Stud.IP
 * @since       4.1
 */


/**
 * RoomManagement_AdminController contains actions which are
 * useful for admin users.
 */
class RoomManagement_AdminController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!ResourceManager::userHasGlobalPermission(User::findCurrent(), 'admin')) {
            throw new AccessDeniedException();
        }
    }
}
