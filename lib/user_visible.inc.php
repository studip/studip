<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
/*
user_visible.inc.php - Functions for determining a users visibility
Copyright (C) 2004 Till Glöggler <virtuos@snowysoft.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

// Define constants for visibility states.
define('VISIBILITY_ME', 1);
define('VISIBILITY_BUDDIES', 2);
define('VISIBILITY_DOMAIN', 3);
define('VISIBILITY_STUDIP', 4);
define('VISIBILITY_EXTERN', 5);

/*
 * A function to determine a users visibility
 *
 * @param   $user_id    user-id
 * @returns boolean true: user is visible, false: user is not visible
 */
function get_visibility_by_id ($user_id)
{
    if ($GLOBALS['perm']->have_perm('root')) {
        return true;
    }

    $query = "SELECT visible FROM auth_user_md5 WHERE user_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$user_id]);
    $visible = $statement->fetchColumn();

    return get_visibility_by_state($visible, $user_id);
}

/*
 * A function to determine a users visibility
 *
 * @param   $username   username
 * @returns boolean true: user is visible, false: user is not visible
 */
function get_visibility_by_username($username)
{
    if ($GLOBALS['perm']->have_perm('root')) {
        return true;
    }

    $query = "SELECT visible, user_id FROM auth_user_md5 WHERE username = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$username]);
    $temp = $statement->fetch(PDO::FETCH_ASSOC);

    return get_visibility_by_state($temp['visible'], $temp['user_id']);
}

/*
 * A function to determine, whether a given state means 'visible' or 'invisible'
 *
 * @param   $stat   ['global', 'always', 'yes', 'unknown', 'no', 'never']
 * @param   $user_id        id of user that should be checked
 * @returns boolean true: state means 'visible', false: state means 'invisible'
 */
function get_visibility_by_state ($state, $user_id) {
    // Globally visible, no need for futher checks.
    if ($state === 'global') {
        return true;
    }

    $same_domain = UserDomain::checkUserVisibility(
        User::find($user_id)->domains,
        User::find($GLOBALS['user']->id)->domains
    );

    switch ($state) {
        case 'yes':
        case 'always':
            return $same_domain;
        case 'unknown':
            return $same_domain && Config::get()->USER_VISIBILITY_UNKNOWN;
    }

    return false;
}

/*
 * This function returns a query-snip for selecting with current visibility rights
 * @returns string  returns a query string
 */
function get_vis_query($table_alias = 'auth_user_md5', $context = '') {
    global $auth, $perm;

    if ($GLOBALS['perm']->have_perm('root')) {
        return '1';
    }

    $query = "{$table_alias}.visible = 'global'";

    /*
     *  Check if the user has set own visibilities or if the system default
     *  should be used.
     */
    if ($context) {
        $context_default = (int) get_config(mb_strtoupper($context) . '_VISIBILITY_DEFAULT');
        $contextQuery = " AND (IFNULL(user_visibility.{$context}, {$context_default}) = 1
                               OR {$table_alias}.visible = 'always')";
    }

    $my_domains = UserDomain::getUserDomainsForUser($GLOBALS['user']->id);
    $restricted = count($my_domains) > 0;

    $my_domain_ids = [];
    foreach ($my_domains as $domain) {
        if (!$domain->restricted_access) {
            $restricted = false;
        } else {
            $my_domain_ids[] = $domain->id;
        }
    }

    if (!$restricted) {
        $query .= " OR NOT EXISTS (
                     SELECT *
                     FROM user_userdomains
                     JOIN userdomains USING (userdomain_id)
                     WHERE user_id = {$table_alias}.user_id
                  ) OR EXISTS (
                      SELECT *
                      FROM user_userdomains
                      JOIN userdomains USING (userdomain_id)
                      WHERE user_id = {$table_alias}.user_id
                      AND restricted_access = 0
                  )";
    }
    if (count($my_domain_ids) > 0) {
        $query .= " OR EXISTS (
                     SELECT *
                     FROM user_userdomains
                     WHERE user_id = {$table_alias}.user_id
                       AND userdomain_id IN (" . DBManager::get()->quote($my_domain_ids) . ")
                   )";
    }


    $allowed = ['always', 'yes'];
    if (Config::get()->USER_VISIBILITY_UNKNOWN) {
        // users with visibility "unknown" are treated as visible
        $allowed[] = 'unknown';
    }
    $quoted = DBManager::get()->quote($allowed);
    $query .= " AND {$table_alias}.visible IN ({$quoted})";

    return "($query) $contextQuery";
}

function get_ext_vis_query($table_alias = 'aum') {
    $allowed = ['global', 'always', 'yes'];
    if (Config::get()->USER_VISIBILITY_UNKNOWN) {
        $allowed[] = 'unknown';
    }

    $quoted = DBManager::get()->quote($allowed);
    return "({$table_alias}.visible IN ({$quoted}))";
}

/*
 * A function to create a chooser for a users visibility
 *
 * @param   $vis    visibility-state
 * @returns string  gives back a string with the chooser
 */
function vis_chooser($vis, $new = false, $id = false) {
    if ($vis == '') {
        $vis = 'unknown';
    }
    $txt = [];
    $txt[] = sprintf('<select name="visible"%s>', $id ? 'id="' . htmlReady($id) . '"' : '');
    $txt[] = '<option value="global"'.($vis === "global" ? " selected" : "").'>'._("global").'</option>';
    $txt[] = '<option value="always"'.($vis === "always" ? " selected" : "").'>'._("immer").'</option>';
    $txt[] = '<option value="yes"'.($vis === "yes" ? " selected" : "").'>'._("ja").'</option>';
    $txt[] = '<option value="unknown"'.(($new || $vis === "unknown") ? ' selected="selected"':'').'>'._("unbekannt").'</option>';
    $txt[] = '<option value="no"'.($vis === "no" ? " selected" : "").'>'._("nein").'</option>';
    $txt[] = '<option value="never"'.($vis === "never" ? " selected" : "").'>'._("niemals").'</option>';
    $txt[] = '</select>';
    return implode("\n", $txt);
}

// Ask user with unknown visibility state directly after login
// whether they want to be visible or invisible
//
// ATTENTION: NOT USED IN STANDARD DISTRIBUTION.
// see header.php for further info on enabling this feature.
//
// DON'T USE UNMODIFIED TEXTS!
//
function first_decision($userid) {
    $vis_cmd = Request::option('vis_cmd');
    $vis_state = Request::option('vis_state');
    $user_language = getUserLanguagePath($userid);

    if ($vis_cmd == "apply" && ($vis_state == "global" || $vis_state == "yes" || $vis_state == "no")) {
        $query = "UPDATE auth_user_md5 SET visible = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$vis_state, $userid]);
        return;
    }

    $query = "SELECT visible FROM auth_user_md5 WHERE user_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$userid]);
    $visiblity = $statement->fetchColumn();

    if ($visiblity != 'unknown') {
        return;
    }

    PageLayout::setTitle(_('Bitte wählen Sie Ihren Sichtbarkeitsstatus aus!'));
    PageLayout::setTabNavigation(NULL);

    // avoid recursion when loading the header
    Config::get()->USER_VISIBILITY_CHECK = false;

    $template = $GLOBALS['template_factory']->open("../locale/$user_language/LC_HELP/visibility_decision.php");
    $template->set_layout('layouts/base.php');

    echo $template->render();
    page_close();
    die;
}


/**
 * Gets a user's visibility settings for special context. Valid contexts are
 * at the moment:
 * <ul>
 * <li><b>online</b>: Visibility in "Who is online" list</li>
 * <li><b>search</b>: Can the user be found via person search?</li>
 * <li><b>email</b>: Is user's email address shown?</li>
 * <li><b>homepage</b>: Visibility of all user homepage elements, stored as
 * JSON-serialized array</li>
 * </ul>
 *
 * @param string $user_id user ID to check
 * @param string $context local visibility in which context?
 * @param boolean $return_user_perm return not only visibility, but also
 * the user's global permission level
 * @return mixed Visibility flag or array with visibility and user permission
 * level.
 */
function get_local_visibility_by_id($user_id, $context, $return_user_perm=false) {
    global $NOT_HIDEABLE_FIELDS;

    $query = "SELECT a.perms, u.`{$context}`
              FROM auth_user_md5 AS a
              LEFT JOIN user_visibility AS u USING (user_id)
              WHERE user_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$user_id]);
    $data = $statement->fetch(PDO::FETCH_ASSOC);

    if ($context === 'homepage') {
        $settings = User_Visibility_Settings::findByUser_id($user_id);
        foreach ($settings as $setting) {
            if ($setting['category'] == 1) {
                $homepage_settings[$setting['identifier']] = $setting['state'];
            }
        }

        if ($homepage_settings) {
            $data[$context] = json_encode($homepage_settings);
        }
    }

    if ($data[$context] === null) {
        $user_perm = $data['perm'];
        $data['perms'] = $user_perm;

        $data[$context] = get_config(mb_strtoupper($context) . '_VISIBILITY_DEFAULT');
    }
    // Valid context given.
    if (isset($data[$context])) {
        // Context may not be hidden per global config setting.
        if ($NOT_HIDEABLE_FIELDS[$data['perms']][$context]) {
            $result = true;
        } else {
            // Give also user's permission level.
            if ($return_user_perm) {
                $result = [
                    'perms' => $data['perms'],
                    $context => $data[$context]
                ];
            } else {
                $result = $data[$context];
            }
        }
    } else {
        $result = false;
    }
    return $result;
}


/**
 * Checks whether an element of a user homepage is visible for another user.
 * We do not give an element name and look up its visibility setting in the
 * database, because that would generate many database requests for a single
 * user homepage. Instead, the homepage itself loads all element visibilities
 * and we only need to check if the given element visibility allows showing it
 * to the visiting user. We need not check for not hideable fields here,
 * because that is already done when loading the element visibilities.
 *
 * @param string $user_id ID of the user who wants to see the element
 * @param string $owner_id ID of the homepage owner
 * @param int $element_visibility visibility level of the element, one of
 * the constants VISIBILITY_ME, VISIBILITY_BUDDIES, VISIBILITY_DOMAIN,
 * VISIBILITY_STUDIP, VISIBILITY_EXTERN
 * @return boolean Is the element visible?
 */
function is_element_visible_for_user($user_id, $owner_id, $element_visibility) {
    $is_visible = false;
    if ($user_id == $owner_id) {
        $is_visible = true;
    // Deputies with homepage editing rights see the same as the owner
    } else if (get_config('DEPUTIES_ENABLE') && get_config('DEPUTIES_DEFAULTENTRY_ENABLE') && get_config('DEPUTIES_EDIT_ABOUT_ENABLE') && isDeputy($user_id, $owner_id, true)) {
        $is_visible = true;
    } else {
        // No element visibility given (user has not configured this element yet)
        // Set default visibility as element visibility
        if (!$element_visibility) {
            $element_visibility = get_default_homepage_visibility($owner_id);
        }
        // Check if the given element is visible according to its visibility.
        switch ($element_visibility) {
            case VISIBILITY_EXTERN:
                $is_visible = true;
                break;
            case VISIBILITY_STUDIP:
                if ($user_id != "nobody") {
                    $is_visible = true;
                }
                break;
            case VISIBILITY_DOMAIN:
                $user_domains = UserDomain::getUserDomainsForUser($user_id);
                $owner_domains = UserDomain::getUserDomainsForUser($owner_id);
                if ((count($user_domains) === 0 && count($owner_domains) === 0)
                    || array_intersect($user_domains, $owner_domains)) {
                    $is_visible = true;
                }
                break;
            case VISIBILITY_BUDDIES:
                if (Contact::CountBySQL("user_id=? AND owner_id=?", [$user_id, $owner_id])) {
                    $is_visible = true;
                }
                break;
            case VISIBILITY_ME:
                if ($owner_id == $user_id) {
                    $is_visible = true;
                }
                break;
        }
    }
    return $is_visible;
}

/**
 * Checks whether a homepage element is visible on external pages.
 * We do not give an element name and look up its visibility setting in the
 * database, because that would generate many database requests for a single
 * user homepage. Instead, the homepage itself loads all element visibilities
 * and we only need to check if the given element visibility allows showing it.
 *
 * @param string $owner_id user ID of the homepage owner
 * @param string $owner_perm permission level of the homepage owner, needed
 * because every permission level can have its own not hideable fields.
 * @param string $field_name Name of the homepage field to check, needed for
 * checking if the element is not hideable
 * @param int $element_visibility visibility level of the element, one of
 * the constants VISIBILITY_ME, VISIBILITY_BUDDIES, VISIBILITY_DOMAIN,
 * VISIBILITY_STUDIP, VISIBILITY_EXTERN
 * @return boolean May the element be shown on external pages?
 */
function is_element_visible_externally($owner_id, $owner_perm, $field_name, $element_visibility) {
    global $NOT_HIDEABLE_FIELDS;
    $is_visible = false;
    if (!isset($element_visibility)) {
        $element_visibility = get_default_homepage_visibility($owner_id);
    }
    if ($element_visibility == VISIBILITY_EXTERN || $NOT_HIDEABLE_FIELDS[$owner_perm][$field_name])
        $is_visible = true;
    return $is_visible;
}

/**
 * Retrieves the standard visibility level for a homepage element if the user
 * hasn't specified anything explicitly. This default can be set via the global
 * configuration (variable "HOMEPAGE_VISIBILITY_DEFAULT").
 *
 * @return int Default visibility level.
 */
function get_default_homepage_visibility($user_id)
{
    $query = "SELECT default_homepage_visibility FROM user_visibility WHERE user_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute([$user_id]);
    $visibility = $statement->fetchColumn();

    if (intval($visibility) != 0) {
        $result = $visibility;
    } else {
        $result = @constant(Config::getInstance()->getValue('HOMEPAGE_VISIBILITY_DEFAULT'));
        if (!$result) {
            $result = VISIBILITY_STUDIP;
        }
    }
    return $result;
}

/**
 * Gets a user's email address. If the address should not be shown according
 * to the user's privacy settings, we try to get the email address of the
 * default institute (this can be one of the institutes the user is assigned
 * to). If no default institute is found, the email address of the first found
 * institute is given. If the user isn't assigned to any institute, an empty
 * string is returned.
 *
 * @param string $user_id which user's email address is required?
 * @return string User email address or email address of the user's default
 * institute or empty string.
 */
function get_visible_email($user_id) {
    $result = '';
    // Email address is visible -> just show user's address.
    if (get_local_visibility_by_id($user_id, 'email')) {
        $query = "SELECT Email FROM auth_user_md5 WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_id]);
        $result = $statement->fetchColumn();
    // User's email is not visible
    } else if ($GLOBALS['perm']->get_perm($user_id) == 'dozent') {
        // bei Dozenten eine Institutsadresse verwenden
        $query = "SELECT i.email, u.externdefault
                  FROM user_inst AS u
                  JOIN Institute AS i USING (Institut_id)
                  WHERE u.user_id = ? AND u.inst_perms != 'user'
                  ORDER BY u.priority";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_id]);

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if (!$result || $row['externdefault']) {
                $result = $row['email'];
            }
        }
    }
    return $result;
}
