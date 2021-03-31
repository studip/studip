<?php
/**
 * Config.class.php
 * provides access to global configuration
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

class Config implements ArrayAccess, Countable, IteratorAggregate
{
    private static $instance = null;

    /**
     * contains all config entries as field => value pairs
     * @var array
     */
    protected $data = [];
    /**
     * contains additional metadata for config fields
     * @var array
     */
    protected $metadata = [];

    /**
     * returns singleton instance
     * @return Config
     */
    public static function get()
    {
        if (self::$instance === null) {
            $config = new Config();
            self::$instance = $config;
        }
        return self::$instance;
    }

    /**
     * alias of Config::get() for compatibility
     * @return Config
     */
    public static function getInstance()
    {
        return self::get();
    }

    /**
     * use to set singleton instance for testing
     * or to unset by passing null
     * @param Config $my_instance
     */
    public static function set()
    {
        $my_instance = func_get_arg(0);
        self::$instance = $my_instance;
    }

    /**
     * pass array of config entries in field => value pairs
     * to circumvent fetching from database
     * @param array $data
     */
    public function __construct($data = null)
    {
        $this->fetchData($data);
    }

    /**
     * returns a list of config entry names, filtered by
     * given params
     * @param string filter by range: global, range, user, course or institute
     * @param string filter by section
     * @param string filter by part of name
     * @return array
     */
    public function getFields($range = null, $section = null, $name = null)
    {
        if ($range && !in_array($range, words('global range user course institute'))) {
            throw new Exception('Invalid range type');
        }

        $temp = $this->metadata;

        if ($range) {
            $temp = array_filter($temp, function ($a) use ($range) {
                return $a['range'] === $range
                    || ($a['range'] === 'range' && in_array($range, words('user course institute')));
            });
        }
        if ($section) {
            $temp = array_filter($temp, function ($a) use ($section) {
                return $a['section'] === $section;
            });
        }
        if ($name) {
            $temp = array_filter($temp, function ($a) use ($name) {
                return mb_stripos($a['field'], $name) !== false;
            });
        }

        $fields = array_keys($temp);
        sort($fields, SORT_NATURAL |  SORT_FLAG_CASE);
        return $fields;
    }

    /**
     * returns metadata for config entry
     * @param srting $field
     * @return array
     */
    public function getMetadata($field)
    {
        return $this->metadata[$field];
    }

    /**
     * returns value of config entry
     * for compatibility reasons an existing variable in global
     * namespace with the same name is also returned
     * @param string $field
     * @return Ambigous
     */
    public function getValue($field)
    {
        if (array_key_exists($field, $this->data)) {
            return $this->data[$field];
        }
        if (isset($GLOBALS[$field]) && !isset($_REQUEST[$field])) {
            return $GLOBALS[$field];
        }
    }

    /**
     * set config entry to given value, but don't store it
     * in database
     * @param string $field
     * @param unknown_type $value
     * @return
     */
    public function setValue($field, $value)
    {
        if (array_key_exists($field, $this->data)) {
            return $this->data[$field] = $value;
        }
    }

    /**
     * IteratorAggregate
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }
    /**
     * magic method for dynamic properties
     */
    public function __get($field)
    {
        return $this->getValue($field);
    }
    /**
     * magic method for dynamic properties
     */
    public function __set($field, $value)
    {
         return $this->setValue($field, $value);
    }
    /**
     * magic method for dynamic properties
     */
    public function __isset($field)
    {
        return isset($this->data[$field]);
    }
    /**
     * ArrayAccess: Check whether the given offset exists.
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }
    /**
     * ArrayAccess: Get the value at the given offset.
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }
    /**
     * ArrayAccess: Set the value at the given offset.
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }
    /**
     * ArrayAccess: unset the value at the given offset (not applicable)
     */
    public function offsetUnset($offset)
    {

    }
    /**
     * Countable
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * fetch config data from table config
     * pass array to override database access
     * @param array $data
     */
    protected function fetchData($data = null)
    {
        if ($data !== null) {
            $this->data = $data;
        } else {
            $this->data = [];
            $db = DBManager::get();

            try {
                $query = "SELECT config.field, IFNULL(config_values.value, config.value) AS value, type, section, `range`, description,
                                 config_values.comment, config_values.value = config.value AS is_default
                          FROM config
                          LEFT JOIN config_values ON config.field = config_values.field AND range_id = 'studip'
                          ORDER BY section, config.field";
                $rs = $db->query($query);
            } catch (Exception $e) {
                //if migration is smaller than 226 and Stud.IP needs to be migrated to version 4.1 or greater:
                $query = "SELECT field, value, type, section, `range`, description, comment, is_default
                          FROM `config`
                          ORDER BY is_default DESC, section, field";
                $rs = $db->query($query);
            }

            while ($row = $rs->fetch(PDO::FETCH_ASSOC)) {
                // set the the type of the default entry for the modified entry
                if (!empty($this->metadata[$row['field']])) {
                    $row['type'] = $this->metadata[$row['field']]['type'];
                }

                $this->data[$row['field']] = $this->convertFromDatabase(
                    $row['type'],
                    $row['value'],
                    $row['field']
                );

                $this->metadata[$row['field']] = array_intersect_key($row, array_flip(words('type section range description is_default comment')));
                $this->metadata[$row['field']]['field'] = $row['field'];
                $this->metadata[$row['field']]['type']  = $row['type'] ?: 'string';
            }
        }
    }

    /**
     * store new value for existing config entry in database
     * posts notification ConfigValueChanged if entry is changed
     * @param string $field
     * @param string $data
     * @throws InvalidArgumentException
     * @return boolean
     */
    public function store($field, $data)
    {
        if (!is_array($data) || !isset($data['value'])) {
            $values['value'] = $data;
        } else {
            $values = $data;
        }

        $values['value'] = $this->convertForDatabase(
            $this->metadata[$field]['type'],
            $values['value'],
            $field
        );

        $entry = ConfigEntry::find($field);
        if (!isset($entry)) {
            throw new InvalidArgumentException($field . " not found in config table");
        }
        $ret = 0;
        if (isset($values['value'])) {
            $value_entry = new ConfigValue([$field, 'studip']);
            $old_value = $value_entry->isNew() ? $entry->value : $value_entry->value;
            $value_entry->value = $values['value'];
            if (isset($values['comment'])) {
                $value_entry->comment = $values['comment'];
            }
            if ($entry->isDefault($value_entry)) {
                $ret += $value_entry->delete();
            } else {
                $ret += $value_entry->store();
            }
        }

        if (isset($values['section'])) {
            $entry->section = $values['section'];
            $ret += $entry->store();
        }

        if ($ret) {
            $this->fetchData();
            if (isset($value_entry)) {
               NotificationCenter::postNotification('ConfigValueDidChange', $this, [
                   'field'     => $field,
                   'old_value' => $old_value,
                   'new_value' => $value_entry->value,
               ]);
            }
        }
        return $ret > 0;
    }

    /**
     * creates a new config entry in database
     * @param string name of entry
     * @param array data to insert as assoc array
     * @throws InvalidArgumentException
     * @return Ambigous <NULL, ConfigEntry>
     */
    public function create($field, $data = [])
    {
        if (!$field) {
            throw new InvalidArgumentException("config fieldname is mandatory");
        }
        $entry = new ConfigEntry($field);
        if (!$entry->isNew()) {
            throw new InvalidArgumentException("config $field already exists");
        }
        $entry->setData($data);
        $ret = $entry->store() ? $entry : null;
        if ($ret) {
            $this->fetchData();
        }
        return $ret;
    }

    /**
     * delete config entry from database
     * @param string name of entry
     * @throws InvalidArgumentException
     * @return integer number of deleted rows
     */
    public function delete($field)
    {
        if (!$field) {
            throw new InvalidArgumentException("config fieldname is mandatory");
        }
        ConfigValue::deleteBySql('field=?', [$field]);
        $deleted = ConfigEntry::deleteBySql('field=?', [$field]);
        if ($deleted) {
            $this->fetchData();
        }
        return $deleted;
    }

    /**
     * Returns the identifier for the i18n field.
     * @param  string $field
     * @return string
     */
    protected function getI18NIdentifier($field)
    {
        return md5($field);
    }

    /**
     * Transforms the data from the database for use.
     *
     * @param  string $type
     * @param  mixed  $value
     * @param  string $field
     * @return mixed
     */
    public function convertFromDatabase($type, $value, $field)
    {
        if ($type === 'integer') {
            return (int) $value;
        }

        if ($type === 'boolean') {
            return (bool) $value;
        }

        if ($type === 'array') {
            return (array) json_decode($value, true);
        }

        if ($type === 'i18n') {
            return new I18NString($value, null, [
                'object_id' => $this->getI18NIdentifier($field),
                'table'     => 'config',
                'field'     => 'value',
            ]);
        }

        return (string) $value;
    }

    /**
     * Transforms the given value to be stored in the database.
     *
     * @param  string $type
     * @param  mixed  $value
     * @param  string $field
     * @return mixed
     */
    public function convertForDatabase($type, $value, $field)
    {
        if ($type === 'boolean') {
            return (bool) $value;
        }

        if ($type === 'integer') {
            return (int) $value;
        }

        if ($type === 'array') {
            return json_encode($value);
        }

        if ($type === 'i18n') {
            $value->setMetadata([
                'object_id' => $this->getI18NIdentifier($field),
                'table'     => 'config',
                'field'     => 'value',
            ]);
            $value->storeTranslations();

            return $value->original();
        }

        return (string) $value;
    }
}
