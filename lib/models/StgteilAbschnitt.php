<?php
/**
 * StgteilAbschnitt.php
 * Model class for Studiengangsabschnitte (table mvv_stgteilabschnitt)
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

class StgteilAbschnitt extends ModuleManagementModelTreeItem
{
    
    private $count_module;
    
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_stgteilabschnitt';
    
        $config['has_and_belongs_to_many']['module'] = [
            'class_name' => 'Modul',
            'thru_table' => 'mvv_stgteilabschnitt_modul',
            'thru_key' => 'abschnitt_id',
            'thru_assoc_key' => 'modul_id',
            'order_by' => 'ORDER BY position,mkdate'
        ];
        $config['belongs_to']['version'] = [
            'class_name' => 'StgteilVersion',
            'foreign_key' => 'version_id'
        ];
        $config['has_many']['modul_zuordnungen'] = [
            'class_name' => 'StgteilabschnittModul',
            'assoc_foreign_key' => 'abschnitt_id',
            'order_by' => 'ORDER BY position,mkdate',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        $config['has_many']['modulteil_abschnitte'] = [
            'class_name' => 'ModulteilStgteilabschnitt',
            'assoc_foreign_key' => 'abschnitt_id',
            'order_by' => 'ORDER BY fachsemester',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        
        $config['additional_fields']['count_module']['get'] =
            function($fach) { return $fach->count_module; };
        $config['additional_fields']['count_module']['set'] = false;
        
        $config['i18n_fields']['name'] = true;
        $config['i18n_fields']['kommentar'] = true;
        $config['i18n_fields']['ueberschrift'] = true;
        
        parent::configure($config);
    }
    
    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return ($long ? _('Studiengangteil-Abschnitt')
            : _('Abschnitt'));
    }
    
    public static function getEnriched($abschnitt_id)
    {
        $abschnitte = parent::getEnrichedByQuery(
                'SELECT msa.*, COUNT(modul_id) AS count_module '
                . 'FROM mvv_stgteilabschnitt msa '
                . 'LEFT JOIN mvv_stgteilabschnitt_modul msm USING(abschnitt_id) '
                . 'WHERE msa.abschnitt_id = ? '
                . 'GROUP BY abschnitt_id',
                [$abschnitt_id]);
        if (sizeof($abschnitte)) {
            return $abschnitte[$abschnitt_id];
        }
        return self::get();
    }
    
    /**
     * Retrieves all Studienganteil-Abschnitte for the given
     * Studienganteil-Version.
     * 
     * @param type $version_id The id of a Studiengangteil-Version.
     * @return SimpleORMapCollection A collection of Studiengangteil-Abschnitte.
     */
    public static function findByStgteilVersion($version_id)
    {
        return parent::getEnrichedByQuery('SELECT msa.*, '
                . 'COUNT(modul_id) AS count_module '
                . 'FROM mvv_stgteilabschnitt msa '
                . 'LEFT JOIN mvv_stgteilabschnitt_modul msm USING(abschnitt_id) '
                . 'WHERE msa.version_id = ? '
                . 'GROUP BY abschnitt_id '
                . 'ORDER BY position, chdate', [$version_id]);
    }
    
    /**
     * Retrieves all Studienganteil-Abschnitte the given Modul is assigned to.
     * 
     * @param type $modul_id The id of a Modul.
     * @return SimpleORMapCollection A collection of Studiengangteil-Abschnitte.
     */
    public static function findByModul($modul_id)
    {
        return parent::getEnrichedByQuery('SELECT msa.* '
                . 'FROM mvv_stgteilabschnitt msa '
                . 'LEFT JOIN mvv_stgteilabschnitt_modul msm USING(abschnitt_id) '
                . 'WHERE msm.modul_id = ? '
                . 'ORDER BY position, chdate', [$modul_id]);
    }
    
    /**
     * Assignes a Modul to this Studiengangteil-Abschnitt.
     * 
     * @param type $modul The id of a Modul or the Modul as object.
     * @return boolean True if the assignemnt was successful.
     */
    public function addModul($modul)
    {
        if (is_object($modul)) {
            if (!($modul instanceof Modul) || $modul->isNew()) {
                return false;
            }
        } else {
            $modul = Modul::find($modul);
            if (!$modul) return false;
        }
        $abschnitt_modul = StgteilabschnittModul::findBySQL(
                'abschnitt_id = ? AND modul_id = ?', [$this->id, $modul->id]);
        if (!$abschnitt_id) {
            $abschnitt_modul = new StgteilabschnittModul();
            $abschnitt_modul->abschnitt_id = $this->id;
            $abschnitt_modul->modul_id = $modul->id;
        }
        if (!$this->modul_zuordnungen) {
            $this->modul_zuordnungen = SimpleORMapCollection::createFromArray(
                    [$abschnitt_modul]);
        } else {
            $this->modul_zuordnungen->append($abschnitt_modul);
        }
        return true;
    }
    
    /**
     * Removes (deletes the assignment of) a Modul to this Studiengangteil-
     * Abschnitt.
     * 
     * @param type $modul The id of a Modul or the Modul as object.
     * @return boolean True if the Modul was successfully removed.
     */
    public function removeModul($modul)
    {
        if (is_object($modul)) {
            if (!($modul instanceof Modul) || $modul->isNew()) {
                return false;
            }
        } else {
            $modul = Modul::find($modul);
            if (!$modul) return false;
        }
        if (!$this->modul_zuordnungen) {
            return false;
        }
        $modul_zuordnung = StgteilabschnittModul::findOneBySQL(
                'abschnitt_id = ? AND modul_id = ?',
                [$this->id, $modul->id]);
        $removed = $this->modul_zuordnungen->unsetByPk($modul_zuordnung->id);
        return $removed !== false;
    }
    
    /**
     * Returns the Version this Studiengangteilabschnitt is assigned to.
     * 
     * @return null|object The Version.
     */
    public function getVersion()
    {
        return StgteilVersion::findByStgteilAbschnitt($this->getId());
    }
    
    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return $this->version_id;
    }

    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        return $this->getVersion();
    }
    
    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
        return [StgteilVersion::findByStgteilAbschnitt($this->getId())];
    }
    
    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        $_SESSION['MVV/Modul/trail_parent_id'] =  $this->getId();
        $filter = ['mvv_modul.stat' => Modul::getPublicStatus()];
        return Modul::findByStgteilAbschnitt($this->getId(), $filter);
    }

    /**
     * Returns all assignments of Module to this Studiengangteil-Abschnitt.
     * 
     * @return SimpleORMapCollection A collection of Module.
     */
    public function getModulAssignments()
    {
        return $this->modul_zuordnungen;
    }
    
    /**
     * Inherits the status of the parent version.
     * 
     * @return string the status of parent version
     */
    public function getStatus()
    {
        if ($this->getVersion()) {
            return $this->getVersion()->getStatus();
        } elseif ($this->isNew()) {
            return $GLOBALS['MVV_STGTEILVERSION']['STATUS']['default'];
        }
        return parent::getStatus();
    }
    
    /**
     * Returns the responsible institutes.
     * Inherits the responsible institutes from Studiengangteil-Version
     * 
     * @return array Array of institute objects.
     */
    public function getResponsibleInstitutes()
    {
        if ($this->version) {
            return $this->version->getResponsibleInstitutes();
        } else {
            //In case no responsible institutes can be
            //determined we must return an empty array:
            return [];
        }
    }
    
}