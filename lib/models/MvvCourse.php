<?php
/**
 * MvvCourse.php
 * Model class for courses in context of MVV
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

class MvvCourse extends ModuleManagementModelTreeItem
{
    
    protected static function configure($config = [])
    {
        $config['db_table'] = 'seminare';
        
        parent::configure($config);
    }
    
    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return ($_SESSION['MVV/MvvCourse/trail_parent_id']);
    }
    
    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        return LvGruppe::get($this->getTrailParent_id());
    }
    
    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        return null;
    }
    
    /**
     * @see MvvTreeItem::hasChildren()
     */
    public function hasChildren()
    {
        return false;
    }
    
    public function getDisplayName($options = self::DISPLAY_DEFAULT)
    {
        $this->getName();
    }
    
    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
       return Lvgruppe::findBySeminar($this->getId());
    }
}