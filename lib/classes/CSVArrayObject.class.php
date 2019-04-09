<?php
/**
 * This class works like an array.
 * The internal array is constructed from a comma separated string
 * When used in an string context, it is automatically converted to a comma
 * separated string
 *
 * Usage:
 * @code
 * $csvarray = new CSVArrayObject('eins,zwei,drei');
 * $csvarray[] = 'vier';
 * echo $csvarray; // prints out "eins,zwei,drei,vier"
 * @endcode
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @link http://www.php.net/manual/en/class.arrayobject.php
 */
class CSVArrayObject extends StudipArrayObject
{
    /**
     * Construct an array object from a string of comma separated items
     *
     * @param string $input a string of comma separated items
     */
    function __construct($input)
    {
        if (is_string($input)) {
            $input = mb_strlen($input) ? array_map('trim', explode(',', $input)) : [];
        }
        parent::__construct((array)$input);
    }

    /**
     * magic method for use of object in string context
     *
     * @return string internal array itmes converted to a comma separated list
     */
    function __toString()
    {
        return implode(',', $this->getArrayCopy());
    }
}
