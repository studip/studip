<?php
/**
 * Datafield
 * model class for table datafields
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2012 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string datafield_id database column
 * @property string id alias column for datafield_id
 * @property string name database column
 * @property string object_type database column
 * @property string object_class database column
 * @property string edit_perms database column
 * @property string view_perms database column
 * @property string system database column
 * @property string priority database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property string type database column
 * @property string typeparam database column
 * @property string is_required database column
 * @property string description database column
 * @property SimpleORMapCollection entries has_many DatafieldEntryModel
 */
class DataField extends SimpleORMap implements PrivacyObject
{
    protected static $permission_masks = [
        'user'   => 1,
        'autor'  => 2,
        'tutor'  => 4,
        'dozent' => 8,
        'admin'  => 16,
        'root'   => 32,
        'self'   => 64,
    ];

    /**
     * Configures this model.
     *
     * @param Array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'datafields';
        $config['has_many']['entries'] = [
            'class_name' => 'DatafieldEntryModel',
            'on_delete'  => 'delete',
        ];
        $config['has_many']['visibility_settings'] = [
            'class_name'        => 'User_Visibility_Settings',
            'assoc_foreign_key' => 'identifier',
            'on_delete'         => 'delete',
        ];

        $config['i18n_fields']['name'] = true;

        parent::configure($config);
    }

    /**
     * Returns a collection of datafields filtered by objectType,
     * objectClass and/or unassigned objectClasses.
     *
     * @param mixed  $objectType       Object type
     * @param String $objectClass      Object class
     * @param bool   $includeNullClass Should the object class "null" be
     *                                 included
     * @return array of DataField instances
     */
    public static function getDataFields($objectType = null, $objectClass = '', $includeNullClass = false)
    {
        $conditions = [];
        $parameters = [];

        if ($objectType !== null) {
            $conditions[] = 'object_type = ?';
            $parameters[] = $objectType;
        }

        if ($objectClass) {
            $condition = ['object_class & ?'];
            if ($includeNullClass) {
                $condition[] = 'object_class IS NULL';
            }

            $conditions[] = '(' . implode(' OR ', $condition) . ')';
            $parameters[] = $objectClass;
        }

        $where = implode(' AND ', $conditions) ?: '1';

        return self::findBySQL($where . " ORDER BY priority ASC, name ASC", $parameters);
    }

    /**
     * Returns a list of all datatype classes with an id as key and a name as
     * value.
     *
     * @return array() list of all datatype classes
     */
    public static function getDataClass()
    {
        return [
            'sem'                 => _('Veranstaltungen'),
            'inst'                => _('Einrichtungen'),
            'user'                => _('Benutzer'),
            'userinstrole'        => _('Benutzerrollen in Einrichtungen'),
            'usersemdata'         => _('Benutzer-Zusatzangaben in VA'),
            'roleinstdata'        => _('Rollen in Einrichtungen'),
            'moduldeskriptor'     => _('Moduldeskriptor'),
            'modulteildeskriptor' => _('Modulteildeskriptor')
        ];
    }

    /**
     * Return the mask for the given permission
     *
     * @param    string     the name of the permission
     * @return integer  the mask for the permission
     * @static
     */
    public static function permMask($perm)
    {
        return self::$permission_masks[$perm];
    }

    /**
     * liefert String zu gegebener user_class-Maske
     *
     * @param    integer    the user class mask
     * @return string       a string consisting of a comma separated list of
     *                      permissions
     */
    public static function getReadableUserClass($class)
    {
        $result = [];
        foreach (self::$permission_masks as $perm => $mask) {
            if ($class & $mask) {
                $result[] = $perm;
            }
        }
        return implode(', ', $result);
    }

    /**
     * Converts a camel cased field to it's snake case equivalent.
     *
     * @param String $field Field name as camel case
     * @return String containing the snake cased equivalent
     */
    private function convertLegacyFields($field)
    {
        $field = preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $field);
        return mb_strtolower($field);
    }

    /**
     * Specialized getter for values. Will try to obtain the value
     * and if an error occurs, convert it to snake case and try again.
     *
     * @param String $field Field name
     * @return mixed Contents of the variable with the key "$field"
     * @throws InvalidArgumentException when the field is invalid
     * @todo This should be removed after a while (today is 2015-11-19)
     */
    public function getValue($field)
    {
        try {
            return parent::getValue($field);
        } catch (Exception $e) {
            $field = $this->convertLegacyFields($field);
            return parent::getValue($field);
        }
    }

    /**
     * Specialized setter for values. Will try to set the value first
     * and if an error occurs, convert the field name to snake case and try
     * again.
     *
     * @param String $field Field name
     * @param mixed  $value Field value
     * @return mixed Whatever SimpleORMap::setValue() might return
     * @throws InvalidArgumentException when the field is invalid
     * @todo This should be removed after a while (today is 2015-11-19)
     */
    public function setValue($field, $value)
    {
        try {
            return parent::setValue($field, $value);
        } catch (Exception $e) {
            $field = $this->convertLegacyFields($field);
            return parent::setValue($field, $value);
        }
    }

    /**
     * Legacy handler for access via [get|set]VariableName().
     *
     * @param String $method    Called method
     * @param Array  $arguments Given arguments
     * @return mixed Return value of the getter/setter
     * @throws BadMethodCallException when the method does not match a
     *                                valid pattern
     */
    public function __call($method, array $arguments)
    {
        if (mb_substr($method, 0, 3) === 'get') {
            return $this->getValue(mb_substr($method, 3));
        }
        if (mb_substr($method, 0, 3) === 'set') {
            return $this->setValue(mb_substr($method, 3), $arguments[0]);
        }
        throw new BadMethodCallException('Call to undefined method ' . __CLASS__ . '::' . $method);
    }

    /**
     * Sets the type and adjusts type param as well.
     *
     * @param String $type Type of this datafield
     */
    public function setType($type)
    {
        $this->content['type'] = $type;

        if (!in_array($type, words('selectbox selectboxmultiple radio combo'))) {
            $this->typeparam = '';
        }
    }

    /**
     * Returns whether a user may access this datafield.
     *
     * @param String $perm    Permission of the user, optional defaults to
     *                        current user
     * @param String $watcher Current user
     * @param String $user    Associated user of the datafield
     * @return bool indicating whether the datafield may be accessed.
     */
    public function accessAllowed($perm = null, $watcher = '', $user = '')
    {
        if ($perm === null) {
            $perm = $GLOBALS['user']->perms;
        }

        $user_perms = self::permMask($perm);
        $required_perms = self::permMask($this->view_perms);

        # permission is sufficient
        if ($user_perms >= $required_perms) {
            return true;
        }

        // user may see his own data if this either no system field
        // or the user may edit the field
        if ($watcher && $user && $user === $watcher &&
            (!$this->system || $this->editAllowed($perm)))
        {
            return true;
        }

        # nothing matched...
        return false;
    }

    /**
     * Returns whether a user may edit this datafield.
     *
     * @param String $userPerms Permissions of the user
     * @return bool indicating whether the datafield may be edited
     */
    public function editAllowed($userPerms)
    {
        $user_perms     = self::permMask($userPerms);
        $required_perms = self::permMask($this->edit_perms);

        return $user_perms >= $required_perms;
    }

    /**
     * Specialized count method that returns the number of concrete entries.
     *
     * @return int number of entries
     */
    public function count()
    {
        return DatafieldEntryModel::countBySQL('datafield_id = ?', [$this->id]);
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = DataField::findThru($storage->user_id, [
            'thru_table'        => 'datafields_entries',
            'thru_key'          => 'range_id',
            'thru_assoc_key'    => 'datafield_id',
            'assoc_foreign_key' => 'datafield_id',
        ]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Datenfelder'), 'datafields', $field_data);
            }
        }
    }
}
