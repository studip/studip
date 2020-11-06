<?php
/**
* statusgroups.php - provides functions for statusgroup handling
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License as
* published by the Free Software Foundation; either version 2 of
* the License, or (at your option) any later version.
*
* @author      Thomas Hackl <thomas.hackl@uni-passau.de>
* @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
* @category    Stud.IP
* @since 3.5
*/

class StatusgroupsModel
{

    /**
     * Creates or updates a statusgroup.
     *
     * @param string $id                ID of an existing group or empty if new group
     * @param string $name              group name
     * @param int    $position          position or null if automatic position after other groups
     * @param string $range_id          ID of the object this group belongs to
     * @param int    $size              max number of members or 0 if unlimited
     * @param int    $selfassign        may users join this group by themselves?
     * @param int    $selfassign_start  group joining is possible starting at ...
     * @param int    $makefolder        create a document folder assigned to this group?
     * @param array  $dates             dates assigned to this group
     * @return Statusgruppen The saved statusgroup.
     * @throws Exception
     */
    public static function updateGroup($id, $name, $position, $range_id, $size, $selfassign,
                                       $selfassign_start, $selfassign_end, $makefolder, $dates = [])
    {
        $group = new Statusgruppen($id);

        $group->name = $name;
        $group->position = $position;
        $group->range_id = $range_id;
        $group->size = $size;
        $group->selfassign = $selfassign;
        $group->selfassign_start = $selfassign ? $selfassign_start : 0;
        $group->selfassign_end = $selfassign ? $selfassign_end : 0;

        // Set assigned dates.
        if ($dates) {
            $group->dates = CourseDate::findMany($dates);
        }

        $group->store();

        /*
         * Create document folder if requested (ID is needed here,
         * so we do that after store()).
         */
        if (!$group->hasFolder() && $makefolder) {
            $group->updateFolder(true);
        }

        return $group;
    }

    /**
     * Sorts the members of the given group alphabetically
     * or by the given sort criteria.
     *
     * @param string $group_id   ID of the group to sort
     * @param array  $members    group members
     * @param string $sort_by    optional field to sort members by
     * @param string $order      optional, sort by custom field 'asc' or 'desc'
     *
     * @return array Group members, sorted by given criteria or alphabetically
     */
    public static function sortGroupMembers($members, $sort_by = '', $order = '')
    {
        $sorting = 'nachname asc, vorname asc';
        if ($sort_by && $order) {
            $sorting = $sort_by . ' ' . $order . ', ' . $sorting;
        }

        return $members->orderBy($sorting);
    }

}
