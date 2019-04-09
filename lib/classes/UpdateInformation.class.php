<?php
/*
 * Copyright (c) 2011  Rasmus Fuhse
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * Class to set information that should be given to javascript.
 *
 * For a plugin to hand the information "test" to the javascript-function
 * STUDIP.myplugin.myfunction just put the line:
 *  if (UpdateInformation::isCollecting()) {
 *      UpdateInformation::setInformation("myplugin.myfunction", "test");
 *  }
 *
 * @author Rasmus Fuhse
 */
class UpdateInformation
{
    protected static $infos = [];
    protected static $collecting = null;
    protected static $request = null;

    /**
     * Returns the timestamp of the beginning of the run before.
     * Use this to only partially update new stuff.
     *
     * @return int Timestamp of the last run
     */
    public static function getTimestamp()
    {
        return Request::get('server_timestamp');
    }

    /**
     * Extracts updater data from request
     *
     * @return Array Request data (may be empty if no data is present)
     */
    protected static function getRequest()
    {
        if (self::$request === null) {
            self::$request = Request::getArray('page_info');
        }
        return self::$request ?: [];
    }

    /**
     * Checks whether the request has data for the given index.
     *
     * @return bool indicating whether there is data present for the given index
     */
    public static function hasData($index)
    {
        $request = self::getRequest();
        return isset($request[$index]);
    }

    /**
     * Returns request data for the given index.
     *
     * @param String $index Index to get the request data for
     * @return mixed Array with request data or null if index is invalid
     */
    public static function getData($index)
    {
        $request = self::getRequest();
        return $request[$index] ?: null;
    }

    /**
     * Gives information to the buffer for the javascript. The first parameter is
     * the name of the corresponding javascript-function minus the "STUDIP"
     * and the second parameter is the value handed to that function.
     * @param string $js_function : "test.testfunction" to get the JS-function "STUDIP.test.testfunction(information);"
     * @param mixed $information : anything that could be translated into a json-object
     */
    public static function setInformation($js_function, $information)
    {
        self::$infos[$js_function] = $information;
    }

    /**
     * returns the information to give it to javascript
     * @return array
     */
    public static function getInformation()
    {
        return self::$infos;
    }

    /**
     * returns if this request is a request, that wants to collect information
     * to hand it to javascript. Ask for this in your SystemPlugin-constructor.
     * @return: boolean
     */
    public static function isCollecting()
    {
        if (self::$collecting === null) {
            $page = $_SERVER['REQUEST_URI'];
            if (mb_strpos($page, "?") !== false) {
                $page = mb_substr($page, 0, mb_strpos($page, "?"));
            }
            self::$collecting = (mb_stripos($page, "dispatch.php/jsupdater/get") !== false);

            // If we are collecting, store the current timestamp
            if (self::$collecting) {
                self::$infos['server_timestamp'] = time();
            }
        }
        return self::$collecting;
    }
}

