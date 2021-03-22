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
     * Returns the full name of the range (in given format).
     *
     * @param  string $format
     * @return string
     */
    public function getFullname($format = 'default');

    /**
     * Returns the configuration object for this range.
     * @return RangeConfig
     */
    public function getConfiguration();

    /**
     * Decides whether the user may access the range.
     *
     * @param string|null $user_id Optional id of a user, defaults to current user
     * @return bool
     */
    public function isAccessibleToUser($user_id = null);

    /**
     * Decides whether the user may edit/alter the range.
     *
     * @param string|null $user_id Optional id of a user, defaults to current user
     * @return bool
     */
    public function isEditableByUser($user_id = null);
}
