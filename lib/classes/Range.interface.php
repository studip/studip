<?php
/**
 * Generic range interface. Ranges may be a lot of things in Stud.IP.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
interface Range
{
    /**
     * Returns a descriptive text for the range type.
     *
     * @return string
     */
    public function describeRange();

    /**
     * Returns a unique identificator for the range type.
     *
     * @return string
     */
    public function getRangeType();

    /**
     * Returns the id of the current range
     *
     * @return mixed (string|int)
     */
    public function getRangeId();

    /**
     * Decides whether the user may access the range.
     *
     * @param string $user_id Optional id of a user, defaults to current user
     * @return bool
     */
    public function userMayAccessRange($user_id = null);

    /**
     * Decides whether the user may edit/alter the range.
     *
     * @param string $user_id Optional id of a user, defaults to current user
     * @return bool
     */
    public function userMayEditRange($user_id = null);

    /**
     * Decides whether the user may administer the range.
     *
     * @param string $user_id Optional id of a user, defaults to current user
     * @return bool
     */
    public function userMayAdministerRange($user_id = null);
}
