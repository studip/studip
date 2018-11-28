<?php
/**
 * This class works like an array.
 * The internal array is constructed from a a json string
 * When used in an string context, it is automatically converted to a json string
 * utf8 de and encoding is done
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @link http://www.php.net/manual/en/class.arrayobject.php
 */
class JSONArrayObject extends MultiDimArrayObject
{
    /**
     * Construct an array object from a json string
     *
     * @param string $input a json string
     */
    function __construct($input)
    {
        if (is_string($input)) {
            $input = (array)json_decode($input, true);
        }
        parent::__construct($input);
    }

    /**
     * magic method for use of object in string context
     *
     * @return string internal array converted to json
     */
    function __toString()
    {
        return json_encode($this->getArrayCopy());
    }
}
