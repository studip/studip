<?php
/**
 * MvvTreeRoot.php
 * The root element of a path or tree of MVV objects. This is the root of the
 * tree or the end of a path.
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

class MvvTreeRoot implements MvvTreeItem
{

    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return null;
    }

    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        return null;
    }

    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        $institute = [];

        $query = "SELECT DISTINCT inst.fakultaets_id
                  FROM mvv_modul_inst mmi
                  JOIN mvv_modul USING (modul_id)
                  JOIN mvv_modulteil USING (modul_id)
                  JOIN mvv_lvgruppe_modulteil USING (modulteil_id)
                  JOIN mvv_studiengang USING (institut_id)
                  LEFT JOIN Institute inst USING (institut_id)
                  LEFT JOIN Institute fak ON (fak.institut_id = inst.fakultaets_id)
                  WHERE mmi.gruppe = ?
                    AND mvv_studiengang.stat IN (?)
                  ORDER BY fak.name ASC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(['hauptverantwortlich', Studiengang::getPublicStatus()]);
        foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $faculty_id) {
            $institute[$faculty_id] = new Fachbereich($faculty_id);
        }
        return $institute;
    }

    /**
     * @see MvvTreeItem::hasChildren()
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }

    public function getDisplayName()
    {
        return Config::get()->UNI_NAME_CLEAN;
    }

    public function getId()
    {
        return 'root';
    }

    /**
     * @see MvvTreeItem::isAssignable()
     */
    public function isAssignable()
    {
        return false;
    }

    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
        return [];
    }

    /**
     * @see MvvTreeItem::getTrails()
     */
    public function getTrails($types = null, $mode = null, $path = null, $in_recursion = false)
    {
        $path = $path ?: ModuleManagementModelTreeItem::$TRAIL_DEFAULT;
        $types = $types ?: $path;
        $trails = [];
        $class_name = get_class($this);
        $next = $path[array_search($class_name, $path) + 1];
        $parents = $this->getParents($next);

        foreach ($parents as $parent) {
            if ($parent) {
                foreach ($parent->getTrails($types, $mode, $path, true) as $trail) {
                    if (in_array($class_name, $types)) {
                        $trail[$class_name] = $this;
                    }
                    if (!$in_recursion) {
                        if (($mode & MvvTreeItem::TRAIL_SHOW_INCOMPLETE)
                            || count($trail) == count($types)) {
                            $trails[] = $trail;
                        }
                    } else {
                        $trails[] = $trail;
                    }
                }
            }
        }

        if (empty($trails) && in_array($class_name, $types)) {
            $trails = [[$class_name => $this]];
        }

        return $trails;
    }

}
