<?php
/**
 * access_controlled_webservice.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2011 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
*/

/**
 * Abstract class, implementing access check in the before_filter(). All
 * webservices classes should be derived from this.
 *
 */
abstract class AccessControlledService extends Studip_Ws_Service
{
    /**
     * This method is called before every other service method and tries to
     * authenticate an incoming request using the first argument as an so
     * called "api key". If the "api key", the functions name and the remote IP
     * pass the access rules, the request will be authorized, otherwise a fault
     *  is sent back to the caller.
     *
     * @param string the function's name.
     * @param array an array of arguments that will be delivered to the function.
     *
     * @return mixed if this method returns a Studip_Ws_Fault, further
     *               processing will be aborted
     */
    function before_filter(&$name, &$args)
    {

        $api_key = current($args);

        if (!WebserviceAccessRule::checkAccess($api_key, $name, $_SERVER['REMOTE_ADDR'])) {
            return new Studip_Ws_Fault('Could not authenticate client.');
        }
    }
}
