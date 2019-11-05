<?php
/**
 * Context.php - Helper class to handle the currently selected Stud.IP-object
 *
 * Usage:
 * Context::getId()
 *    -> to retrieve id of current Stud.IP-object
 *
 * Context::isCours()
 *    -> check if context is of type course/seminar
 *
 * Context::get()
 *    -> get sorm-object for current context
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class Context
{
    /**
     * Constants to check for type of context
     */
    const COURSE    = 'course';
    const INSTITUTE = 'institute';
    const USER      = 'user';

    /**
     * storage for the current context
     */
    private static $context = null;
    private static $type    = null;

    /**
     * Load context for passed id.
     *
     * @param string $id seminar_id, institute_id or user_id
     */
    private static function loadContext($id)
    {
        $possible_sorms = ['Course', 'Institute'];
        foreach($possible_sorms as $sorm) {
            if ($context = $sorm::find($id)) {
                self::$context = $context;
                self::$type    = strtolower($sorm);
            }
        }
    }

    /**
     * Return sorm-object of currently active Stud.IP object
     *
     * @return Course|Institute sorm-object of current context
     */
    public static function get()
    {
        return self::$context;
    }

    /**
     * Return id of currently active Stud.IP object
     *
     * @return string md5-hash
     */
    public static function getId()
    {
        if (!self::$context) {
            return null;
        }

        return self::$context->getId();
    }

    /**
     * Return type of currently active Stud.IP object. To easily check what type
     * of object that is, check the return value against
     * Context::COURSE, Context::INSTITUTE or Context::USER
     *
     * @return mixed one of Context::COURSE, Context::INSTITUTE or Context::USER
     */
    public static function getType()
    {
        return self::$type;
    }

    /**
     * Checks if current context is a seminar
     *
     * @return bool
     */
    public static function isCourse()
    {
        return self::getType() === self::COURSE;
    }

    /**
     * Checks if current context is an institute
     *
     * @return bool
     */
    public static function isInstitute()
    {
        return self::getType() === self::INSTITUTE;
    }

    /**
     * Checks if current context is an user
     *
     * @return bool
     */
    public static function isUser()
    {
        return self::getType() === self::USER;
    }

    /**
     * Get string representation of object-type fpr legacy support
     *
     * @deprecated
     *
     * @return string returns 'sem' or 'inst'
     */
    public static function getClass()
    {
        switch (self::getType()) {
            case self::COURSE:
                return 'sem';
                break;

            case self::INSTITUTE:
                return 'inst';
                break;
        }
    }

    /**
     * Get SemClass-number (kind of) for current context. Only works for
     * seminar or institute
     *
     * @deprecated
     *
     * @return int
     */
    public static function getArtNum()
    {
        if (self::isCourse()) {
            return self::get()->status;
        } else if (Context::isInstitute()) {
            return self::get()->type;
        }
    }

    /**
     * Return human readable text for current context, excluding user
     *
     * @deprecated
     *
     * @return string
     */
    public static function getTypeName()
    {
        switch (self::getType()) {
            case self::COURSE:
                return _('Veranstaltung');

            case self::INSTITUTE:
                return _('Einrichtung');
        }
    }

    /**
     * Get Fullname of current context, to use it in the page-title
     *
     * @return string or null if no context is available
     */
    public static function getHeaderLine()
    {
        if (!self::$context) {
            return null;
        }
        return self::get()->getFullname();
    }

    /**
     * Set the context to the object denoted by the passed id.
     * Usually there is no need to call this on your own, since seminar_open
     * already does this for you!
     *
     * @param string $id
     *
     * @throws AccessDeniedException
     */
    public static function set($id)
    {
        global $perm, $auth;

        self::close();
        self::loadContext($id);

        if (!self::getType()) {
            return;
        }

        if (self::isCourse() || self::isInstitute()) {
            $GLOBALS['SessionSeminar'] = $id;
        }

        URLHelper::addLinkParam('cid', $GLOBALS['SessionSeminar']);

        if (self::isCourse()) {
            $course = self::get();

            Seminar::setInstance(new Seminar($course));

            // check if current user can access the object
            if (!$perm->get_studip_perm($course['Seminar_id'])) {
                if ($course['lesezugriff'] > 0 || !Config::get()->ENABLE_FREE_ACCESS) {
                    // redirect to login page if user is not logged in
                    $auth->login_if($auth->auth['uid'] === 'nobody');

                    if (!$perm->get_studip_perm($course['Seminar_id'])) {
                        throw new AccessDeniedException();
                    }
                }
            }

            // if the aux data is forced for this seminar forward all user that havent made an input to this site
            if ($course['aux_lock_rule_forced']
                && !$perm->have_studip_perm('tutor', $course['Seminar_id'])
                && !match_route('dispatch.php/course/members/additional_input')
                && !match_route('dispatch.php/course/change_view/*'))
            {
                $query = "SELECT 1
                          FROM datafields_entries
                          WHERE range_id = ? AND sec_range_id = ?
                          LIMIT 1";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$GLOBALS['user']->id, $course['Seminar_id']]);

                if (!$statement->rowCount()) {
                    header('Location: ' . URLHelper::getURL('dispatch.php/course/members/additional_input'));
                    page_close();
                    die;
                }
            }
        } else if (self::isInstitute()) {
            // check if current user can access the object
            $no_access = (!Config::get()->ENABLE_FREE_ACCESS ||
                          (Config::get()->ENABLE_FREE_ACCESS && Config::get()->ENABLE_FREE_ACCESS_FOR_COURSES_ONLY)) &&
                         !$perm->have_perm('user');
            if ($no_access) {
                // redirect to login page if user is not logged in
                $auth->login_if($auth->auth['uid'] === 'nobody');

                if (!$perm->have_perm('user')) {
                    throw new AccessDeniedException();
                }
            }
        }
    }

    /**
     * "Close" the current context
     */
    public static function close()
    {
        self::$context = null;
        self::$type    = null;

        URLHelper::removeLinkParam('cid');
        unset($GLOBALS['SessionSeminar']);
    }
}
