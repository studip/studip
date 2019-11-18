<?php
/**
 * DatafieldCondition.class.php
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class DatafieldCondition extends UserFilterField
{
    public static $isParameterized = true;

    public $datafield_id, $null_yields, $datafield_name;

    public static function getParameterizedTypes()
    {
        $ret = [];
        try {
            foreach (DataField::findBySQL("object_type='user' AND (object_class & (1|2|4|8) OR object_class IS NULL) AND is_userfilter = 1 ORDER BY priority") as $df) {
                $ret[__CLASS__ . '_' . $df->id] = utf8_encode(chr(160)) . _("Datenfeld") . ': ' . $df->name;
            }
        } catch (PDOException $e) {} //migration 128 chokes on this...
        return $ret;
    }
    /**
     * @see UserFilterField::__construct
     */
    public function __construct($typeparam, $fieldId = '')
    {
        $this->validCompareOperators = [
            '>=' => _('mindestens'),
            '<=' => _('höchstens'),
            '=' => _('ist'),
            '!=' => _('ist nicht')
        ];
        if ($fieldId) {
            $this->id = $fieldId;
            $this->load();
        } else {
            $this->id = $this->generateId();
            $this->datafield_id = $typeparam;
        }

        $df = DataField::find($this->datafield_id);
        if ($df) {
            $this->datafield_name = $df->name;
        } else {
            throw new UnexpectedValueException('datafield not found, id: ' . $typeparam);
        }
        $typed_df = DataFieldEntry::createDataFieldEntry($df);
        if ($typed_df instanceof DataFieldBoolEntry) {
            $this->validValues = [1 => _('Ja'), 0 => _('Nein')];
            unset($this->validCompareOperators['>=']);
            unset($this->validCompareOperators['<=']);
            unset($this->validCompareOperators['!=']);
            $this->null_yields = 0;
        } else if ($typed_df instanceof DataFieldSelectboxEntry) {
            list($valid_values, $is_assoc) = $typed_df->getParameters();
            if (!$is_assoc) {
                $valid_values = array_combine($valid_values, $valid_values);
            }
            $this->validValues = $valid_values;
            $this->null_yields = $typed_df instanceof DataFieldSelectboxMultipleEntry ? '' : key($valid_values);
        } else {
            $this->null_yields = '';
        }

    }

    /**
     * Get this field's display name.
     *
     * @return String
     */
    public function getName()
    {
        return $this->datafield_name;
    }

    public function getUsers($restrictions = [])
    {
        $db = DBManager::get();
        $users = [];
        // Standard query getting the values without respecting other values.
        $select = "SELECT user_id FROM
                    auth_user_md5 LEFT JOIN
                  datafields_entries ON range_id = user_id AND datafield_id = ?
                  WHERE perms IN ('user','autor','tutor','dozent') AND IFNULL(content, ?)
                  " . $this->compareOperator . " ?";
        $users = $db->fetchFirst($select, [$this->datafield_id, $this->null_yields,$this->value]);
        return $users;
    }

    /**
     * Gets the value for the given user that is relevant for this
     *
     * @param  String $userId User to check.
     * @param  Array additional conditions that are required for check.
     * @return The value(s) for this user.
     */
    public function getUserValues($userId, $additional = null)
    {
        $result = DBManager::get()->fetchColumn(
            "SELECT content FROM datafields_entries
            WHERE datafield_id = ? AND range_id = ?", [$this->datafield_id, $userId]);
        return [$result === null || $result === false ? $this->null_yields : $result];
    }

    /**
     * Helper function for loading data from DB.
     */
    public function load()
    {
        $stmt = DBManager::get()->prepare(
            "SELECT * FROM `userfilter_fields` WHERE `field_id`=? LIMIT 1");
        $stmt->execute([$this->id]);
        if ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->conditionId = $data['filter_id'];
            $this->value = $data['value'];
            $this->compareOperator = $data['compare_op'];
            list(,$this->datafield_id) = explode('_', $data['type']);
        }
    }

    /**
     * Sets a new selected value.
     *
     * @param  String newValue
     * @return UserFilterField
     */
    public function setValue($newValue)
    {
        $this->value = $newValue;
        return $this;
    }

    /**
     * Stores data to DB.
     *
     */
    public function store() {
        // Generate new ID if field entry doesn't exist in DB yet.
        if (!$this->id) {
            $this->id = $this->generateId();
        }
        // Store field data.
        $stmt = DBManager::get()->prepare("INSERT INTO `userfilter_fields`
            (`field_id`, `filter_id`, `type`, `value`, `compare_op`,
            `mkdate`, `chdate`)  VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE `filter_id`=VALUES(`filter_id`),
            `type`=VALUES(`type`),`value`=VALUES(`value`),
            `compare_op`=VALUES(`compare_op`), `chdate`=VALUES(`chdate`)");
        $stmt->execute([$this->id, $this->conditionId, get_class($this).'_'.$this->datafield_id,
            $this->value, $this->compareOperator, time(), time()]);
    }
}
