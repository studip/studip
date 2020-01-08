<?php
/**
 * StgteilVersionCondition.class.php
 *
 * All conditions concerning the Studiengangteil-Versionen in Stud.IP can be specified here.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class StgteilVersionCondition extends UserFilterField
{
    // --- ATTRIBUTES ---
    public $valuesDbTable = 'mvv_stgteilversion';
    public $valuesDbIdField = 'version_id';
    public $valuesDbNameField = 'code';
    public $userDataDbTable = 'user_studiengang';
    public $userDataDbField = 'version_id';

    public static $isParameterized = true;

    public static function getParameterizedTypes()
    {
        if (Config::get()->DISPLAY_STGTEILVERSION_USERFILTER) {
            $filter = new StgteilVersionCondition;
            $fields['StgteilVersionCondition'] = $filter->getName();
            return $fields;
        } else {
            return [];
        }
    }

    /**
     * @see UserFilterField::__construct
     */
    public function __construct($fieldId = '')
    {
        $this->validCompareOperators = [
            '=' => _('ist'),
            '!=' => _('ist nicht')
        ];
        if ($this->valuesDbNameField) {
            // Get all available values from database.
            $stmt = DBManager::get()->query(
                "SELECT DISTINCT `version_id`, `fach`.`name` ".
                 "FROM `mvv_stgteilversion` LEFT JOIN mvv_stgteil USING (stgteil_id)".
                 "LEFT JOIN fach USING (fach_id)".
                 "WHERE `mvv_stgteilversion`.`stat` = 'genehmigt' ORDER BY `fach`.`name` ASC");

            while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->validValues[$current[$this->valuesDbIdField]] = $current[$this->valuesDbNameField];
            }
        }
        if ($fieldId) {
            $this->id = $fieldId;
            $this->load();
        } else {
            $this->id = $this->generateId();
        }

        foreach ($this->validValues as $version_id => $name) {
            $stgteilversion = StgteilVersion::find($version_id);
            $this->validValues[$version_id] = $stgteilversion->getDisplayname();
        }
    }

    /**
     * Get this field's display name.
     *
     * @return String
     */
    public function getName()
    {
        return _('Studiengangteil-Version');
    }
}
