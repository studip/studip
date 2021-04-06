<?php
/**
 * RangeConfig.class.php
 * provides access to object preferences
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>>
 * @copyright   2010 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
*/

class RangeConfig extends Config
{
    /**
     * range type
     */
    const RANGE_TYPE = 'range';

    /**
     * cache of created RangeConfig instances
     * @var array
     */
    protected static $instances = [];

    /**
     * range_id
     * @var string
     */
    private $range_id;

    /**
     * returns cached instance for given range_id
     * creates new objects if needed
     * @param string $range_id
     * @return RangeConfig
     */
    public static function get()
    {
        if (func_num_args() === 0 || func_get_arg(0) === null) {
            return new static(true);
        }

        $range_id = func_get_arg(0);

        if ($range_id instanceof Range) {
            $range_id = $range_id->getRangeId();
        }

        if (static::$instances[$range_id] === null) {
            static::$instances[$range_id] = new static($range_id);
        }
        return static::$instances[$range_id];
    }

    /**
     * set cached instance for given range_id
     * use for testing or to unset cached instance by passing
     * null as second param
     * @param string $range_id
     * @param RangeConfig $my_instance
     */
    public static function set()
    {
        list($range_id, $my_instance) = func_get_args();
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
     * {@inheritdoc}
     */
    protected function fetchData($data = null)
    {
        if ($data !== null) {
            $this->data = $data;
        } else {
            $this->data = [];
            foreach (Config::get()->getFields(static::RANGE_TYPE) as $field) {
                $this->data[$field] = Config::get()->$field;
                $this->metadata[$field] = Config::get()->getMetadata($field);
            }
            try {
                $query = "SELECT `config_values`.`field`, `config_values`.`value`
                          FROM `config_values`
                          LEFT JOIN `config` USING (`field`)
                          WHERE `range_id` = :id
                            AND (
                                `field` IN (:fields)
                                OR `config`.`field` IS NULL
                            )";
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':id', $this->range_id);
                $statement->bindValue(':fields', array_keys($this->data));
                $statement->execute();
            } catch (Exception $e) {
                //in case we have not migrated 226 yet:
                $query = 'SELECT field, value FROM user_config WHERE user_id = :id';
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':id', $this->range_id);
                $statement->execute();
            }
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $this->data[$row['field']] = $this->convertFromDatabase(
                    $this->metadata[$row['field']]['type'],
                    $row['value'],
                    $row['field']
                );
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
        $entry->value = $this->convertForDatabase($metadata['type'], $value, $field);

        $ret = $entry->store();
        if ($ret) {
            $this->fetchData();
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function create($field, $data = [])
    {
        if (!isset($data['range'])) {
            $data['range'] = static::RANGE_TYPE;
        }

        parent::create($field, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($field)
    {
        $entry = ConfigValue::find([$field, $this->range_id]);
        if (!$entry) {
            return null;
        }

        if ($ret = $entry->delete()) {
            $this->data[$field] = Config::get()->$field;
        }
        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    protected function getI18NIdentifier($field)
    {
        return md5("{$field}|{$this->range_id}");
    }
}
