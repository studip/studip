<?php
/**
 * SemesterOfStudyCondition.class.php
 *
 * All conditions concerning the semester of study in Stud.IP can be specified here.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class SemesterOfStudyCondition extends UserFilterField
{
    // --- ATTRIBUTES ---
    public $valuesDbTable = 'user_studiengang';
    public $valuesDbIdField = 'semester';
    public $valuesDbNameField = 'semester';
    public $userDataDbTable = 'user_studiengang';
    public $userDataDbField = 'semester';

    // --- OPERATIONS ---

    /**
     * @see UserFilterField::__construct
     */
    public function __construct($fieldId='')
    {
        parent::__construct($fieldId);
        $this->validValues = [];
        $this->relations = [
            'DegreeCondition' => [
                'local_field' => 'abschluss_id',
                'foreign_field' => 'abschluss_id'
            ],
            'SubjectCondition' => [
                'local_field' => 'fach_id',
                'foreign_field' => 'fach_id'
            ]
        ];
        $this->validCompareOperators = [
            '>=' => _('mindestens'),
            '<=' => _('höchstens'),
            '=' => _('ist'),
            '!=' => _('ist nicht')
        ];
        // Initialize to some value in case there are no semester numbers.
        $maxsem = 15;
        // Calculate the maximal available semester.
        $query = "SELECT MAX(`{$this->valuesDbIdField}`) AS maxsem
                  FROM `{$this->valuesDbTable}`";
        $stmt = DBManager::get()->query($query);
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($current['maxsem']) {
                $maxsem = $current['maxsem'];
            }
        }
        for ($i = 1; $i <= $maxsem; $i++) {
            $this->validValues[$i] = $i;
        }
    }

    /**
     * Get this field's display name.
     *
     * @return String
     */
    public function getName()
    {
        return _('Fachsemester');
    }

}
