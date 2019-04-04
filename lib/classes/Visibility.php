<?php

/**
 * Visibility.php - VisibilityAPI
 *
 * The visibilityAPI provides all accessmethods to create visibilitysettings
 * that apear in the users setting menu. The verify method makes it possible to
 * check if the user wants another user to see something on his homepage
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class Visibility
{
    private static $verified;

    /**
     * Basic visibilitycheck to find out if a given user (or the current logged
     * on user) is allowed to view the object that is defined by a visibilityid
     *
     * @param int|string $visibilityid The visibility to check can be defined by
     * the direct id (int) or the predefined identifier (string)
     *
     * @param int $userid The user for whom we check the visibility. If no user
     * is given, the system will take the current logged on user
     *
     * @return boolean true if user is allowed to view content according to the
     * current visibilitysetting otherwise false;
     */
    public static function verify($visibilityid, $ownerid, $userid = null)
    {
        // check if visiting user is given
        self::getUser($userid);

        // root sees everything
        if ($GLOBALS['perm']->have_perm('root') || $ownerid === $userid) {
            return true;
        }

        if (isset(self::$verified[$visibilityid][$ownerid][$userid])) {
            return self::$verified[$visibilityid][$ownerid][$userid];
        }
        // load the possible visibilities
        $vs = VisibilitySettings::getInstance();

        // produce where clause
        $where = self::prepareWhere($visibilityid, $ownerid);

        // load the owner and the state of the visibility
        $sql = "SELECT `user_id`, state FROM `user_visibility_settings` $where";
        $db = DBManager::get();
        $stmt = $db->prepare($sql);
        $stmt->execute();

        // if we got a record we verify if the calling user is allowed to see what he wants to see
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return self::$verified[$visibilityid][$ownerid][$userid] = $vs->verify($userid, $ownerid, $result['state']);
        }

        // if db query fails something went wrong anyway so we use the default setting
        return self::$verified[$visibilityid][$ownerid][$userid] = $vs->verify($userid, $ownerid, constant(get_config('HOMEPAGE_VISIBILITY_DEFAULT')));
    }

    /**
     * Adds a privacysetting for a user. The user can change the setting in his
     * privacytab. To check back if a user is allowed to see any content use the
     * verify method
     *
     * @param string $name The setting's name, that will be displayed in the
     * user's settingtab (Important: Don't mix the name up with the identifier)
     *
     * @param string $identifier the identifier is used to simplify the usage
     * of the visibilityAPI. An identifier maps a string to a visibilityid
     * (under the usage of a userid) therefore all identifier set for one user
     * MUST be unique.
     *
     * @param int|string $parent Determines the parent of the visibility to add.
     * Use the direct visibilityid of the parent visibility or the identifier.
     * If the visibility should be created on the top level the value has to be
     * 0. Plugins creating a privacysetting will automaticly be added to the
     * parent "plugins". Important: If u add a visibility without as a parent
     * and as a category it wont be displayed until it has children that are
     * displayed.
     *
     * @param int $category Sets the type of the visibilitysetting. Currently
     * there are only 2 types available:
     * 0 - The setting is only a header without any options
     * 1 (Default) - Normal setting
     *
     * @param string $user Userid of the user that should be added the visibility.
     * Default: The current logged on user
     *
     * @param int $default int representation of the visibility that should be
     * set. Use with caution since the API provides the easy change of the
     * visibility int representation
     *
     * @param int $pluginid Connects the created visibility with a plugin.
     * Important: If addPrivacySetting is called in a file of a plugin there is
     * no need to set the pluginid manually, because the API will normally find
     * it
     *
     * @return int the created visibilityid
     */
    public static function addPrivacySetting($name, $identifier = "", $parent = 0, $category = 1, $user = null, $default = null, $pluginid = null)
    {
        $db = DBManager::get();

        // find out our default state
        if ($default == null) {
            $default = self::get_default_homepage_visibility($user);
        }

        // parse User and Identifier to format we want to have in the database
        self::getUser($user);
        $parent = self::parseIdentifier($parent, $user);

        // dont create duplicates
        if (self::exists($identifier, $user)) {
            return false;
        }

        // insert the new id and return the id
        $sql = "INSERT INTO user_visibility_settings (`user_id`, `parent_id`, `category`, `name`, `state`, `identifier`, `plugin`)
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($user, $parent, $category, $name, $default, $identifier, $pluginid));
        NotificationCenter::postNotification('UserVisibilitySettingDidCreate', $user, $identifier);
        return $db->lastInsertId();
    }

    /**
     * Adds a privacysetting for all users. If a parent is given, the function
     * will only add a privacySetting for those users that have an existing
     * parent
     *
     * @param string $name The setting's name, that will be displayed in the
     * user's settingtab (Important: Don't mix the name up with the identifier)
     *
     * @param string $identifier the identifier is used to simplify the usage
     * of the visibilityAPI. An identifier maps a string to a visibilityid
     * (under the usage of a userid) therefore all identifier set for one user
     * MUST be unique.
     *
     * @param int|string $parent Determines the parent of the visibility to add.
     * Use the direct visibilityid of the parent visibility or the identifier.
     * If the visibility should be created on the top level the value has to be
     * 0. Plugins creating a privacysetting will automaticly be added to the
     * parent "plugins". Important: If u add a visibility without a parent and
     * without beeing a toplevelpoint itself it will NEVER be displayed.
     *
     * @param int $category Sets the type of the visibilitysetting. Currently
     * there are only 2 types available:
     * 0 - The setting is only a header without any options
     * 1 (Default) - Normal setting
     *
     * @param string $user Userid of the user that should be added the visibility.
     * Default: The current logged on user
     *
     * @param int $default int representation of the visibility that should be
     * set. Use with caution since the API provides the easy change of the
     * visibility int representation
     *
     * @param int $pluginid Connects the created visibility with a plugin.
     * Important: If addPrivacySetting is called in a file of a plugin there is
     * no need to set the pluginid manually, because the API will normally find
     * it
     *
     * @return int the created visibilityid
     */
    public static function addPrivacySettingForAll($name, $identifier = "", $parent_identifier = 0, $category = 1, $default = null, $pluginid = null)
    {
        $db = DBManager::get();

        // load the users default out of the database if needed
        if ($default == null) {
            $default = "default_homepage_visibility as state ";
            $default_join = "JOIN `user_visibility` USING (`user_id`)";
        } else {
            $default = "'$default' as state";
            }

        // sure we could do this less complicated with php but with a single
        // query it's a lot faster
        $sql = "REPLACE into `user_visibility_settings`
        (`user_id`, `parent_id`, `identifier`, `category`, `name`, `state`, `plugin`)
            (SELECT user_id, visibilityid as parent_id,
            ? as identifier,
            ? as category,
            ? as name,
            $default,
            ? as plugin
            FROM `user_visibility_settings`
            $default_join
            WHERE `identifier`= ? );";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($identifier, $category, $name, $pluginid, $parent_identifier));
        NotificationCenter::postNotification('UserVisibilitySettingDidCreate', 'all', $identifier);
    }

    /**
     * Creates all nessesary categories for standard usage
     *
     * @param int $user Userid to define what user should create the categories
     * default: current logged on user;
     */
    public static function createDefaultCategories($user = null)
    {
        self::getUser($user);
        Visibility::addPrivacySetting(_("Allgemeine Daten"), "commondata", 0, 0, $user);
        Visibility::addPrivacySetting(_("Private Daten"), "privatedata", 0, 0, $user);
        Visibility::addPrivacySetting(_("Studien-/Einrichtungsdaten"), "studdata", 0, 0, $user);
        Visibility::addPrivacySetting(_("Zusätzliche Datenfelder"), "additionaldata", 0, 0, $user);
        Visibility::addPrivacySetting(_("Eigene Kategorien"), "owncategory", 0, 0, $user);
        // Visibility::addPrivacySetting(_("Plugins"), "plugins", 0, 0, $user);
        // self::createHomepagePluginEntries($user);
    }

    /**
     * Returns the description for a specific state set on a visibilityID
     *
     * @param int $visibilityid the visibility id
     *
     * @return string Description of the state or false if visibilityid was not found
     */
    public static function getStateDescription($visibilityid, $owner = null)
    {
        $visibilityid = self::parseIdentifier($visibilityid, $owner);
        $vs = VisibilitySettings::getInstance();
        $sql = "SELECT state FROM user_visibility_settings WHERE visibilityid = ?";
        $db = DBManager::get();
        $stmt = $db->prepare($sql);
        $stmt->execute(array($visibilityid));
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $vs->getDescription($result['state']);
        }
        return false;
    }

    /**
     * Updates a privacySetting. Important: The whole privacySetting gets
     * deleted and recreated. Therefore a new visibilityID is created.
     * If you use the privacyID you will have to update it as well.
     *
     * @param string $name The setting's name, that will be displayed in the
     * user's settingtab (Important: Don't mix the name up with the identifier)
     *
     * @param string $identifier the identifier is used to simplify the usage
     * of the visibilityAPI. An identifier maps a string to a visibilityid
     * (under the usage of a userid) therefore all identifier set for one user
     * MUST be unique.
     *
     * @param int|string $parent Determines the parent of the visibility to add.
     * Use the direct visibilityid of the parent visibility or the identifier.
     * If the visibility should be created on the top level the value has to be
     * 0. Plugins creating a privacysetting will automaticly be added to the
     * parent "plugins". Important: If u add a visibility without a parent and
     * without beeing a toplevelpoint itself it will NEVER be displayed.
     *
     * @param int $category Sets the type of the visibilitysetting. Currently
     * there are only 2 types available:
     * 0 - The setting is only a header without any options
     * 1 (Default) - Normal setting
     *
     * @param string $user Userid of the user that should be added the visibility.
     * Default: The current logged on user
     *
     * @param int $default int representation of the visibility that should be
     * set. Use with caution since the API provides the easy change of the
     * visibility int representation
     *
     * @param int $pluginid Connects the created visibility with a plugin.
     * Important: If addPrivacySetting is called in a file of a plugin there is
     * no need to set the pluginid manually, because the API will normally find
     * it
     *
     * @return int the created visibilityid
     */
    public static function updatePrivacySetting($name, $id = "", $parent = null, $category = 1, $user = null, $default = null, $pluginid = null)
    {
        $default = Visibility::removePrivacySetting($id);
        return Visibility::addPrivacySetting($name, $id, $parent, $category, $user, $default, $pluginid);
    }

    /**
     * Updates a privacySetting. Important: The whole privacySetting gets
     * deleted and recreated if the teststring is not empty. Therefore a new
     * visibilityID is created. If you use the privacyID you will have to update
     * it as well.
     *
     * @param string $test A teststring to determine if the privacySetting is
     * only deleted or if it is deleted and recreated. Use this with a request-
     * string for example.
     *
     * @param string $name The setting's name, that will be displayed in the
     * user's settingtab (Important: Don't mix the name up with the identifier)
     *
     * @param string $identifier the identifier is used to simplify the usage
     * of the visibilityAPI. An identifier maps a string to a visibilityid
     * (under the usage of a userid) therefore all identifier set for one user
     * MUST be unique.
     *
     * @param int|string $parent Determines the parent of the visibility to add.
     * Use the direct visibilityid of the parent visibility or the identifier.
     * If the visibility should be created on the top level the value has to be
     * 0. Plugins creating a privacysetting will automaticly be added to the
     * parent "plugins". Important: If u add a visibility without a parent and
     * without beeing a toplevelpoint itself it will NEVER be displayed.
     *
     * @param int $category Sets the type of the visibilitysetting. Currently
     * there are only 2 types available:
     * 0 - The setting is only a header without any options
     * 1 (Default) - Normal setting
     *
     * @param string $user Userid of the user that should be added the visibility.
     * Default: The current logged on user
     *
     * @param int $default int representation of the visibility that should be
     * set. Use with caution since the API provides the easy change of the
     * visibility int representation
     *
     * @param int $pluginid Connects the created visibility with a plugin.
     * Important: If addPrivacySetting is called in a file of a plugin there is
     * no need to set the pluginid manually, because the API will normally find
     * it
     *
     * @return int the created visibilityid
     */
    public static function updatePrivacySettingWithTest($test, $name, $id, $parent = null, $category = 1, $user = null, $default = null, $pluginid = null)
    {
        $default = Visibility::removePrivacySetting($id, $user);
        if ($test != "") {
            return Visibility::addPrivacySetting($name, $id, $parent, $category, $user, $default, $pluginid);
        }
        return false;
    }

    /**
     * Returns the visibilityID by an identifier and a user
     *
     * @param string $ident The identifier for the visibilityid
     *
     * @param string $user The userid. Default: Current logged on user
     *
     * @return int The visibilityID
     */
    public static function getPrivacyIdByIdentifier($ident, $user = null)
    {
        self::getUser($user);
        $sql = "SELECT `visibilityid` FROM user_visibility_settings WHERE `user_id` = ? AND `identifier` = ?";
        $db = DBManager::get();
        $stmt = $db->prepare($sql);
        $stmt->execute(array($user, $ident));
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $result['visibilityid'];
        }
        return false;
    }

    /**
     * Removes a privacysetting via ID or Identifier and user
     *
     * @param int|string $id ID or Identifier
     *
     * @param string $user Userid. Default: Current logged on user
     *
     * @return int state The state the deleted visibility had
     */
    public static function removePrivacySetting($id, $user = null)
    {
        $db = DBManager::get();
        $where = self::prepareWhere($id, $user);

        // select last state
        $sql = "SELECT state FROM user_visibility_settings $where";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $lastState = $stmt->fetch(PDO::FETCH_ASSOC);
        $lastState = $lastState['state'];

        // now delete the value
        $sql = "DELETE FROM user_visibility_settings $where";
        $db->prepare($sql)->execute();
        NotificationCenter::postNotification('UserVisibilitySettingDidDelete', $user, $id);
        return $lastState;
    }

    /**
     * Deletes ALL privacySettings for a Identifier (for ALL users!)
     *
     * @param string $ident The Identifier to be removed
     */
    public static function removeAllPrivacySettingForIdentifier($ident)
    {
        $db = DBManager::get();
        $sql = "DELETE FROM user_visibility_settings WHERE `identifier`= " . $db->quote($ident);
        NotificationCenter::postNotification('UserVisibilitySettingDidDelete', 'all', $ident);
        $db->exec($sql);
    }

    /**
     * Removes all PrivacySetting of the given pluginid
     *
     * @param int $id the pluginid
     */
    public static function removePlugin($id)
    {
        $db = DBManager::get();
        $sql = "DELETE FROM user_visibility_settings WHERE `plugin`= " . $db->quote($id);
    }

    /**
     * Renames a PrivacySetting
     *
     * @param int|string $id VisibilityID or Identifier to be renamed
     *
     * @param string $new_name the new name
     *
     * @param string $user Userid. Default: Current logged on user
     */
    public static function renamePrivacySetting($id, $new_name, $user = null)
    {
        $setting = User_Visibility_Settings::find($id, $user);
        if ($setting) {
            $setting->name = $new_name;
            $setting->store();
        }
    }

    /**
     * Removes all PrivacySettings from a user
     *
     * @param string $user_id the given userid
     */
    public static function removeUserPrivacySettings($user_id)
    {
        $db = DBManager::get();
        $sql = "DELETE FROM user_visibility_settings WHERE `user_id`= " . $db->quote($user_id);
        $db->exec($sql);
    }

    /**
     * Builds up the htmlArgs for display in Settings
     *
     * @param string $userid the userid
     *
     * @return array tree of htmlArgs
     */
    public static function getSettingsArgs($userid)
    {
        $userPrivacy = new UserPrivacy($userid);
        return $userPrivacy->getHTMLArgs();
    }

    /**
     * Takes the updaterequestdata and stores it into the database
     *
     * @param array $data Data produced by the settingspage
     *
     * @param string $userid the userid. Default: current logged on user
     */
    public static function updateUserFromRequest($data, $userid = null)
    {
        self::getUser($userid);
        try {
            $userPrivacy = new UserPrivacy($userid);
            $userPrivacy->updateAllFromRequest($data);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Helpfunction to produce a where clause for sql statements by the type
     * given
     *
     * @param int|string $id ID or identifier.
     *
     * @param type $userid Userid. Default: Current logged on user
     *
     * @return string Where clause to use in a sql statement
     */
    private static function prepareWhere($id, $userid = null)
    {
        $db = DBManager::get();
        if (is_int($id)) {
            return "WHERE `visibilityid`= " . $db->quote($id);
        } else {
            self::getUser($userid);
            return "WHERE `user_id` = " . $db->quote($userid) . " AND `identifier` = " . $db->quote($id);
        }
    }

    /**
     * Helpfunction to set default user
     *
     * @param type $user Userid input.
     */
    private static function getUser(&$user)
    {
        if ($user == null) {
            $user = $GLOBALS['user']->user_id;
        }
    }

    /**
     * Helpfunction to calculate how many columns the settingstable needs
     *
     * @return int Number of columns
     */
    public static function getColCount()
    {
        return VisibilitySettings::getInstance()->count();
    }

    /**
     * Helpfunction to get all names for the settingstable
     *
     * @return array all visibility names
     */
    public static function getVisibilities()
    {
        return VisibilitySettings::getInstance()->getAllNames();
    }

    /**
     * Helpfunction to get the data for the settingstable
     *
     * @return array tree of settingelements
     */
    public static function getHTMLArgs($userid = null)
    {
        $ps = new UserPrivacy($userid);
        return $ps->getHTMLArgs();
    }

    /**
     * Helpfunction to determine the visibilityID
     *
     * @param int|string $id ID or identifier.
     *
     * @param string $user The user. Default: Current logged on user
     *
     * @return id The visibilityID
     */
    private static function parseIdentifier($id, $user)
    {
        if (is_int($id)) {
            return $id;
        } else {
            return self::getPrivacyIdByIdentifier($id, $user);
        }
    }

    /**
     * Sets all visibilites of a user to a specific state. This function should
     * ONLY be called from the settingspage or some administration page NOT from
     * a plugin
     *
     * @param type $state The int representation of the state everything should
     * be set to
     *
     * @param type $user The user. Default: current logged on user;
     */
    public static function setAllSettingsForUser($state, $user = null)
    {
        try {
            self::getUser($user);
            $sql = "UPDATE user_visibility_settings SET `state` = ? WHERE `user_id`= ?";
            $db = DBManager::get();
            $st = $db->prepare($sql);
            $st->execute(array($state, $user));
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Retrieves the standard visibility level for a homepage element if the user
     * hasn't specified anything explicitly. This default can be set via the global
     * configuration (variable "HOMEPAGE_VISIBILITY_DEFAULT").
     *
     * @return int Default visibility level.
     */
    public static function get_default_homepage_visibility($user_id = null)
    {
        self::getUser($user_id);
        $query = "SELECT default_homepage_visibility FROM user_visibility WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_id]);

        /*
         * If we have no default visibility set by the user itself we load the
         * global config set in configuration. Unfortunatelly we have to map
         * this throughout the constants. Oneday this should be eliminated
         * by a migration.
         */
        if (!$visibility = $statement->fetchColumn()) {
            $def = Config::get()->HOMEPAGE_VISIBILITY_DEFAULT;
            $consts = get_defined_constants();
            $visibility = $consts[$def];
        }
        return $visibility;
    }

    /**
     * Creates a visibilitySetting for every homepagePlugin
     *
     * @param type $user the user for whom the settings should be created
     */
    private function createHomepagePluginEntries($user)
    {
        self::getUser($user);
        $homepageplugins = PluginEngine::getPlugins('HomepagePlugin');
        foreach ($homepageplugins as $plugin) {
            self::addPrivacySetting($plugin->getPluginName(), ("plugin".$plugin->getPluginId()), 'plugins', 1, $user, null, $plugin->getPluginId());
        }
    }

    /**
     * Checks if a specific visibilityID or an identifier & userid combination
     * exists
     *
     * @param type $id id or identifier
     *
     * @param type $user user id
     *
     * @return type true if the id exists. false if not
     */
    public static function exists($id, $user = null)
    {
        $where = self::prepareWhere($id, $user);
        $sql = "SELECT user_id FROM user_visibility_settings $where";
        $db = DBManager::get();
        $stmt = $db->prepare($sql);
        $stmt->execute();
        return $stmt->rowCount();
    }
}
?>
