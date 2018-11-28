<?php
/**
 * Loggable
 * This interface provides necessary functions to use the Stud.IP internal
 * logging.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2013 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 */

/**
 * Loggable
 * This interface provides necessary functions to use the Stud.IP internal
 * logging.
 * 
 * @see StudipLog
 */
interface Loggable
{
    
    /**
     * This function is used to format the info_template of the
     * action used by the given event and its properties. It is the first step
     * in the formatting process. It returns a string that will
     * be formatted by the replacements for the Stud.IP standard objects
     * (e.g. User, Seminar, Institute,...).
     * See LogEvent::formatEvent().
     * 
     * @param LogEvent $event
     */
    public static function logFormat(LogEvent $event);

    /**
     * This function is used to search for objects related to log events.
     * The search has to accept a string as part of the name or the id of the
     * object.
     * See search functions in StudipLog.
     * 
     * @param string $needle The needle to search for (object id or part of the
     * name)
     * @param string $action_name The name of the action.
     */
    public static function logSearch($needle, $action_name = null);
    
}