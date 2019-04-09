<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * SessionDecoder.class.php
 *
 * decodes serialized PHP session data
 *
 * @author      André Noack <noack@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
 * @access      public
 * @package     core
 */
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// SessionDecoder.class.php
//
// Copyright (C) 2008 André Noack <noack@data-quest>, data-quest GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

class SessionDecoder implements ArrayAccess, Countable, Iterator {

    private $encoded_session = [];
    private $decoded_session = [];
    private $var_names = [];

    /**
     * Usage:
     * Pass the string containing encoded session data to the
     * constructor, the identified session variables become public members
     * of the object
     *
     * $session = new SessionDecoder($encoded_session_string);
     * print_r($session->my_var);
     * or
     * print_r($session['my_var']);
     * get the names of identified variables
     * print_r($session->keys());
     *
     * @param string $encoded_session_string
     */
    public function __construct($encoded_session_string) {
        $this->decode($encoded_session_string);
    }

    /**
     * pass an encoded session string to fill the object
     *
     * @param string $encoded_session_string
     * @return int number of identified variables
     */
    public function decode($encoded_session_string){
        $this->encoded_session = $this->sessionRealDecode($encoded_session_string);
        if(is_array($this->encoded_session)) {
            $this->var_names = array_keys($this->encoded_session);
        } else {
            $this->encoded_session = [];
        }
        $this->decoded_session = [];

        return count($this->encoded_session);
    }

    /**
     * returns an array containing the names of the identified variables
     *
     * @return array names of identified variables
     */
    public function keys() {
        return $this->var_names;
    }

    public function rewind() {
        reset($this->var_names);
    }

    public function current() {
        $current = current($this->var_names);
        return $current !== false ? $this->offsetGet($current) : false;
    }

    public function key() {
        return current($this->var_names);
    }

    public function next() {
        $next = next($this->var_names);
        return $this->current();
    }

    public function valid() {
        $current = current($this->var_names);
        return $current !== false;
    }

    public function offsetExists($offset){
        return isset($this->encoded_session[$offset]);
    }

    public function offsetGet($offset){
        if($this->offsetExists($offset) && !isset($this->decoded_session[$offset])){
            $this->decoded_session[$offset] = unserialize($this->encoded_session[$offset]);
        }
        return isset($this->decoded_session[$offset]) ? $this->decoded_session[$offset] : null;
    }

    public function offsetSet($offset, $value) {}
    public function offsetUnset($offset) {}

    public function count(){
        return count($this->var_names);
    }

    public function __get($name){
        return $this->offsetGet($name);
    }

    public function __isset($name){
        return $this->offsetExists($name);
    }

    /**
     * a function that returns decoded session data,
     * that seems to work in every cases,
     * even when strings contain reserved chars
     * (c) bmorel at ssi dot fr
     * http://www.php.net/manual/en/function.session-decode.php#56106
     *
     * @param string $str
     * @return array
     */
    private function sessionRealDecode($str) {
        $ret = [];
        $PS_DELIMITER = '|';
        $PS_UNDEF_MARKER = '!';
        $str = (string)$str;

        $endptr = strlen($str);
        $p = 0;

        $items = 0;
        $level = 0;

        while ($p < $endptr) {
            $q = $p;
            while ($str[$q] != $PS_DELIMITER)
            if (++$q >= $endptr) break 2;

            if ($str[$p] == $PS_UNDEF_MARKER) {
                $p++;
                $has_value = false;
            } else {
                $has_value = true;
            }

            $name = substr($str, $p, $q - $p);
            $q++;

            $serialized = '';
            if ($has_value) {
                for (;;) {
                    $p = $q;
                    switch ($str[$q]) {
                        case 'N': /* null */
                        case 'b': /* boolean */
                        case 'i': /* integer */
                        case 'd': /* decimal */
                            do $q++;
                            while ( ($q < $endptr) && ($str[$q] != ';') );
                            $q++;
                            $serialized .= substr($str, $p, $q - $p);
                            if ($level == 0) break 2;
                            break;
                        case 'R': /* reference  */
                            $q+= 2;
                            for ($id = ''; ($q < $endptr) && ($str[$q] != ';'); $q++) $id .= $str[$q];
                            $q++;
                            //$serialized .= 'R:' . ($id + 1) . ';'; /* increment pointer because of outer array */
                            $serialized .= 'N;'; /* unserializing references is not possible*/
                            if ($level == 0) break 2;
                            break;
                        case 's': /* string */
                            $q+=2;
                            for ($length=''; ($q < $endptr) && ($str[$q] != ':'); $q++) $length .= $str[$q];
                            $q+=2;
                            $q+= (int)$length + 2;
                            $serialized .= substr($str, $p, $q - $p);
                            if ($level == 0) break 2;
                            break;
                        case 'a': /* array */
                        case 'O': /* object */
                            do $q++;
                            while ( ($q < $endptr) && ($str[$q] != '{') );
                            $q++;
                            $level++;
                            $serialized .= substr($str, $p, $q - $p);
                            break;
                        case '}': /* end of array|object */
                            $q++;
                            $serialized .= substr($str, $p, $q - $p);
                            if (--$level == 0) break 2;
                            break;
                        default:
                            return false;
                    }
                }
            } else {
                $serialized .= 'N;';
                $q+= 2;
            }
            $items++;
            $p = $q;
            $ret[$name] = $serialized;
        }
        return $ret;
    }
}
?>
