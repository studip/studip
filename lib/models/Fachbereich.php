<?php
/*
 * Fachbereich.php
 * model class for Fachbereiche (aka institutes)
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

class Fachbereich extends ModuleManagementModelTreeItem
{

    private $count_objects;
    private $count_module;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'Institute';

        $config['additional_fields']['count_objects']['get'] =
            function($fb) { return $fb->count_objects; };
        $config['additional_fields']['count_module']['get'] =
            function($fb) { return $fb->count_module; };

        $config['i18n_fields']['name'] = true;
        
        parent::configure($config);
    }

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name = _('Verantwortliche Einrichtung');
    }

    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Verantwortliche Einrichtung');
    }

    /**
     * Retrieves all Fachbereiche which are implicitly related to the given
     * modules. The relation is done through the hole MVV structure. If an
     * object has a status field, the status has to be public. Otherwise the
     * related Fachbereich will not be retrieved.
     *
     * @param array $module_ids An array of module ids.
     */
    public static function findByModule($module_ids)
    {
        $query = 'SELECT COUNT(DISTINCT modul_id) as count_module, inst.* '
                . 'FROM Institute inst '
                . 'INNER JOIN mvv_fach_inst mfi USING(Institut_id) '
                . 'INNER JOIN mvv_stgteil USING(fach_id) '
                . 'INNER JOIN mvv_stgteilversion msv USING(stgteil_id) '
                . 'INNER JOIN mvv_stg_stgteil USING(stgteil_id) '
                . 'INNER JOIN mvv_studiengang ms USING(studiengang_id) '
                . 'INNER JOIN mvv_stgteilabschnitt USING(version_id) '
                . 'INNER JOIN mvv_stgteilabschnitt_modul USING(abschnitt_id)'
                . 'INNER JOIN mvv_modul mm USING(modul_id) '
                . 'WHERE msv.stat IN(?) '
                . 'AND ms.stat In (?) AND mm.stat IN(?) '
                . 'GROUP BY Institut_id';
        $params = [StgteilVersion::getPublicStatus(),
                Studiengang::getPublicStatus(), Modul::getPublicStatus()];
        return parent::getEnrichedByQuery($query, $params);
    }

    /**
     * Retrieves all modules this Fachbereich is related to. The relation is
     * done through the hole MVV structure. Optional filtered by given module
     * ids.
     *
     * @param boolean $only_public If true, all objects with a status field has to
     * be public.
     * @param array $modul_ids An array with module ids. Only these modules will
     * be retrieved.
     * @return array An array with module ids.
     */
    public function getRelatedModules($only_public = true, $modul_ids = null)
    {
        if ($only_public) {
            $query = 'SELECT DISTINCT modul_id FROM mvv_fach_inst mfi '
                    . 'INNER JOIN mvv_stgteil USING(fach_id) '
                    . 'INNER JOIN mvv_stgteilversion msv USING(stgteil_id) '
                    . 'INNER JOIN mvv_stg_stgteil USING(stgteil_id) '
                    . 'INNER JOIN mvv_studiengang ms USING(studiengang_id) '
                    . 'INNER JOIN mvv_stgteilabschnitt USING(version_id) '
                    . 'INNER JOIN mvv_stgteilabschnitt_modul USING(abschnitt_id)'
                    . 'INNER JOIN mvv_modul mm USING(modul_id) '
                    . 'WHERE mfi.institut_id = ? AND msv.stat IN(?) '
                    . 'AND ms.stat In (?) AND mm.stat IN(?)';
            $params = [$this->getId(), StgteilVersion::getPublicStatus(),
                    Studiengang::getPublicStatus(), Modul::getPublicStatus()];
        } else {
            $query = 'SELECT DISTINCT modul_id FROM mvv_fach_inst '
                    . 'INNER JOIN mvv_stgteil USING(fach_id) '
                    . 'INNER JOIN mvv_stgteilversion msv USING(stgteil_id) '
                    . 'INNER JOIN mvv_stgteilabschnitt USING(version_id) '
                    . 'INNER JOIN mvv_stgteilabschnitt_modul USING(abschnitt_id)'
                    . 'INNER JOIN mvv_modul mm USING(modul_id) '
                    . 'WHERE institut_id = ? ';
            $params = [$this->getId()];
        }
        if ($modul_ids) {
            $query .= ' AND mm.modul_id IN (?)';
            $params[] = $modul_ids;
        }
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        if ($this->isFaculty()) {
            return 'root';
        } else {
            $this->getValue('fakultaets_id');
        }
    }

    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        if ($this->isFaculty()) {
            return new MvvTreeRoot();
        } else {
            return new Fachbereich($this->getValue('fakultaets_id'));
        }
    }

    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
        return [];
    }

    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        $_SESSION['MVV/AbschlussKategorie/trail_parent_id'] =  $this->getId();
        return AbschlussKategorie::getEnrichedByQuery('SELECT mak.* '
            . 'FROM Institute ins '
            . 'INNER JOIN mvv_studiengang ms ON ins.Institut_id = ms.institut_id '
            . 'INNER JOIN mvv_abschl_zuord USING(abschluss_id) '
            . 'INNER JOIN mvv_abschl_kategorie mak USING(kategorie_id) '
            . 'WHERE ins.Institut_id = ? OR ins.fakultaets_id = ? '
            . 'ORDER BY mak.name', [$this->getId(),
                $this->getValue('fakultaets_id')]);

    }

    /**
     * @see MvvTreeItem::hasChildren()
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }

    public function getDisplayName($options = self::DISPLAY_DEFAULT)
    {
        if ($this->isFaculty()) {
            return $this->getValue('Name');
        }
        if ($options & self::DISPLAY_FACULTY) {
            return (Fachbereich::get($this->getValue('fakultaets_id'))->getShortName()
                . ' - ' . $this->name);
        }
        return ($this->name);
    }

    /**
     * Returns whether this Fachbereich is a faculty.
     *
     * @return boolean True, if the Fachbereich is a faculty.
     */
    public function isFaculty()
    {
        return $this->getId() == $this->getValue('fakultaets_id');
    }

    public static function getFilterStudiengaengeEinrichtung($studiengang_ids = [])
    {
        return parent::getEnrichedByQuery('SELECT Institute.*, '
                . 'COUNT(mvv_studiengaenge.studiengang_id) '
                . 'AS `count_studiengang`, Institute.Institut_id AS `institut_id`, '
                . 'Institute.Name AS `name`, fak.Name AS `fak_name` '
                . 'FROM mvv_studiengang ms '
                . 'LEFT JOIN Institute inst '
                . 'ON (ms.institut_id = inst.Institut_id) '
                . 'RIGHT OUTER JOIN Institute fak '
                . 'ON (inst.fakultaets_id = fak.Institut_id) '
                . 'WHERE fak.Institut_id = fak.fakultaets_id '
                . 'AND fak.Institut_id = inst.fakultaets_id '
                . parent::getFilterSql(
                        ['mvv_studiengang.studiengang_id'
                            => $studiengang_ids])
                . 'GROUP BY inst.Institut_id '
                . 'ORDER BY is_fak DESC, fak_name ASC, inst_name ASC');
    }

    /**
     * Returns a collection of all Fachbereiche where a
     * Studiengangteil is assigned to.
     *
     * @param string $sortby The result is sorted by these fields.
     * @param string $order The direction of sorting.
     * @return Object SimplORMapCollection of all Fachbereiche
     */
    public static function getFachbereiche($sortby = 'name', $order = 'ASC',
            $filter = null)
    {
        $sortby = self::createSortStatement($sortby, $order, 'name',
                words('name count_objects'));
        return Fachbereich::getEnrichedByQuery('SELECT Institute.*, '
                . 'COUNT(DISTINCT studiengang_id) AS count_objects '
                . 'FROM mvv_stg_stgteil '
                . 'INNER JOIN mvv_stgteil USING(stgteil_id) '
                . 'INNER JOIN mvv_fach_inst USING(fach_id) '
                . 'LEFT JOIN Institute ON mvv_fach_inst.institut_id = Institute.Institut_id '
                . parent::getFilterSql($filter, true)
                . 'GROUP BY Institut_id '
                . 'ORDER BY ' . $sortby, []);
    }

    /**
     * Gießener Spezialität: Kurzbezeichnungen für Fakultäten.
     * Returns the short name of the faculty. If short name is not set returns
     * the display name.
     *
     * @return string The (short) name of the faculty.
     */
    public function getShortName()
    {
        // Gießen
        //return $this->jlug_fak ? $this->jlug_fak : $this->getDisplayName();

        return $this->getDisplayName();
    }

}