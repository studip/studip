<?php
/**
 * PermissionCondition.class.php
 *
 * All conditions concerning the semester of study in Stud.IP can be specified here.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig <elmar.ludwig@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class PermissionCondition extends UserFilterField
{
    /**
     * @see UserFilterField::__construct
     */
    public function __construct($fieldId = '')
    {
        $this->userDataDbTable = 'auth_user_md5';
        $this->userDataDbField = 'perms';

        parent::__construct($fieldId);

        $this->validValues = [
            'autor' => _('Student/in'),
            'tutor' => _('Tutor/in'),
            'dozent' => _('Lehrende/r')
        ];
    }

    /**
     * Get this field's display name.
     *
     * @return String
     */
    public function getName()
    {
        return _('Globaler Status');
    }
}
