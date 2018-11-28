<?php
/**
 * Settings_PrivacyController - Administration of all user privacy related
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

class Settings_PrivacyController extends Settings_SettingsController
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

        PageLayout::setHelpKeyword('Basis.MyStudIPPrivacy');
        PageLayout::setTitle(_('Privatsphäre'));

        Navigation::activateItem('/profile/settings/privacy');

        SkipLinks::addIndex(_('Privatsphäre'), 'layout_content', 100);
    }

    /**
     * Displays the privacy settings of a user.
     */
    public function index_action()
    {
        // Get visibility settings from database.
        $this->global_visibility = $this->user->visible;
        $this->online_visibility = get_local_visibility_by_id($this->user->user_id, 'online');
        $this->search_visibility = get_local_visibility_by_id($this->user->user_id, 'search');
        $this->email_visibility  = get_local_visibility_by_id($this->user->user_id, 'email');

        // Get default visibility for homepage elements.
        $this->default_homepage_visibility = Visibility::get_default_homepage_visibility($this->user->user_id);

        $this->NOT_HIDEABLE_FIELDS = $GLOBALS['NOT_HIDEABLE_FIELDS'];
        $this->user_perm           = $GLOBALS['perm']->get_perm($this->user->user_id);
        $this->user_domains        = UserDomain::getUserDomains();

        // Calculate colWidth and colCount for different visibilities
        $this->colCount          = Visibility::getColCount();
        $this->colWidth          = 67 / $this->colCount;
        $this->visibilities      = Visibility::getVisibilities();
        $this->homepage_elements = Visibility::getHTMLArgs($this->user->user_id);

    }

    /**
     * Stores the privacy settings concerning the appearance of a user inside
     * the system.
     */
    public function global_action()
    {
        CSRFProtection::verifySecurityToken();

        $visibility = Request::option('global_visibility');

        // Globally visible or unknown -> set local visibilities accordingly.
        if ($visibility != 'no') {
            $online             = Request::int('online') ?: 0;
            $search             = Request::int('search') ?: 0;
            $email              = Request::int('email') ?: 0;
            $foaf_show_identity = Request::int('foaf_show_identity') ?: 0;
            // Globally invisible -> set all local fields to invisible.
        } else {
            $online  = $search = $foaf_show_identity = 0;
            $email   = Config::get()->DOZENT_ALLOW_HIDE_EMAIL ? 0 : 1;
            $success = $this->changeCompleteHomepageVisibility(VISIBILITY_ME);
        }

        $this->config->store('FOAF_SHOW_IDENTITY', $foaf_show_identity);

        $this->user->visible = $visibility;
        $this->user->store();

        $query = "INSERT INTO user_visibility
                    (user_id, online, search, email, mkdate)
                  VALUES (?, ?, ?, ?, UNIX_TIMESTAMP())
                  ON DUPLICATE KEY
                    UPDATE online = VALUES(online),
                           search = VALUES(search),
                           email = VALUES(email)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $this->user->user_id,
            $online,
            $search,
            $email,
        ]);
        NotificationCenter::postNotification('UserVisibilityDidCreate', $GLOBALS['user']->id);

        PageLayout::postSuccess(_('Ihre Sichtbarkeitseinstellungen wurden gespeichert.'));
        $this->redirect('settings/privacy');
    }

    private function changeCompleteHomepageVisibility($new_visibility)
    {
        $result    = [];
        $new_data  = [];
        $db_result = [];
        // Retrieve homepage elements.
        $data = $this->user->getHomepageElements();
        // Iterate through data and set new visibility.
        foreach ($data as $key => $entry) {
            $new_data[$key] = [
                'name'       => $entry['name'],
                'visibility' => $new_visibility,
            ];
            if ($entry['extern']) {
                $new_data[$key]['extern'] = true;
            }
            $new_data[$key]['category'] = $entry['category'];

            $db_result[$key] = $new_visibility;
        }
        $success = $this->change_homepage_visibility($db_result);
        if ($success) {
            $result = $new_data;
        }
        return $result;
    }

    /**
     * Saves user specified visibility settings for homepage elements.
     *
     * @param array $data all homepage elements with their visiblities in
     *                    the form $name => $visibility
     * @return int Number of affected database rows (hopefully 1).
     */
    private function change_homepage_visibility($data)
    {
        $query = "INSERT INTO user_visibility
                    (user_id, homepage, mkdate)
                  VALUES (?, ?, UNIX_TIMESTAMP())
                  ON DUPLICATE KEY
                    UPDATE homepage = VALUES(homepage)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $this->user->id,
            json_encode($data),
        ]);
        return $statement->rowCount();
    }

    /**
     * Sets a default visibility for elements that are added to a user's
     * homepage but whose visibility hasn't been configured explicitly yet.
     *
     * @param int $visibility default visibility for new homepage elements
     * @return Number of affected database rows (hopefully 1).
     */
    private function set_default_homepage_visibility($visibility)
    {
        $query = "INSERT INTO user_visibility
                    (user_id, default_homepage_visibility, mkdate)
                  VALUES (?, ?, UNIX_TIMESTAMP())
                  ON DUPLICATE KEY
                    UPDATE default_homepage_visibility = VALUES(default_homepage_visibility)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $this->user->id,
            (int)$visibility,
        ]);
        return $statement->rowCount();
    }

    /**
     * Stores the privacy settings concerning the homepage / profile of a
     * user.
     */
    public function homepage_action()
    {
        CSRFProtection::verifySecurityToken();

        // If no bulk action is performed set all visibilitysettings seperately
        if (!$this->bulk()) {
            $data = Request::getArray('visibility_update');
            if (Visibility::updateUserFromRequest($data)) {
                PageLayout::postSuccess(_('Ihre Sichtbarkeitseinstellungen wurden gespeichert.'));
            } else {
                PageLayout::postError(_('Ihre Sichtbarkeitseinstellungen wurden nicht gespeichert!'));
            }
        }
        $this->redirect('settings/privacy');
    }

    /**
     * Performs bulk actions on the privacy settings of a user. This can be
     * either the setting of new default values or the changing of all privacy
     * values at once.
     *
     * @return boolean Returns <b>true</b> if all visibilities have been set
     */
    public function bulk()
    {
        if ($default_visibility = Request::int('default')) {
            $this->set_default_homepage_visibility($default_visibility);
        }

        if ($visibility = Request::int('all')) {
            if (Visibility::setAllSettingsForUser($visibility, $this->user->user_id)) {
                PageLayout::postSuccess(_('Die Sichtbarkeit der Profilelemente wurde gespeichert.'));
                return true;
            } else {
                PageLayout::postError(_('Die Sichtbarkeitseinstellungen der Profilelemente wurden nicht gespeichert!'));
            }
        }
        return false;
    }
}
