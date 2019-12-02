<?php
/**
 * Settings_DomainsController - Administration of all user domains related
 * settings
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 */

require_once 'settings.php';

class Settings_UserdomainsController extends Settings_SettingsController
{
    /**
     * Set up this controller.
     *
     * @param String $action Name of the action to be invoked
     * @param Array $args    Arguments to be passed to the action method
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setHelpKeyword('Basis.HomepageNutzerdomänen');
        PageLayout::setTitle(_('Nutzerdomänen bearbeiten'));
        Navigation::activateItem('/profile/edit/userdomains');
        SkipLinks::addIndex(_('Zugeordnete Nutzerdomänen'), 'assigned_userdomains');
        SkipLinks::addIndex(_('Nutzerdomäne auswählen'), 'select_userdomains');

        Sidebar::get()->setImage('sidebar/admin-sidebar.png');
    }

    /**
     * Displays the user domain settings of a user.
     */
    public function index_action()
    {
        $this->allow_change = !StudipAuthAbstract::CheckField("userdomain_id", $this->user->auth_plugin)
                              && $GLOBALS['perm']->have_perm('admin');
        $this->user_domains = UserDomain::getUserDomainsForUser($this->user->user_id);
        $all_domains        = UserDomain::getUserDomains();
        $this->domains      = array_diff($all_domains, $this->user_domains);
    }

    /**
     * Stores the user domain settings of a user.
     */
    public function store_action()
    {
        $this->check_ticket();

        $any_change = false;

        $userdomain_delete = Request::getArray('userdomain_delete');
        if (count($userdomain_delete) > 0) {
            foreach ($userdomain_delete as $id) {
                $domain = UserDomain::find($id);
                $domain->removeUser($this->user->user_id);
            }

            $any_change = true;
        }

        $new_userdomain = Request::get('new_userdomain');
        if ($new_userdomain && $new_userdomain != 'none') {
            $domain = UserDomain::find($new_userdomain);
            $domain->addUser($this->user->user_id);

            $any_change = true;
        }

        if ($any_change) {
            PageLayout::postSuccess(_('Die Zuordnung zu Nutzerdomänen wurde geändert.'));

            setTempLanguage($this->user->user_id);
            $this->postPrivateMessage(_("Die Zuordnung zu Nutzerdomänen wurde geändert!\n"));
            restoreLanguage();
        }

        $this->redirect('settings/userdomains');
    }
}
