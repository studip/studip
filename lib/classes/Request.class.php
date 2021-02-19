<?php
# Lifter007: TODO
/*
 * Request.php - class representing a HTTP request in Stud.IP
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * Singleton class representing a HTTP request in Stud.IP.
 */
class Request implements ArrayAccess, IteratorAggregate
{
    /**
     * cached request parameter array
     */
    private $params;

    /**
     * Initialize a new Request instance.
     */
    private function __construct()
    {
        $this->params = array_merge($_GET, $_POST);
    }

    /**
     * Return the Request singleton instance.
     */
    public static function getInstance()
    {
        static $instance;

        if (isset($instance)) {
            return $instance;
        }

        return $instance = new Request();
    }

    /**
     * ArrayAccess: Check whether the given offset exists.
     */
    public function offsetExists($offset)
    {
        return isset($this->params[$offset]);
    }

    /**
     * ArrayAccess: Get the value at the given offset.
     */
    public function offsetGet($offset)
    {
        return $this->params[$offset];
    }

    /**
     * ArrayAccess: Set the value at the given offset.
     */
    public function offsetSet($offset, $value)
    {
        $this->params[$offset] = $value;
    }

    /**
     * ArrayAccess: Delete the value at the given offset.
     */
    public function offsetUnset($offset)
    {
        unset($this->params[$offset]);
    }

    /**
     * IteratorAggregate: Create interator for request parameters.
     */
    public function getIterator()
    {
        return new ArrayIterator((array)$this->params);
    }

    /**
     * Return the current URL, including query parameters.
     */
    public static function url()
    {
        return self::protocol() . '://' . self::server() . self::path();
    }

    /**
     * Return the current protocol ('http' or 'https').
     */
    public static function protocol()
    {

        // If a reverse proxy tells us the required protocol we should respect that
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            return $_SERVER['HTTP_X_FORWARDED_PROTO'];
        }

        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            ? 'https'
            : 'http';
    }

    /**
     * Return the current server name and port (host:port).
     */
    public static function server()
    {
        $host = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        $ssl  = $_SERVER['HTTPS'] == 'on';

        if ($ssl && $port != 443 || !$ssl && $port != 80) {
            $host .= ':' . $port;
        }

        return $host;
    }

    /**
     * Return the current request path, relative to the server root.
     */
    public static function path()
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * Set the selected query parameter to a specific value.
     *
     * @param string $param    parameter name
     * @param mixed  $value    parameter value (string or array)
     */
    public static function set($param, $value)
    {
        $request = self::getInstance();

        $request->params[$param] = $value;
    }

    /**
     * Return the value of the selected query parameter as a string.
     *
     * @param string $param    parameter name
     * @param string $default  default value if parameter is not set
     *
     * @return string  parameter value as string (if set), else NULL
     */
    public static function get($param, $default = NULL)
    {
        $request = self::getInstance();

        return (isset($request[$param]) && is_string($request[$param]))
             ? $request[$param]
             : $default;
    }

    /**
     * Return the value of the selected query parameter as an I18NString.
     *
     * @param string   $param    parameter name
     * @param string   $default  default value if parameter is not set
     * @param Callable $op       Operation to perform on each text string
     *
     * @return I18NString  parameter value as string (if set), else NULL
     */
    public static function i18n($param, $default = NULL, Callable $op = null)
    {
        $value = self::get($param, $default);

        if (isset($value)) {
            $lang = self::getArray($param . '_i18n');

            if ($op) {
                $value = $op($value);
                $lang  = array_map($op, $lang);
            }

            $value = new I18NString($value, $lang);
        }

        return $value;
    }

    /**
     * Return the value of the selected query parameter as a string.
     * The contents of the string is quoted with addslashes().
     *
     * @param string $param    parameter name
     * @param string $default  default value if parameter is not set
     *
     * @return string  parameter value as string (if set), else NULL
     */
    public static function quoted($param, $default = NULL)
    {
        $value = self::get($param, $default);

        if (isset($value)) {
            $value = addslashes($value);
        }

        return $value;
    }

    /**
     * Return the value of the selected query parameter as an alphanumeric
     * string (consisting of only digits, letters and underscores).
     *
     * @param string $param    parameter name
     * @param string $default  default value if parameter is not set
     *
     * @return string  parameter value as string (if set), else NULL
     */
    public static function option($param, $default = NULL)
    {
        $value = self::get($param, $default);

        if (!isset($value) || preg_match('/\\W/', $value)) {
            $value = $default;
        }

        return $value;
    }

    /**
     * Return the value of the selected query parameter as an integer.
     *
     * @param string $param    parameter name
     * @param int    $default  default value if parameter is not set
     *
     * @return int     parameter value as integer (if set), else NULL
     */
    public static function int($param, $default = NULL)
    {
        $value = self::get($param, $default);

        if (isset($value)) {
            $value = (int) $value;
        }

        return $value;
    }

    /**
     * Return the value of the selected query parameter as a float.
     *
     * @param string $param    parameter name
     * @param float  $default  default value if parameter is not set
     *
     * @return float   parameter value as float (if set), else NULL
     */
    public static function float($param, $default = NULL)
    {
        $value = self::get($param, $default);

        if (isset($value)) {
            $value = (float) strtr($value, ',', '.');
        }

        return $value;
    }


    /**
     * Returns the date and time values from one or two fields
     * as a DateTime object. The $second_param and $second_format
     * parameters are handy in case the date and time values
     * come from different fields.
     *
     * @param string $param The name of the date/time field.
     * @param string $format The date format of the date/time field.
     * @param string $second_param The name of the second field, if used.
     *     This parameter is optional.
     * @param string $second_format The time format of the second field, if used.
     *     This parameter is optional.
     * @param DateTime|null $default Either a default DateTime object
     *     or null if no default shall be set. In the latter case a DateTime
     *     object representing the unix timestamp 0 is returned.
     *
     * @returns DateTime|bool A DateTime object containing the
     *     date and time values of the specified date and time field.
     *     In case something went wrong the boolean value false is returned.
     *
     * @see the following PHP documentation page for a list of
     *     accepted date and time formats:
     *     https://secure.php.net/manual/en/datetime.createfromformat.php
     */
    public static function getDateTime(
        $param = 'date',
        $format = 'Y-m-d',
        $second_param = null,
        $second_format = null,
        $default = null
    )
    {
        $value = self::get($param);

        if (!$value) {
            //In case the first field is not set
            //use the default value, if any:
            if ($default instanceof DateTime) {
                return $default;
            } else {
                $datetime = new DateTime();
                $datetime->setTimestamp(0);
                return $datetime;
            }
        }

        //Combine the format specifications and the
        //values into one string each.
        $combined_format = $format;
        $combined_value = $value;

        //The second format and value is only added
        //when $second_param and $second_format are set
        //and a second value could be retrieved.
        if ($second_param and $second_format) {
            $second_value = Request::get($second_param);
            if ($second_value) {
                $combined_format .= ' ' . $second_format;
                $combined_value .= ' ' . $second_value;
            }
        }

        //The time zone may not be set in the fields
        //so we use the default timezone from a new
        //DateTime object:
        $value = new DateTime();
        $time_zone = $value->getTimezone();

        //Now we return a DateTime object created from the
        //specified date value(s):
        return DateTime::createFromFormat(
            $combined_format,
            $combined_value,
            $time_zone
        );
    }


    /**
     * Retrieves a parameter that stores time data and converts the time data
     * to a DateTime object. If a date is specified using an existing DateTime
     * object, the time will be set on the existing DateTime object and the
     * modified object is returned.
     *
     * @param string $param The name of the time field.
     *
     * @param string $format The time format of the time field.
     *
     * @param DateTime|null $date An optional DateTime object whose time
     *     shall be set from the specified parameter.
     *
     * @returns DateTime|bool A DateTime object containing the
     *     time value of the specified date and time field.
     *     In case something went wrong the boolean value false is returned.
     *
     */
    public static function getTime(
        $param = 'time',
        $format = 'H:i',
        $date = null
    )
    {
        $value = Request::get($param);

        //Get the timezone before parsing the time
        //so that the resulting DateTime object
        //will have the current timezone set.
        $tz_get = new DateTime();
        $time_zone = $tz_get->getTimezone();
        $converted_value = DateTime::createFromFormat(
            $format,
            $value,
            $time_zone
        );

        if ($date instanceof DateTime) {
            //Modify the time information of the specified
            //DateTime object and return the modified object.
            $date->setTime(
                intval($converted_value->format('H')),
                intval($converted_value->format('i')),
                intval($converted_value->format('s'))
            );

            return $date;
        }

        //Return the time value.
        return $converted_value;
    }


    /**
     * Return the value of the selected query parameter as a string
     * consisting only of allowed characters for usernames.
     *
     * @param string $param    parameter name
     * @param string  $default  default value if parameter is not set
     *
     * @return string   parameter value (if set), else NULL
     */
    public static function username($param, $default = NULL)
    {
        $value = self::get($param, $default);

        if (!isset($value) || !preg_match(Config::get()->USERNAME_REGULAR_EXPRESSION, $value)) {
            $value = $default;
        }

        return $value;
    }

    /**
     * Return the value of the selected query parameter as an array.
     *
     * @param string $param    parameter name
     *
     * @return array  parameter value as array (if set), else an empty array
     */
    public static function getArray($param)
    {
        $request = self::getInstance();

        return (isset($request[$param]) && is_array($request[$param]))
            ? $request[$param]
            : [];
    }

    /**
     * Return the value of the selected query parameter as a string array.
     * The contents of each element is quoted with addslashes().
     *
     * @param string $param    parameter name
     *
     * @return array  parameter value as array (if set), else an empty array
     */
    public static function quotedArray($param)
    {
        $array = self::getArray($param);

        return self::addslashes($array);
    }

    /**
     * Return the value of the selected query parameter as an array of
     * alphanumeric strings (consisting of only digits, letters and
     * underscores).
     *
     * @param string $param    parameter name
     *
     * @return array  parameter value as array (if set), else an empty array
     */
    public static function optionArray($param)
    {
        $array = self::getArray($param);

        foreach ($array as $key => $value) {
            if (preg_match('/\\W/', $value)) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    /**
     * Return the value of the selected query parameter as an integer array.
     *
     * @param string $param    parameter name
     *
     * @return array  parameter value as array (if set), else an empty array
     */
    public static function intArray($param)
    {
        $array = self::getArray($param);

        foreach ($array as $key => $value) {
            $array[$key] = (int) $value;
        }

        return $array;
    }

    /**
     * Return the value of the selected query parameter as a float array.
     *
     * @param string $param    parameter name
     *
     * @return array  parameter value as array (if set), else an empty array
     */
    public static function floatArray($param)
    {
        $array = self::getArray($param);

        foreach ($array as $key => $value) {
            $array[$key] = (float) strtr($value, ',', '.');
        }

        return $array;
    }

    /**
     * Return the value of the selected query parameter as a boolean.
     *
     * @param string $param   parameter name
     * @param bool   $default default value if parameter is not set
     *
     * @return bool parameter value as bool (if set), else NULL
     *
     * @since Stud.IP 4.4
     */
    public static function bool($param, $default = null)
    {
        $value = self::get($param, $default);

        if (isset($value)) {
            $value = (bool) $value;
        }

        return $value;
    }

    /**
     * Return the value of the selected query parameter as a boolean array.
     *
     * @param string $param    parameter name
     *
     * @return array  parameter value as array (if set), else an empty array
     *
     * @since Stud.IP 4.4
     */
    public static function boolArray($param)
    {
        $array = self::getArray($param);

        foreach ($array as $key => $value) {
            $array[$key] = (bool) $value;
        }

        return $array;
    }

    /**
     * Return the value of the selected query parameter as an array of
     * strings consisting only of allowed characters for usernames.
     *
     * @param string $param    parameter name
     *
     * @return array  parameter value as array (if set), else an empty array
     */
    public static function usernameArray($param)
    {
        $array = self::getArray($param);

        foreach ($array as $key => $value) {
            if (!preg_match(Config::get()->USERNAME_REGULAR_EXPRESSION, $value)) {
                unset($array[$key]);
            }
        }

        return $array;
    }
    /**
     * Check whether a form submit button has been pressed. This works for
     * both image and text submit buttons.
     *
     * @param string $param    submit button name
     *
     * @returns boolean  true if the button has been submitted, else false
     */
    public static function submitted($param)
    {
        $request = self::getInstance();

        return isset($request[$param])
            || isset($request[$param . '_x']);
    }

    /**
     * Check whether one of the form submit buttons has been
     * pressed. This works for both image and text submit buttons.
     *
     * @param string ...
     *                 a variable argument list of submit button names
     *
     * @returns boolean  true if any button has been submitted, else false
     */
    public static function submittedSome($param/*, ... */)
    {
        foreach(func_get_args() as $button) {
            if (self::submitted($button)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Quote a given string or array using addslashes(). If the parameter
     * is an array, the quoting is applied recursively.
     *
     * @param mixed $value    string or array value to be quoted
     *
     * @return mixed  quoted string or array
     */
    public static function addslashes($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = self::addslashes($val);
            }
        } else {
            $value = addslashes($value);
        }

        return $value;
    }

    /**
     * Returns the (uppercase) request method.
     *
     * @return string  the uppercased method of the request
     */
    public static function method()
    {
        return mb_strtoupper($_SERVER['X_HTTP_METHOD_OVERRIDE'] ?: $_SERVER['REQUEST_METHOD']);
    }

    /**
     * @return boolean  true if this a GET request
     */
    public static function isGet()
    {
        return self::method() === 'GET';
    }

    /**
     * @return boolean  true if this a POST request
     */
    public static function isPost()
    {
        return self::method() === 'POST';
    }

    /**
     * @return boolean  true if this a PUT request
     */
    public static function isPut()
    {
        return self::method() === 'PUT';
    }

    /**
     * @return boolean  true if this a DELETE request
     */
    public static function isDelete()
    {
        return self::method() === 'DELETE';
    }


    /**
     * @return boolean  true if this an XmlHttpRequest sent by jQuery/prototype
     */
    public static function isXhr()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'xmlhttprequest') === 0;
    }

    /**
     * This is an alias of Request::isXhr
     *
     * @return boolean  true if this an XmlHttpRequest sent by jQuery/prototype
     */
    public static function isAjax()
    {
        return self::isXhr();
    }

    /**
     * extracts some params from request, the desired params must be a comma separated list
     * for each param, the type of used extraction method can be specified after the name,
     * default is get
     * null values are not returned
     *
     * e.g.:
     * $data = Request::extract('admission_prelim int, admission_binding submitted, admission_prelim_txt');
     * will yield
     * array(3) {
     *    ["admission_prelim"]=>
     *    int(1)
     *    ["admission_binding"]=>
     *    bool(false)
     *    ["admission_prelim_txt"]=>
     *    string(0) ""
     *  }
     * @param string $what comma separated list of param names and types
     * @return array assoc array with extracted data
     */
    public static function extract($what)
    {
        $extract = [];
        $return = [];
        foreach (explode(',', $what) as $one) {
            $extract[] = array_values(array_filter(array_map('trim', explode(' ', $one))));
        }
        foreach ($extract as $one) {
            list($param, $func) = $one;
            if (!$func) {
                $func = 'get';
            }
            $value = self::$func($param);
            if ($value !== null) {
                $return[$param] = $value;
            }
        }
        return $return;
    }

    /**
     * returns true if http header indicates that the response will be rendered as dialog
     *
     * @return bool
     */
    public static function isDialog()
    {
        return self::isXhr() && isset($_SERVER['HTTP_X_DIALOG']);
    }

    /**
     * Returns an object that has previously been serialized using the
     * ObjectBuilder.
     *
     * @param String $param         parameter name
     * @param mixed $expected_class Expected class name of object (optional)
     * @param bool   $allow_null    If true, return null on error; otherwise an
     *                              exception is thrown
     * @return mixed Object of arbitrary type or null on error and $allow_null
     * @throws Exception when an error occurs and $allow_null = false
     * @see ObjectBuilder
     */
    public static function getObject($param, $expected_class = null, $allow_null = true)
    {
        try {
            return ObjectBuilder::build(Request::get($param), $expected_class);
        } catch (Exception $e) {
            if ($allow_null) {
                return null;
            }

            throw $e;
        }
    }

    /**
     * Returns a collection of objects that have previously been serialized
     * using the ObjectBuilder.
     *
     * @param String $param         parameter name
     * @param mixed $expected_class Expected class name of objects (optional)
     * @param bool   $allow_null    If true, return empty array on error;
     *                              otherwise an exception is thrown
     * @return array as collection of objects
     * @throws Exception when an error occurs and $allow_null = false
     * @see ObjectBuilder
     */
    public static function getManyObjects($param, $expected_class = null, $allow_null = true)
    {
        try {
            $request = self::getInstance();
            return ObjectBuilder::buildMany($request[$param] ?: null, $expected_class);
        } catch (Exception $e) {
            if ($allow_null) {
                return [];
            }

            throw $e;
        }
    }
}
