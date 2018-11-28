<?php
/**
 * MvvTreeItem.php
 * Interface for tree and path view of MVV objects. All MVV objects used in
 * tree- and path-views have to implement this interface.
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

interface MvvTreeItem
{
    /**
     * Returns a trail even if the object is not related to the root item.
     */
    const TRAIL_SHOW_INCOMPLETE = 1;
    
     /**
     * Returns the parent of the last selected item.
     * Used by assignnment of LV-Gruppen to courses.
     * 
     * @return string|null Id of object, null if object has no parent.
     */
    public function getTrailParentId();
    
    /**
     * Returns the parent of this object in the specific trail.
     * 
     * @return object The parent object, null if object has no parent.
     */
    public function getTrailParent();
    
    /**
     * Returns all children of this object.
     * 
     * @return SimplORMapCollection
     */
    public function getChildren();
    
    /**
     * Returns whether this object has children.
     * 
     * @return boolean
     */
    public function hasChildren();
    
    /**
     * Returns the id of this object.
     * 
     * @return string
     */
    public function getId();
    
    /**
     * Returns the name of the object to displa in the trail.
     * 
     * @return string
     */
    public function getDisplayName();
    
    /**
     * Returns whether this object is assignable to courses. Only used by
     * selecting and asssigning LV-Gruppen to courses. 
     * 
     */
    public function isAssignable();
    
    /**
     * Returns all parents of this object.
     * 
     * @param int $mode Parameter to modify search result.
     */
    public function getParents($mode);
    
    /**
     * Returns all trails (pathes through the mvv object structure) for one object.
     * The trails are the relations of this object along the given types
     * (classes of MvvTreeItem objects) to the root object type. 
     * 
     * @param array $types An array of class names.
     * @param int $mode Modifeies the result (only possible value
     * is MvvTreeItem::TRAIL_SHOW_INCOMPLETE)
     * @param array $path The uncomplete path (used in recursion)
     * @param boolean $last True if the last object (no parents) is reached
     * (used in recursion)
     * @return array An Array of trails. Each trail is an array of objects with
     * the class names as keys. Each trail consists of the objects defined by the
     * types parameter and have the same order as the types array.
     */
    public function getTrails($types = null, $mode = null, $path = null, $in_recursion = false);
    
}