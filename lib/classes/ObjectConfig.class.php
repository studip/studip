<?php
/**
 * ObjectConfig.class.php
 * provides access to object preferences
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2010 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
*/

class ObjectConfig extends Config
{
    /**
     * cache of created ObjectConfig instances
     * @var array
     */
    private static $instances;

    /**
     * range_id
     * @var string
     */
    private $range_id;

    /**
     * range type ('user' or 'course')
     * @var string
     */
    protected $range_type;

    /**
     * returns cached instance for given range_id
     * creates new objects if needed
     * @param string $range_id
     * @return ObjectConfig
     */
    public static function get()
    {
        $range_id = func_get_arg(0);
        if (self::$instances[$range_id] === null) {
            $config = new static($range_id);
            self::$instances[$range_id] = $config;
        }
        return self::$instances[$range_id];
    }

    /**
     * set cached instance for given range_id
     * use for testing or to unset cached instance by passing
     * null as second param
     * @param string $range_id
     * @param ObjectConfig $my_instance
     */
    public static function set()
    {
        list ($range_id, $my_instance) = func_get_args();
        self::$instances[$range_id] = $my_instance;
    }

    /**
     * passing null as first param is for compatibility and
     * should be considered deprecated.
     * passing data array as second param only for testing
     * @param string $range_id
     * @param array $data
     */
    public function __construct($range_id = null, $data = null)
    {
        $this->range_id = $range_id;
        if ($range_id !== null || $data !== null) {
            $this->fetchData($data);
        }
    }

    /**
     * @see lib/classes/Config::fetchData()
     */
    protected function fetchData($data = null)
    {
        if ($data !== null) {
            $this->data = $data;
        } else {
            $this->data = [];
            foreach(Config::get()->getFields($this->range_type) as $field) {
                $this->data[$field] = Config::get()->$field;
                $metadata[$field] = Config::get()->getMetadata($field);
            }
            $db = DBManager::get();
            try {
                $query = 'SELECT field, value FROM config_values WHERE range_id = ';
                $rs = $db->query($query . $db->quote($this->range_id));
            } catch (Exception $e) {
                //in case we have not migrated 226 yet:
                $query = 'SELECT field, value FROM user_config WHERE user_id = ';
                $rs = $db->query($query . $db->quote($this->range_id));
            }
            while ($row = $rs->fetch(PDO::FETCH_ASSOC)) {
                switch ($metadata[$row['field']]['type']) {
                    case 'integer':
                        $value = (int)$row['value'];
                        break;
                    case 'boolean':
                        $value = (bool)$row['value'];
                        break;
                    case 'array':
                        $value = (array)json_decode($row['value'], true);
                        break;
                    default:
                        $value = $row['value'];
                }
                $this->data[$row['field']] = $value ;
            }
        }
    }

    /**
     * returns the range id
     *
     * @return string
     */
    public function getRangeId()
    {
        return $this->range_id;
    }

    /**
     * @see lib/classes/Config::getValue()
     */
    public function getValue($field)
    {
        if (array_key_exists($field, $this->data)) {
            return $this->data[$field];
        }
        return null;
    }

    /**
     * @param string $field
     * @return bool
     */
    public function unsetValue($field)
    {
        return $this->delete($field);
    }

    /**
     * @see lib/classes/Config::store()
     */
    public function store($field, $value)
    {
        $entry = new ConfigValue([$field, $this->range_id]);
        $this->data[$field] = $value;

        // Check if entry is default and if so, delete it
        if (Config::get()->getValue($field) == $value) {
            $entry->delete();
            return 1;
        }

        // Otherwise convert it to an appropriate format and store it
        $metadata = Config::get()->getMetadata($field);
        switch ($metadata['type']) {
            case 'integer':
            case 'boolean':
                $value = (int)$value;
            break;
            case 'array' :
                $value = json_encode($value);
            break;
            default:
                $value = (string)$value;
        }
        $entry->value = $value;
        $ret = $entry->store();
        if ($ret) {
            $this->fetchData();
        }
        return $ret;

    }

    /**
     * @see lib/classes/Config::delete()
     */
    public function delete($field)
    {
        $entry = ConfigValue::find([$field, $this->range_id]);
        if ($entry !== null) {
            if ($ret = $entry->delete()) {
                $this->data[$field] = Config::get()->$field;
            }
            return $ret;
        } else {
            return null;
        }
    }

}
