<?php

/**
 * StudipDispatcher.php - create the default Trails dispatcher
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      <mlunzena@uos.de>
 * @copyright   2013 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * Use this subclass to easily get an Stud.IP specific
 * Trails_Dispatcher.
 *
 * Example of use:
 * @code
 * // deep in the Stud.IP jungle
 * $dispatcher = new StudipDispatcher();
 * $dispatcher->dispatch($requested_uri);
 * @endcode
 */
class StudipDispatcher extends Trails_Dispatcher {

  /**
   * Create a new Trails_Dispatcher with Stud.IP specific parameters
   * for: trails_root is "$STUDIP_BASE_PATH/app", trails_uri is
   * "dispatch.php" and default_controller is "default" (which does
   * not map to anything).
   */
    public function __construct()
    {
        global $STUDIP_BASE_PATH, $ABSOLUTE_URI_STUDIP;

        $trails_root = $STUDIP_BASE_PATH . DIRECTORY_SEPARATOR . 'app';
        $trails_uri = rtrim($ABSOLUTE_URI_STUDIP, '/') . '/dispatch.php';
        $default_controller = 'default';

        parent::__construct($trails_root, $trails_uri, $default_controller);
    }

    /**
     * Adapted error method that just passes the exception to stud.ip's
     * exception instead of the standard trails handling.
     *
     * @param Exception $exception The exception that occured
     * @throws Exception
     */
    public function trails_error($exception)
    {
        throw $exception;
    }
}
