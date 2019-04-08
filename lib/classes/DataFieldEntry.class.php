<?php
/**
 * DataFieldEntry.class.php
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Marcus Lunzenauer <mlunzena@uos.de>
 * @author  Martin Gieseking <mgieseki@uos.de>
 * @license GPL2 or any later version
 */
abstract class DataFieldEntry
{
    protected static $supported_types = array(
        'bool',
        'textline',
        'textlinei18n',
        'textarea',
        'textareai18n',
        'textmarkup',
        'textmarkupi18n',
        'selectbox',
        'selectboxmultiple',
        'date',
        'time',
        'email',
        'phone',
        'radio',
        'combo',
        'link',
    );

    protected $language = '';

    /**
     * Returns all supported datafield types.
     *
     * @param string Object type of the datafield.
     * @return array of supported types
     */
    public static function getSupportedTypes($object_type = null)
    {
        // no i18n fields for descriptors
        if (in_array($object_type, ['moduldeskriptor', 'modulteildeskriptor'])) {
            return array_diff(
                self::$supported_types,
                [
                    'textlinei18n',
                    'textareai18n',
                    'textmarkupi18n'
                ]);
        }
        return self::$supported_types;
    }

    /**
     * Returns the according class for a given type.
     *
     * @param String $type Type of the datafield
     * @return String class name
     */
    private static function getClassForType($type)
    {
        if ($type === 'selectboxmultiple') {
            return 'DataFieldSelectboxMultipleEntry';
        }
        return 'DataField' . ucfirst($type) . 'Entry';
    }

    /**
     * Factory method that returns the appropriate datafield object
     * for the given parameters.
     *
     * @param DataField $datafield Underlying structure
     * @param String    $rangeID   Range id
     * @param mixed     $value     Value of the entry
     * @return DataFieldEntry instance of appropriate type
     */
    public static function createDataFieldEntry(DataField $datafield, $rangeID = '', $value = null)
    {
        $type = $datafield->type;
        if (!in_array($type, self::getSupportedTypes())) {
            return false;
        }

        $entry_class = self::getClassForType($type);
        $entry = new $entry_class($datafield, $rangeID, $value);

        return $entry;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $range_id
     * @param unknown_type $object_type
     * @param unknown_type $object_class_hint
     * @return unknown
     */
    public static function getDataFieldEntries($range_id, $object_type = '', $object_class_hint = '')
    {
        if (!$range_id) {
            return []; // we necessarily need a range ID
        }

        $parameters = array();
        if(is_array($range_id)) {
            // rangeID may be an array ("classic" rangeID and second rangeID used for user roles)
            $secRangeID = $range_id[1];
            $rangeID = $range_id[0]; // to keep compatible with following code
            if('usersemdata' !== $object_type && 'roleinstdata' !== $object_type) {
                $object_type = 'userinstrole';
            }
            $clause1 = "AND sec_range_id= :sec_range_id";
            $parameters[':sec_range_id'] = $secRangeID;
        } else {
            $rangeID = $range_id;
        }
        if (!$object_type) {
            $object_type = get_object_type($rangeID);
        }

        if($object_type) {
            switch ($object_type) {
                case 'sem':
                    if ($object_class_hint) {
                        $object_class = SeminarCategories::GetByTypeId($object_class_hint);
                    } else {
                        $object_class = SeminarCategories::GetBySeminarId($rangeID);
                    }
                    $clause2 = "object_class = :object_class OR object_class IS NULL";
                    $parameters[':object_class'] = (int) $object_class->id;
                    break;
                case 'inst':
                case 'fak':
                    if ($object_class_hint) {
                        $object_class = $object_class_hint;
                    } else {
                        $query = "SELECT type FROM Institute WHERE Institut_id = ?";
                        $statement = DBManager::get()->prepare($query);
                        $statement->execute(array($rangeID));
                        $object_class = $statement->fetchColumn();
                    }
                    $object_type = "inst";
                    $clause2 = "object_class = :object_class OR object_class IS NULL";
                    $parameters[':object_class'] = (int) $object_class;
                    break;
                case 'roleinstdata': //hmm tja, vermutlich so
                    $clause2 = '1';
                    break;
                case 'user':
                case 'userinstrole':
                case 'usersemdata':
                    $object_class = is_object($GLOBALS['perm']) ? DataField::permMask($GLOBALS['perm']->get_perm($rangeID)) : 0;
                    $clause2 = "((object_class & :object_class) OR object_class IS NULL)";
                    $parameters[':object_class'] = (int) $object_class;
                    break;
            }
            $query = "SELECT a.*, content
                      FROM datafields AS a
                      LEFT JOIN datafields_entries AS b
                        ON (a.datafield_id = b.datafield_id AND range_id = :range_id {$clause1})
                      WHERE object_type = :object_type AND ({$clause2})
                      ORDER BY priority";
            $parameters[':range_id']    = $rangeID;
            $parameters[':object_type'] = $object_type;

            $rs = DBManager::get()->prepare($query);
            $rs->execute($parameters);

            $entries = [];
            while ($data = $rs->fetch(PDO::FETCH_ASSOC)) {
                $datafield = DataField::buildExisting($data);
                $entries[$data['datafield_id']] = DataFieldEntry::createDataFieldEntry($datafield, $range_id, $data['content']);
            }
        }
        return $entries ?: [];
    }

    /**
     * Removes all datafields from a given range_id (and secondary range
     * id if passed as array)
     *
     * @param mixed $range_id Range id (or array with range id and secondary
     *                        range id)
     * @return int representing the number of deleted entries
     */
    public static function removeAll($range_id)
    {
        if (is_array($range_id)) {
            list ($rangeID, $secRangeID) = $range_id;
        } else {
            $rangeID = $range_id;
            $secRangeID = "";
        }

        if (!$rangeID && !$secRangeID) {
            return;
        }

        $conditions = array();
        $parameters = array();

        if ($rangeID) {
            $conditions[] = 'range_id = ?';
            $parameters[] = $rangeID;
        }
        if ($secRangeID) {
            $conditions[] = 'sec_range_id = ?';
            $parameters[] = $secRangeID;
        }

        $where = implode(' AND ', $conditions);

        return DatafieldEntryModel::deleteBySQL($where, $parameters);
    }

    public $value;
    public $model;
    public $rangeID;

    /**
     * Constructs this datafield
     *
     * @param DataField $datafield Underlying model
     * @param mixed $range_id Range id (or array with range id and secondary
     *                        range id)
     * @param mixed     $value     Value
     */
    public function __construct(DataField $datafield = null, $rangeID = '', $value = null)
    {
        $this->model   = $datafield;
        $this->rangeID = $rangeID;
        $this->value   = isset($value) ? $value : $datafield->default_value;
    }

    /**
     * Stores this datafield entry
     *
     * @return int representing the number of changed entries
     */
    public function store()
    {
        $id = [
            $this->model->id,
            (string) $this->getRangeID(),
            (string) $this->getSecondRangeID(),
            (string) $this->language
        ];
        $entry = new DatafieldEntryModel($id);
        $entry->lang = (string) $this->language;

        $old_value = $entry->content;
        $entry->content = $this->getValue();

        if ($entry->content == $this->model->default_value) {
            $result = $entry->delete();
        } else {
            $result = $entry->store();
        }

        if ($result) {
            NotificationCenter::postNotification('DatafieldDidUpdate', $this, array(
                'changed'   => $result,
                'old_value' => $old_value,
            ));
        }

        return $result;
    }

    /**
     * Returns whether this datafield is required
     *
     * @return bool indicating whether the datafield is required or not
     */
    public function isRequired()
    {
        return $this->model->is_required;
    }

    /**
     * Returns the description of this datafield
     *
     * @return String containing the description
     */
    public function getDescription()
    {
        return $this->model->description;
    }

    /**
     * Returns the type of this datafield
     *
     * @return string type of entry
     */
    public function getType()
    {
        $class = mb_strtolower(get_class($this));
        return mb_substr($class, 9, mb_strpos($class, 'entry') - 9);
    }

    /**
     * Returns the display/rendered value of this datafield
     *
     * @param bool $entities Should html entities be encoded (defaults to true)
     * @return String containg the rendered value
     */
    public function getDisplayValue($entities = true)
    {
        if ($entities) {
            return htmlReady($this->getValue());
        }
        return $this->getValue();
    }

    /**
     * Returns the value of the datafield
     *
     * @return mixed containing the value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the name of the datafield
     *
     * @return String containing the name
     */
    public function getName()
    {
        return $this->model->name;
    }

    /**
     * Returns the id of the datafield
     *
     * @return String containing the id
     */
    public function getId()
    {
        return $this->model->id;
    }

    /**
     * Returns the according input elements as html for this datafield
     *
     * @param String $name      Name prefix of the associated input
     * @param Array  $variables Additional variables
     * @return String containing the required html
     */
    public function getHTML($name = '', $variables = array())
    {
        $variables = array_merge(array(
            'name'  => $name,
            'entry' => $this,
            'model' => $this->model,
            'value' => $this->getValue(),
        ), $variables);

        return $GLOBALS['template_factory']->render('datafields/' . $this->template, $variables);
    }

    /**
     * Sets the value
     *
     * @param mixed $value The value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Sets the value from a post request
     *
     * @param mixed $submitted_value The value from request
     */
    public function setValueFromSubmit($submitted_value)
    {
        $this->setValue($submitted_value);
    }

    /**
     * Sets the range id
     *
     * @param String $range_id Range id
     */
    public function setRangeID($range_id)
    {
        $this->rangeID = $range_id;
    }

    /**
     * Sets the secondary range id
     *
     * @param String $sec_range_id Secondary range id
     */
    public function setSecondRangeID($sec_range_id)
    {
        $this->rangeID = array($this->getRangeID(), $sec_range_id);
    }

    /**
     * Sets the prefered content language if this is an i18n datafield.
     *
     * @param string $language The prefered display language
     */
    public function setContentLanguage($language)
    {
        if (!Config::get()->CONTENT_LANGUAGES[$language]) {
            throw new InvalidArgumentException('Language not configured.');
        }

        if ($language == reset(array_keys(Config::get()->CONTENT_LANGUAGES))) {
            $language = '';
        }

        $this->language = $language;
    }

    /**
     * Checks if datafield is empty (was not set)
     *
     * @return bool true if empty, else false
     */
    public function isEmpty()
    {
        return $this->getValue() == '';
    }

    /**
     * Returns whether the datafield contents are valid
     *
     * @return boolean indicating whether the datafield contents are valid
     */
    public function isValid()
    {
        return trim($this->getValue())
            || !$this->model->is_required;
    }

    /**
     * Returns the number of html fields this datafield uses for input.
     *
     * @return int representing the number of html fields
     */
    public function numberOfHTMLFields()
    {
        return 1;
    }

    /**
     * Returns the range id
     *
     * @return String containing the range id
     */
    public function getRangeID()
    {
        if (is_array($this->rangeID)) {
            return reset($this->rangeID);
        }
        return $this->rangeID;
    }

    /**
     * Returns the secondary range id
     *
     * @return String containing the secondary range id
     */
    public function getSecondRangeID()
    {
        if (is_array($this->rangeID)) {
            list (, $secRangeID) = $this->rangeID;
            return $secRangeID;
        }
        return '';
    }

    /**
     * Returns whether the datafield is visible for the current user
     *
     * @param String $perm Permissions to test against (optional, default to
     *                     the current user's permissions)
     * @param bool $test_ownership Defines whether the ownership of the field
     *                             should be taken into account; a field may
     *                             be invisible for a user according to the
     *                             permissions but since the datafield belongs
     *                             to the user, it is visible.
     * @return boolean indicating whether the datafield is visible
     */
    public function isVisible($perm = null, $test_ownership = true)
    {
        if ($test_ownership) {
            return $this->model->accessAllowed($perm,
                                               $GLOBALS['user']->id,
                                               $this->getRangeID());
        }
        return $this->model->accessAllowed($perm);
    }

    /**
     * Returns whether the datafield is editable for the current user
     *
     * @param mixed $perms Perms to test against (optional, defaults to logged
     *                     in user's perms)
     * @return boolean indicating whether the datafield is editable
     */
    public function isEditable($perms = null)
    {
        return $this->model->editAllowed($perms ?: $GLOBALS['perm']->get_perm());
    }

    /**
     * Returns a human readable string describing the view permissions
     *
     * @return String containing the descriptons of the view permissions
     */
    public function getPermsDescription()
    {
        if ($this->model->view_perms === 'all') {
            return _('sichtbar für alle');
        }
        return sprintf(_('sichtbar nur für Sie und alle %s'),
                       $this->prettyPrintViewPerms());
    }

    /**
     * Generates a full status description depending on the the perms
     *
     * @return string
     */
    protected function prettyPrintViewPerms()
    {
        switch ($this->model->view_perms) {
            case 'all':
                return _('alle');
                break;
            case 'root':
                return _('Systemadministrator/-innen');
                break;
            case 'admin':
                return _('Administrator/-innen');
                break;
            case 'dozent':
                return _('Lehrenden');
                break;
            case 'tutor':
                return _('Tutor/-innen');
                break;
            case 'autor':
                return _('Studierenden');
                break;
            case 'user':
                return _('Nutzer/-innen');
                break;
        }
        return '';
    }

}
