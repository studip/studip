<?php
if (!defined('SORT_NATURAL')) define('SORT_NATURAL', 6);
if (!defined('SORT_FLAG_CASE')) define('SORT_FLAG_CASE', 8);
/**
 * SimpleCollection.class.php
 * collection of assoc arrays with convenience
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2013 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
*/
class SimpleCollection extends StudipArrayObject
{

    /**
     * callable to initialize collection
     *
     * @var Closure
     */
    protected $finder;

    /**
     * number of records after last init
     *
     * @var int
     */
    protected $last_count;

    /**
     * collection with deleted records
     * @var SimpleCollection
     */
    protected $deleted;

    /**
     * creates a collection from an array of arrays
     * all arrays should contain same keys, but is not enforced
     *
     * @param array $data array containing assoc arrays
     * @return SimpleCollection
     */
    public static function createFromArray(Array $data)
    {
        return new SimpleCollection($data);
    }

    /**
     * converts arrays or objects to ArrayObject objects
     * if ArrayAccess interface is not available
     *
     * @param mixed $a
     * @return ArrayAccess
     */
    public static function arrayToArrayObject($a)
    {
        if ($a instanceof StudipArrayObject) {
            $a->setFlags(StudipArrayObject::ARRAY_AS_PROPS);
            return $a;
        } else if ($a instanceof ArrayObject) {
            return new StudipArrayObject($a->getArrayCopy(), StudipArrayObject::ARRAY_AS_PROPS);
        } else if ($a instanceof ArrayAccess) {
            return $a;
        } else {
            return new StudipArrayObject((array)$a, StudipArrayObject::ARRAY_AS_PROPS);
        }
    }

    /**
     * returns closure to compare a value against given arguments
     * using given operator
     *
     * @param string $operator
     * @param mixed $args
     * @throws InvalidArgumentException
     * @return Closure comparison function
     */
    public static function getCompFunc($operator, $args)
    {
        if ($operator instanceOf Closure) {
            $comp_func = function ($a) use ($args, $operator) {return $operator($a, $args);};
        } else {
            if (!is_array($args)) {
                $args = [$args];
            }
            switch ($operator) {
                case '==':
                    $comp_func = function ($a) use ($args) {return in_array($a, $args);};
                break;
                case '===':
                    $comp_func = function ($a) use ($args) {return in_array($a, $args, true);};
                break;
                case '!=':
                case '<>':
                    $comp_func = function ($a) use ($args) {return !in_array($a, $args);};
                break;
                case '!==':
                    $comp_func = function ($a) use ($args) {return !in_array($a, $args, true);};
                break;
                case '<':
                case '>':
                case '<=':
                case '>=':
                    $op_func = function ($a, $b) use ($operator) {
                        if ($operator === '<') {
                            return $a < $b;
                        } elseif ($operator === '<=') {
                            return $a <= $b;
                        } elseif ($operator === '>=') {
                            return $a >= $b;
                        } elseif ($operator === '>') {
                            return $a > $b;
                        }
                    };
                    $comp_func = function($a) use ($op_func, $args) {return $op_func($a, $args[0]);};
                break;
                case '><':
                    $comp_func = function($a) use ($args) {
                        return $a > $args[0] && $a < $args[1];
                    };
                break;
                case '>=<=':
                    $comp_func = function($a) use ($args) {
                        return $a >= $args[0] && $a <= $args[1];
                    };
                    break;
                case '%=':
                    $comp_func = function($a) use ($args) {
                        $a = mb_strtolower(SimpleCollection::translitLatin1($a));
                        $args = array_map('SimpleCollection::translitLatin1', $args);
                        $args = array_map('mb_strtolower', $args);
                        return in_array($a, $args);
                    };
                break;
                case '*=':
                    $comp_func = function($a) use ($args) {
                        foreach ($args as $arg) {
                            if (mb_strpos($a, $arg) !== false) {
                                return true;
                            }
                        }
                        return false;
                    };
                break;
                case '^=':
                    $comp_func = function($a) use ($args) {
                        foreach ($args as $arg) {
                            if (mb_strpos($a, $arg) === 0) {
                                return true;
                            }
                        }
                        return false;
                    };
                break;
                case '$=':
                    $comp_func = function($a) use ($args) {
                        foreach ($args as $arg) {
                            $found = mb_strrpos($a, $arg);
                            if ($found !== false && ($found
                                + mb_strlen($arg)) === mb_strlen($a)) {
                                return true;
                            }
                        }
                        return false;
                    };
                break;
                case '~=':
                    $comp_func = function($a) use ($args) {
                        foreach ($args as $arg) {
                            if (preg_match($arg, $a) === 1) {
                                return true;
                            }
                        }
                        return false;
                    };
                    break;
                default:
                    throw new InvalidArgumentException('unknown operator: ' . $operator);
             }
         }
         return $comp_func;
    }

    /**
     * transliterates latin1 string to ascii
     *
     * @param string $text
     * @return string
     */
    public static function translitLatin1($text) {
        if (!preg_match('/[\200-\377]/', $text)) {
            return $text;
        }
        $text = str_replace(['ä','A','ö','Ö','ü','Ü','ß'], ['a','A','o','O','u','U','s'], $text);
        $text = str_replace(['À','Á','Â','Ã','Å','Æ'], 'A' , $text);
        $text = str_replace(['à','á','â','ã','å','æ'], 'a' , $text);
        $text = str_replace(['È','É','Ê','Ë'], 'E' , $text);
        $text = str_replace(['è','é','ê','ë'], 'e' , $text);
        $text = str_replace(['Ì','Í','Î','Ï'], 'I' , $text);
        $text = str_replace(['ì','í','î','ï'], 'i' , $text);
        $text = str_replace(['Ò','Ó','Õ','Ô','Ø'], 'O' , $text);
        $text = str_replace(['ò','ó','ô','õ','ø'], 'o' , $text);
        $text = str_replace(['Ù','Ú','Û'], 'U' , $text);
        $text = str_replace(['ù','ú','û'], 'u' , $text);
        $text = str_replace(['Ç','ç','Ð','Ñ','Ý','ñ','ý','ÿ'], ['C','c','D','N','Y','n','y','y'] , $text);
        return $text;
    }

    /**
     * Constructor
     *
     * @param mixed $data array or closure to fill collection
     */
    function __construct($data = [])
    {
        parent::__construct();
        $this->finder = $data instanceof Closure ? $data : null;
        $this->deleted = clone $this;
        if ($data instanceof Closure) {
            $this->refresh();
        } else {
            $this->exchangeArray($data);
        }
    }

    /**
     * @param array $input
     * @return array
     */
    function exchangeArray($input)
    {
        return parent::exchangeArray(array_map('SimpleCollection::arrayToArrayObject', $input));
    }

    /**
     * converts the object and all elements to plain arrays
     *
     * @return array
     */
    function toArray()
    {
        $args = func_get_args();
        return $this->map( function ($a) use ($args) {
            if (method_exists($a, 'toArray')) {
                return call_user_func_array([$a, 'toArray'], $args);
            }
            if (method_exists($a, 'getArrayCopy')) {
                return $a->getArrayCopy();
            }
            return (array)$a;
        }
        );
    }

    /**
     *
     * @see ArrayObject::append()
     */
    function append($newval)
    {
        return parent::append(SimpleCollection::arrayToArrayObject($newval));
    }

    /**
     * Sets the value at the specified index
     * ensures the value has ArrayAccess
     *
     * @see ArrayObject::offsetSet()
     */
    function offsetSet($index, $newval)
    {
        if (is_numeric($index)) {
            $index = (int)$index;
        }
        return parent::offsetSet($index, SimpleCollection::arrayToArrayObject($newval));
    }

    /**
     * Unsets the value at the specified index
     * value is moved to internal deleted collection
     *
     * @see ArrayObject::offsetUnset()
     * @throws InvalidArgumentException
     */
    function offsetUnset($index)
    {
        if ($this->offsetExists($index)) {
            $this->deleted[] = $this->offsetGet($index);
        }
        return parent::offsetUnset($index);
    }

    /**
     * sets the finder function
     *
     * @param Closure $finder
     */
    function setFinder(Closure $finder)
    {
        $this->finder = $finder;
    }

    /**
     * get deleted records collection
     * @return SimpleCollection
     */
    function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * reloads the elements of the collection
     * by calling the finder function
     *
     * @return number of records after refresh
     */
    function refresh()
    {
        if (is_callable($this->finder)) {
            $data = call_user_func($this->finder);
            $this->exchangeArray($data);
            $this->deleted->exchangeArray([]);
            return $this->last_count = $this->count();
        }
    }

    /**
     * returns a new collection containing all elements
     * where given columns value matches given value(s) using passed operator
     * pass array for multiple values
     *
     * operators:
     * == equal, like php
     * === identical, like php
     * !=,<> not equal, like php
     * !== not identical, like php
     * <,>,<=,>= less,greater,less or equal,greater or equal
     * >< between without borders, needs two arguments
     * >=<= between including borders, needs two arguments
     * %= like string, transliterate to ascii,case insensitive
     * *= contains string
     * ^= begins with string
     * $= ends with string
     * ~= regex
     *
     * @param string $key the column name
     * @param mixed $value value to search for
     * @param mixed $op operator to find
     * @return SimpleCollection with found records
     */
    function findBy($key, $values, $op = '==')
    {
        $comp_func = self::getCompFunc($op, $values);
        return $this->filter(function($record) use ($comp_func, $key) {return $comp_func($record[$key]);});
    }

    /**
     * returns the first element
     * where given column has given value(s)
     * pass array for multiple values
     *
     * @param string $key the column name
     * @param mixed $value value to search for,
     * @param mixed $op operator to find
     * @return SimpleORMap found record
     */
    function findOneBy($key, $values, $op = '==')
    {
        $comp_func = self::getCompFunc($op, $values);
        return $this->filter(function($record) use ($comp_func, $key) {return $comp_func($record[$key]);}, 1)->first();
    }

    /**
     * apply given callback to all elements of
     * collection
     *
     * @param Closure $func the function to call
     * @return int addition of return values
     */
    function each(Closure $func)
    {
        $result = false;
        foreach ($this->storage as $record) {
            $result+= call_user_func($func, $record);
        }
        return $result;
    }

    /**
     * apply given callback to all elements of
     * collection and give back array of return values
     *
     * @param Closure $func the function to call
     * @return array
     */
    function map(Closure $func)
    {
        $results = [];
        foreach ($this->storage as $key => $value) {
            $results[$key] = call_user_func($func, $value, $key);
        }
        return $results;
    }

    /**
     * filter elements
     * if given callback returns true
     *
     * @param Closure $func the function to call
     * @param integer $limit limit number of found records
     * @return SimpleCollection containing filtered elements
     */
    function filter(Closure $func = null, $limit = null)
    {
        $results = [];
        $found = 0;
        foreach ($this->storage as $key => $value) {
            if (call_user_func($func, $value, $key)) {
                $results[$key] = $value;
                if ($limit && (++$found == $limit)) {
                    break;
                }
            }
        }
        return self::createFromArray($results);
    }

    /**
     * extract array of columns values
     * pass array or space-delimited string for multiple columns
     *
     * @param string|array $columns the column(s) to extract
     * @return array of extracted values
     */
    function pluck($columns)
    {
        if (!is_array($columns)) {
            $columns = words($columns);
        }
        $func = function($r) use ($columns) {
            $result = [];
            foreach ($columns as $c) {
                $result[] = $r[$c];
            }
            return $result;
        };
        $result = $this->map($func);
        return count($columns) === 1 ? array_map('current', $result) : $result;
    }

    /**
     * returns the collection as grouped array
     * first param is the column to group by, it becomes the key in
     * the resulting array, default is pk. Limit returned fields with second param
     * The grouped entries can optoionally go through the given
     * callback. If no callback is provided, only the first grouped
     * entry is returned, suitable for grouping by unique column
     *
     * @param string $group_by the column to group by, pk if ommitted
     * @param mixed $only_these_fields limit returned fields
     * @param Closure $group_func closure to aggregate grouped entries
     * @return array assoc array
     */
    function toGroupedArray($group_by = 'id', $only_these_fields = null, Closure $group_func = null)
    {
        $result = [];
        if (is_string($only_these_fields)) {
            $only_these_fields = words($only_these_fields);
        }
        foreach ($this->toArray() as $record) {
            $key = $record[$group_by];
            $ret = [];
            if (is_array($only_these_fields)) {
                $result[$key][] = array_intersect_key($record, array_flip($only_these_fields));
            } else {
                $result[$key][] = $record;
            }
        }
        if ($group_func === null) {
            $group_func = 'current';
        }
        return array_map($group_func, $result);
    }

    /**
     * get the first element
     *
     * @return Array first element or null
     */
    function first()
    {
        $keys = array_keys($this->storage);
        $first_offset = reset($keys);
        return $this->offsetGet($first_offset ?: 0);
    }

    /**
     * get the last element
     *
     * @return Array last element or null
     */
    function last()
    {
        $keys = array_keys($this->storage);
        $last_offset = end($keys);
        return $this->offsetGet($last_offset ?: 0);
    }

     /**
     * get the the value from given key from first element
     *
     * @return mixed
     */
    function val($key)
    {
        $first = $this->first();
        return $first[$key] ?: null;
    }

    /**
     * mark element(s) for deletion
     * where given column has given value(s)
     * element(s) are moved to
     * internal deleted collection
     * pass array for multiple values
     *
     * operators:
     * == equal, like php
     * === identical, like php
     * !=,<> not equal, like php
     * !== not identical, like php
     * <,>,<=,>= less,greater,less or equal,greater or equal
     * >< between without borders, needs two arguments
     * >=<= between including borders, needs two arguments
     * %= like string, transliterate to ascii,case insensitive
     * *= contains string
     * ^= begins with string
     * $= ends with string
     * ~= regex
     *
     * @param string $key
     * @param mixed $values
     * @param mixed $op operator to find elements
     * @return number of unsetted elements
     */
    function unsetBy($key, $values, $op = '==')
    {
        $ret = false;
        $comp_func = self::getCompFunc($op, $values);
        foreach ($this->storage as $k => $record) {
            if ($comp_func($record[$key])) {
                $this->offsetunset($k);
                $ret += 1;
            }
        }
        return $ret;
    }

    /**
     * sorts the collection by columns of contained elements and returns it
     *
     * works like sql order by:
     * first param is a string containing combinations of column names
     * and sort direction, separated by comma e.g.
     *  'name asc, nummer desc '
     *  sorts first by name ascending and then by nummer descending
     *  second param denotes the sort type (using PHP sort constants):
     *  SORT_LOCALE_STRING:
     *  compare items as strings, transliterate latin1 to ascii, case insensitiv, natural order for numbers
     *  SORT_NUMERIC:
     *  compare items as integers
     *  SORT_STRING:
     *  compare items as strings
     *  SORT_NATURAL:
     *  compare items as strings using "natural ordering"
     *  SORT_FLAG_CASE:
     *  can be combined (bitwise OR) with SORT_STRING or SORT_NATURAL to sort strings case-insensitively
     *
     * @param string $order columns to order by
     * @param integer $sort_flags
     * @return SimpleCollection the sorted collection
     */
    function orderBy($order, $sort_flags = SORT_LOCALE_STRING)
    {
        //('name asc, nummer desc ')
        $sort_locale = false;
        switch ($sort_flags) {
        case SORT_NATURAL:
            $sort_func = 'strnatcmp';
            break;
        case SORT_NATURAL | SORT_FLAG_CASE:
            $sort_func = 'strnatcasecmp';
            break;
        case SORT_STRING | SORT_FLAG_CASE:
            $sort_func = 'strcasecmp';
            break;
        case SORT_STRING:
            $sort_func = 'strcmp';
            break;
        case SORT_NUMERIC:
            $sort_func = function($a,$b) {return (int)$a-(int)$b;};
            break;
        case SORT_LOCALE_STRING:
        default:
            $sort_func = 'strnatcasecmp';
            $sort_locale = true;
        }

        $sorter = [];
        foreach (explode(',', $order) as $one) {
            $sorter[] = array_values(array_filter(array_map('trim', explode(' ', $one))));
        }

        $func = function ($d1, $d2) use ($sorter, $sort_func, $sort_locale) {
            do {
                list($field, $dir) = current($sorter);
                if (!$sort_locale) {
                    $value1 = $d1[$field];
                    $value2 = $d2[$field];
                } else {
                    $value1 = SimpleCollection::translitLatin1(mb_substr($d1[$field], 0, 100));
                    $value2 = SimpleCollection::translitLatin1(mb_substr($d2[$field], 0, 100));
                }
                $ret = $sort_func($value1, $value2);
                if (mb_strtolower($dir) == 'desc') $ret = $ret * -1;
            } while ($ret === 0 && next($sorter));

            return $ret;
        };
        if (count($sorter)) {
            $this->uasort($func);
        }
        return $this;
    }

    /**
     * returns a new collection contaning a sequence of original collection
     * mimics the sql limit constrain:
     * used with one parameter, the first x elements are extracted
     * used with two parameters, the first parameter denotes the offset, the second the
     * number of elements
     *
     * @param integer $arg1
     * @param integer $arg2
     * @return SimpleCollection
     */
    function limit($arg1, $arg2 = null)
    {
        if (is_null($arg2)) {
            if ($arg1 > 0) {
                $row_count = $arg1;
                $offset = 0;
            } else {
                $row_count = abs($arg1);
                $offset = $arg1;
            }
        } else {
            $offset = $arg1;
            $row_count = $arg2;
        }
        return self::createFromArray(array_slice($this->storage, $offset, $row_count, true));
    }

     /**
     * calls the given method on all elements
     * of the collection
     * @param string $method methodname to call
     * @param array $params parameters for methodcall
     * @return array of all return values
     */
    function sendMessage($method, $params = []) {
        $results = [];
        foreach ($this->storage as $record) {
            $results[] = call_user_func_array([$record, $method], $params);
        }
        return $results;
    }

    /**
     * magic version of sendMessage
     * calls undefineds methods on all elements
     * of the collection
     * But beware of the dark side...
     *
     * @param string $method methodname to call
     * @param array $params parameters for methodcall
     * @return array of all return values
     */
    function __call($method, $params)
    {
        return $this->sendMessage($method, $params);
    }

    /**
     * merge in another collection, elements are appended
     *
     * @param SimpleCollection $a_collection
     */
    function merge(SimpleCollection $a_collection)
    {
        $this->storage = array_merge($this->storage, $a_collection->getArrayCopy());
    }
}
