<?php
/**
 * ModulUser.php
 * Model class for assignments of users to modules (table mvv_modul_user)
 *
* This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

class ModulUser extends ModuleManagementModel
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_modul_user';

        $config['belongs_to']['modul'] = [
            'class_name' => 'Modul',
            'foreign_key' => 'modul_id'
        ];
        $config['belongs_to']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id'
        ];

        parent::configure($config);
    }

    /**
     * Retrieves all users assigned to the given module. Optionally filtered
     * by a group. See mvv_config.php for valid groups.
     *
     * @param string $modul_id The id of a module.
     * @param string $group The key of the group.
     * @return SimpleORMapCollection A collection of user assignments (ModulUser)
     */
    public static function findByModul($modul_id, $group = null)
    {
        $users = [];
        $params = is_null($group) ? [$modul_id]
                : [$modul_id, $group];
        foreach (parent::getEnrichedByQuery('SELECT mmu.* '
                . 'FROM mvv_modul_user mmu '
                . 'JOIN auth_user_md5 aum USING (user_id) '
                . 'WHERE mmu.modul_id = ? '
                . (is_null($group) ? '' : 'AND gruppe = ? ')
                . 'ORDER BY gruppe, position, mkdate '
                , $params) as $user) {
            $users[$user->gruppe][$user->user_id] = $user;
        }
        return $users;
    }

    public function validate()
    {
        $ret = parent::validate();
        if (!$this->user || $this->user->isNew()) {
            throw new InvalidValuesException(_('Unbekannter Nutzer'),
                    'assigned_user');
        }
        return $ret;
    }

    /**
     * Inherits the status of the parent module.
     *
     * @return string The status (see mvv_config.php)
     */
    public function getStatus()
    {
        $modul = Modul::find($this->modul_id);
        if ($modul) {
            return $modul->getStatus();
        } elseif ($this->isNew()) {
            return $GLOBALS['MVV_MODUL']['STATUS']['default'];
        }
        return parent::getStatus();
    }
}
