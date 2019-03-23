<?php
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.4
 */

/**
 * Class ProfilePasswordNavigation to check visibility of navigation item from Lock-Rules and
 * auth-plugin.
 */
class ProfilePasswordNavigation
{
    /**
     * Checks if this navigation item to change the password should be visible.
     * @return bool
     */
    public function isVisible()
    {
        $current_user = User::findCurrent();
        return
            !StudipAuthAbstract::CheckField('auth_user_md5.password', $current_user->auth_plugin)
            && !LockRules::check($current_user->user_id, "password", "user");
    }
}