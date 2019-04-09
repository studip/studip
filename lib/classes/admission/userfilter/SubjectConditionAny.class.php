<?php

/**
 * SubjectConditionAny.class.php
 *
 * All conditions concerning the study subject in Stud.IP can be specified here.
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

require_once realpath(__DIR__ . '/..') . '/UserFilterField.class.php';

class SubjectConditionAny extends UserFilterField
{
    // --- ATTRIBUTES ---
    public $valuesDbTable = 'fach';
    public $valuesDbIdField = 'fach_id';
    public $valuesDbNameField = 'name';
    public $userDataDbTable = 'user_studiengang';
    public $userDataDbField = 'fach_id';

    // --- OPERATIONS ---

    /**
     * @see UserFilterField::__construct
     */
    public function __construct($fieldId = '')
    {
        parent::__construct($fieldId);
        $this->validCompareOperators = [
            '!=' => ' '
        ];
        $this->validValues = ['' => ' '];
    }

    /**
     * Get this field's display name.
     *
     * @return String
     */
    public function getName()
    {
        return _('Alle Studienfächer');
    }

} /* end of class SubjectCondition */