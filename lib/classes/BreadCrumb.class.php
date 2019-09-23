<?php
/**
 * breadcrumb.php - Provides bread crumb navigation
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
class BreadCrumb
{

    /**
     * Array with parts of bread crumb navigation.
     *
     * @var array
     */
    private $trail = [];

    public function __construct()
    {
        URLHelper::bindLinkParam('trail', $this->trail);
    }

    /**
     * Appends a new element to the end of the bread crumb navigation.
     *
     * @param MvvTreeItem $object The MvvTreeItem object of the current view
     * to append.
     * @param string $action The url to the current view.
     */
    public function append($object, $action)
    {
        $trail = $this->getTrail();
        if (is_object($object)) {
            $type = get_class($object);
            $id = $object->id;
            $trail[$type] = [
                'id' => $object->id,
                'actn' => $action
            ];
        } else if (is_array($object)) {
            $id = reset($object)->id;
            $type = get_class(reset($object));
            foreach ($object as $obj) {
                if ($obj && $obj->id != $id) {
                    $additional_objects[get_class($obj)] = $obj->id;
                }
            }
            $trail[$type] = [
                'id' => $id,
                'add' => $additional_objects,
                'actn' => $action
            ];
        } else {
            $trail[$action] = [
                'name' => $object,
                'actn' => $action
            ];
        }
        $newTrail = [];
        $lastElement = false;
        foreach ($trail as $key => $trail_item) {
            if ($lastElement) break;
            $newTrail[$key] = $trail_item;
            $lastElement = $key === $id;
        }
        $this->trail = $newTrail;
    }

    /**
     * Removes the last element from the bread crumb navigation.
     */
    public function pop()
    {
        array_pop($this->trail);
    }

    /**
     * Returns all elements of the bread crumb navigation.
     *
     * @return array All elements of the bread crumb navigation
     */
    public function getTrail()
    {
        return $this->trail;
    }

    /**
     * Initialize a new bread crumb navigation.
     */
    public function init()
    {
        $this->trail = [];
    }

}
