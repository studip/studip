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

    /**
     * @see UserFilterField::__construct
     */
    public function __construct($fieldId = '')
    {
        parent::__construct($fieldId);

        foreach ($this->validValues as $version_id => $name) {
            $stgteilversion = StgteilVersion::find($version_id);
            if (!$stgteilversion || $stgteilversion->stat !== 'genehmigt') {
                unset($this->validValues[$version_id]);
                continue;
            }
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
