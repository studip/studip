<?php
# Lifter007: TEST

/**
 * ilias_auth.php - authenticate ILIAS user 
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Arne Schroeder <schroeder@data-quest.de>
 * @copyright   2018 Suchi & Berg GmbH <info@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class IliasAuthController extends StudipController
{

    /**
     * common tasks for all actions
     */
    public function before_filter (&$action, &$args)
    {
        parent::before_filter($action, $args);
    }

    /**
     * check authentication
     */
    public function authenticate_action()
    {
        $authenticated = false;
        $auth_status = StudipAuthAbstract::checkAuthentication(Request::get('login'), Request::get('password'));
        if ($auth_status['uid']) {
            $authenticated = true;
        }
        $query = "SELECT external_user_token_valid_until FROM auth_extern WHERE external_user_name = ? AND external_user_token = ?";
        $result = DBManager::get()->fetchOne($query, [Request::get('login'), Request::get('password')]);
        if (count($result)) {
            if ($result['external_user_token_valid_until'] > time()) {
                $authenticated = true;
            }
        }
        if ($authenticated) {
            $this->render_text('authenticated');
        } else {
            $this->render_nothing();
        }
    }
}
