<?php
/**
 * StgteilabschnittModul.php
 * Model class for the relation between Studiengangteil-Abschnitte and Module
 * (table mvv_stgteilabschnitt_modul)
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

class StgteilabschnittModul extends ModuleManagementModelTreeItem
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_stgteilabschnitt_modul';

        $config['belongs_to']['modul'] = [
            'class_name' => 'Modul',
            'foreign_key' => 'modul_id'
        ];
        $config['belongs_to']['abschnitt'] = [
            'class_name' => 'StgteilAbschnitt',
            'foreign_key' => 'abschnitt_id'
        ];
        
        $config['i18n_fields']['bezeichnung'] = true;

        parent::configure($config);
    }

    function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name =
                _('Zuordnung Modul zu Studiengangteil-Abschnitt');
    }

    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return ($long ? _('Zuordnung Modul zu Studiengangteil-Abschnitt')
            : _('Modul'));
    }

    public function getDisplayName($options = self::DISPLAY_DEFAULT)
    {
        $options = ($options !== self::DISPLAY_DEFAULT)
                ? $options : self::DISPLAY_CODE;
        $with_code = $options & self::DISPLAY_CODE;
        if ($this->isNew()) {
            return parent::getDisplayName($options);
        }

        /* Augsburg
        return ($this->bezeichnung ? $this->bezeichnung . ': ' : '')
            . $this->getModul()->getDisplayName();
         *
         */

        $start_sem = Semester::find($this->modul->start);
        $end_sem = Semester::find($this->modul->end);

        $code = trim($this->modulcode) ?: trim($this->modul->code);

        $name = ($with_code && $code) ? $code . ' - ' : '';
        $name .= trim($this->bezeichnung) ?: trim($this->modul->getDeskriptor()->bezeichnung);
        if ($end_sem || $start_sem) {
            if ($end_sem) {
                $name .= sprintf(_(', gültig %s bis %s'),
                        $start_sem->name, $end_sem->name);
            } else {
                $name .= sprintf(_(', gültig ab %s'), $start_sem->name);
            }
        }

        return $name;
    }

    /**
     * Retrieves all Modul assignments to the given Studiengangteil-Abschnitt.
     *
     * @param string $abschnitt_id The id of a Studiengangteil-Abschnitt.
     * @param array $filter An array of filter definitions,
     * see ModuleManagementModel::getFilterSql().
     * @return array Array of Modul assignments.
     */
    public static function findByStgteilAbschnitt($abschnitt_id, $filter)
    {
        return parent::findBySQL('INNER JOIN mvv_modul USING(modul_id) '
         . 'LEFT JOIN semester_data start_sem '
         . 'ON (mvv_modul.start = start_sem.semester_id) '
         . 'LEFT JOIN semester_data end_sem '
         . 'ON (mvv_modul.end = end_sem.semester_id) '
         . 'LEFT JOIN mvv_stgteilabschnitt '
         . 'ON (mvv_stgteilabschnitt.abschnitt_id = mvv_stgteilabschnitt_modul.abschnitt_id) '
         . 'LEFT JOIN mvv_stgteilversion '
         . 'ON (mvv_stgteilabschnitt.version_id = mvv_stgteilversion.version_id) '
         . 'WHERE mvv_stgteilabschnitt_modul.abschnitt_id = ? '
         . self::getFilterSql($filter)
         . ' ORDER BY position, mkdate', [$abschnitt_id]);
    }

    /**
     * Returns the assigned Fachsemester. If the given Fachsemester is not
     * assigned, it returns null.
     *
     * @param string $modulteil_id
     * @param string $fachsemester
     * @return null|object
     */
    public function getFachsemester($modulteil_id, $fachsemester)
    {
        return ModulteilStgteilabschnitt::find(
                [$modulteil_id, $this->abschnitt_id, $fachsemester]);
    }

    /**
     * Returns an array of all assigned Fachsemester.
     *
     * @param string $modulteil_id
     * @return array Array of objects of assigned Fachsemester
     */
    public function getAllFachsemester($modulteil_id)
    {
        $ret = [];
        $modulteil_abschnitte = ModulteilStgteilabschnitt::findBySql(
                'modulteil_id = ' . DBManager::get()->quote($modulteil_id)
                . ' AND abschnitt_id = '
                . DBManager::get()->quote($this->abschnitt_id));
        foreach ($modulteil_abschnitte as $modulteil_abschnitt) {
            $ret[$modulteil_abschnitt->fachsemester] = $modulteil_abschnitt;
        }
        return $ret;
    }

    /**
     * @see SimpleORMap::delete();
     */
    public function delete()
    {
        foreach ($this->modul->modulteile as $modulteil) {
            ModulteilStgteilabschnitt::deleteBySql(
                    'modulteil_id = '
                    . DBManager::get()->quote($modulteil->getId())
                    . ' AND abschnitt_id = '
                    . DBManager::get()->quote($this->abschnitt_id));
        }

        return parent::delete();
    }

    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return $this->abschnitt_id;
    }

    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        /* Augsburg
        return Abschluss::get($this->getTrailParentId());
         *
         */
        return StgteilAbschnitt::get($this->getTrailParentId());
    }

    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        $_SESSION['MVV/StgteilAbschnitt/trail_parent_id'] =  $this->abschnitt_id;
        $_SESSION['MVV/Modulteil/trail_parent_id'] =  $this->modul_id;
        // return Modulteil::findByModul($this->getId());
        $ret = Lvgruppe::getEnrichedByQuery('SELECT ml.* '
                . 'FROM mvv_lvgruppe ml '
                . 'LEFT JOIN mvv_lvgruppe_modulteil USING(lvgruppe_id) '
                . 'LEFT JOIN mvv_modulteil USING(modulteil_id) '
                . 'WHERE modul_id = ? ', [$this->modul_id]);
        return $ret;
    }

    /**
     * @see MvvTreeItem::hasChildren()
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }

    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
        return [StgteilAbschnitt::find($this->abschnitt_id)];
    }

     /**
     * Inherits the status of the parent StgteilAbschnitt.
     *
     * @return string the status of parent StgteilAbschnitt
     */
    public function getStatus()
    {
        // workaround to copy module with assignments to Studiengangteil-Version
        // check first whether it is new
        if ($this->isNew()) {
            return $GLOBALS['MVV_STGTEILVERSION']['STATUS']['default'];
        }
        if ($this->abschnitt) {
            return $this->abschnitt->getStatus();
        }
        return parent::getStatus();
    }
}