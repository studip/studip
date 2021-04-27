<?php
/**
 * An interface which has to be implemented by caches available for administration
 * via Stud.IP GUI
 *
 * @package    studip
 * @subpackage lib
 *
 * @author     Thomas Hackl <studip@thomas-hackl.name>
 * @copyright  2021 Stud.IP Core-Group
 * @since      Stud.IP 5.0
 * @license    GPL2 or any later version
 */

interface StudipSystemCache extends StudipCache
{

    /**
     * @return string A translateable display name for this cache class.
     */
    public static function getDisplayName(): string;

    /**
     * Get some statistics from cache, like number of entries, hit rate or
     * whatever the underlying cache provides.
     * Results are returned in form of an array like
     *      "[
     *          [
     *              'name' => <displayable name>
     *              'value' => <value of the current stat>
     *          ]
     *      ]"
     *
     * @return array
     */
    public function getStats(): array;

    /**
     * Return the Vue component name and props that handle configuration.
     * The associative array is of the form
     *  [
     *      'component' => <Vue component name>,
     *      'props' => <Properties for component>
     *  ]
     *
     * @return array
     */
    public static function getConfig(): array;
}
