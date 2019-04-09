<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
* ShowList.class.php
*
* creates a list
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module       ShowList.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ShowList.class.php
// erzeugt eine Listenausgabe
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

use Studip\Button,
    Studip\LinkButton;

require_once 'lib/resources/views/ShowTreeRow.class.php';

/*****************************************************************************
ShowList, stellt Liste mit Hilfe von printThread dar
/*****************************************************************************/

class ShowList extends ShowTreeRow{
    var $db;
    var $db2;
    var $recurse_levels;            //How much Levels should the List recurse
    var $supress_hierachy_levels;       //show only resources with a category or show also the hierarhy-levels (that are resources too)
    var $admin_buttons;         //show admin buttons or not

    function __construct() {
        $this->recurse_levels=-1;
        $this->supress_hierachy_levels=FALSE;
        $this->simple_list=FALSE;
    }

    function setRecurseLevels($levels) {
        $this->recurse_levels=$levels;
    }

    function setAdminButtons($value) {
        $this->admin_buttons=$value;
    }

    function setSimpleList($value) {
        $this->simple_list=$value;
    }

    function setViewHiearchyLevels($mode) {
        if ($mode)
            $this->supress_hierachy_levels=FALSE;
        else
            $this->supress_hierachy_levels=TRUE;
    }

    //private
    function showListObject ($resource_id, $admin_buttons=FALSE) {
        global $edit_structure_object,
            $user, $perm, $clipObj, $view_mode, $view;

        //Object erstellen
        $resObject = ResourceObject::Factory($resource_id);

        if (!$resObject->getId())
            return FALSE;

        //link add for special view mode (own window)
        if ($view_mode == "no_nav")
            $link_add = "&quick_view=".$view."&quick_view_mode=".$view_mode;

        if ($this->simple_list){
            //create a simple list intead of printhead/printcontent-design
            $return="<li><a href=\"".URLHelper::getLink('?view=view_details&actual_object='.$resObject->getId().$link_add)."\">".htmlReady($resObject->getName())."</a></li>\n";
            print $return;
        } else {
            //Daten vorbereiten
            if (!$resObject->getCategoryIconnr())
                $icon = Icon::create('folder-full', 'inactive')->asImg(['class' => 'text-top']);
            else
                $icon = Assets::img('cont_res' . $resObject->getCategoryIconnr() . '.gif');

            if ($_SESSION['resources_data']["structure_opens"][$resObject->id]) {
                $link = URLHelper::getLink('?structure_close=' . $resObject->id . $link_add . '#a');
                $open = 'open';
                if ($_SESSION['resources_data']["actual_object"] == $resObject->id)
                    echo '<a name="a"></a>';
            } else {
                $link = URLHelper::getLink('?structure_open=' . $resObject->id . $link_add . '#a');
                $open = 'close';
            }

            $titel='';
            if ($resObject->getCategoryName())
                $titel=$resObject->getCategoryName().": ";
            if ($edit_structure_object == $resObject->id) {
                echo "<a name=\"a\"></a>";
                $titel.="<input style=\"font-size: 8pt; width: 100%;\" type=\"text\" size=20 maxlength=255 name=\"change_name\" value=\"".htmlReady($resObject->getName())."\">";
            } else {
                $titel.=htmlReady($resObject->getName());
            }

            //create a link on the titel, too
            if (($link) && ($edit_structure_object != $resObject->id))
                $titel = "<a href=\"$link\" class=\"tree\" >$titel</a>";

            if ($resObject->getOwnerLink())
                $zusatz=sprintf (_("verantwortlich:")." <a href=\"%s\"><font color=\"#333399\">%s</font></a>", $resObject->getOwnerLink(), htmlReady($resObject->getOwnerName()));
            else
                $zusatz=sprintf (_("verantwortlich:")." %s", htmlReady($resObject->getOwnerName()));

            if ($perm->have_perm('root') || getGlobalPerms($user->id) == "admin"){
                $simple_perms = 'admin';
            } elseif (ResourcesUserRoomsList::CheckUserResource($resObject->getId())){
                $simple_perms = 'tutor';
            } else {
                $simple_perms = false;
            }

            //clipboard in/out
            if ((is_object($clipObj)) && $simple_perms && $resObject->getCategoryId())
                if ($clipObj->isInClipboard($resObject->getId()))
                    $zusatz .= " <a href=\"".URLHelper::getLink('?clip_out='.$resObject->getId().$link_add)."\">" . Icon::create('resources+remove', 'clickable', ['title' => _("Aus der Merkliste entfernen")])->asImg(16, ["alt" => _("Aus der Merkliste entfernen")]) . "</a>";
                else
                    $zusatz .= " <a href=\"".URLHelper::getLink('?clip_in='.$resObject->getId().$link_add)."\">" . Icon::create('resources+add', 'clickable', ['title' => _("In Merkliste aufnehmen")])->asImg(16, ["alt" => _("In Merkliste aufnehmen")]) . "</a>";

            $new=TRUE;

            $edit .= '<div style="text-align: center"><div class="button-group">';

            if ($open == 'open') {
                // check if the edit buttons for admins shell be shown
                if ($admin_buttons && ($simple_perms == "admin")) {
                    $edit .= LinkButton::create(_('Neues Objekt'), URLHelper::getURL('?create_object=' . $resObject->id));
                    if ($resObject->isDeletable()) {
                        $edit .= LinkButton::create(_('Löschen'), URLHelper::getURL('?kill_object=' . $resObject->id));
                    }
                }


                if ($resObject->getCategoryId()) {
                    if (ResourceObject::isScheduleViewAllowed($resObject->getId())) {
                        if ($view_mode == 'no_nav') {
                            $edit .= LinkButton::create(_('Belegung'), URLHelper::getURL('?show_object=' . $resObject->id
                                . '&quick_view=view_schedule&quick_view_mode=' . $view_mode));
                        } else {
                            $edit .= LinkButton::create(_('Belegung'), URLHelper::getURL('?show_object=' . $resObject->id
                                . '&view=view_schedule'));
                        }
                    }
                }
                if ($simple_perms && $resObject->isRoom()) {
                    $edit .= LinkButton::create(_('Benachrichtigung'), UrlHelper::getScriptURL('dispatch.php/resources/helpers/resource_message/' . $resObject->id), ['data-dialog' => '']);
                }
                if ($view_mode == 'no_nav') {
                    $edit .= LinkButton::create(_('Eigenschaften'), URLHelper::getURL('?show_object=' . $resObject->id
                        . '&quick_view=view_details&quick_view_mode=' . $view_mode));
                } else {
                    $edit .= LinkButton::create(_('Eigenschaften'), URLHelper::getURL('?show_object=' . $resObject->id
                        . '&view=view_details'));
                }

                //clipboard in/out
                if (is_object($clipObj) && $simple_perms && $resObject->getCategoryId())
                    if ($clipObj->isInClipboard($resObject->getId())) {
                        $edit .= LinkButton::create(_('Aus Merkliste entfernen'),
                            URLHelper::getURL('?clip_out=' .$resObject->getId() . $link_add));
                    } else {
                        $edit .= LinkButton::create(_('In Merkliste aufnehmen') . ' >',
                            URLHelper::getURL('?clip_in=' .$resObject->getId() . $link_add));
                    }
            }
            $edit .= '</div></div>';
            $content = formatReady($resObject->getDescription());
            //Daten an Ausgabemodul senden
            $this->showRow($icon, $link, $titel, $zusatz, 0, 0, 0, $new, $open, $content, $edit);
        }
        return TRUE;
    }

    function showListObjects ($start_id='', $level=0, $result_count=0)
    {
        //Let's start and load all the threads
        $query = "SELECT resource_id
                  FROM resources_objects AS ro
                  LEFT JOIN resources_categories USING (category_id)
                  WHERE parent_id = ?";
        if ($this->supress_hierachy_levels) {
            $query .= " AND ro.category_id != ''";
        }
        if ($this->show_only_rooms) {
            $query .= " AND is_room = 1";
        }
        $query .= " ORDER BY ro.name";

        $statement = DBManager::get()->prepare($query);
        $statement->execute([$start_id]);
        $resource_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        //if we have an empty result
        if (count($resource_ids) == 0 && $level == 0) {
            return FALSE;
        }

        $query = "SELECT resource_id
                  FROM resources_objects
                  WHERE parent_id = ?
                  ORDER BY name";
        $statement = DBManager::get()->prepare($query);

        foreach ($resource_ids as $resource_id) {
            $this->showListObject($resource_id, $this->admin_buttons);

            //in weitere Ebene abtauchen
            if (($this->recurse_levels == -1) || ($level + 1 < $this->recurse_levels)) {
                //Untergeordnete Objekte laden
                $statement->execute([$resource_id]);

                while ($id = $statement->fetchColumn()) {
                    $this->showListObjects($id, $level + 1, $result_count);
                }
                $statement->closeCursor();
            }
            $result_count += 1;
        }
        return $result_count;
    }

    function showRangeList($range_id) {
        $count = 0;
        require_once "lib/resources/lib/ResourcesOpenObjectGroups.class.php";
        foreach(ResourcesOpenObjectGroups::GetInstance($range_id)->getAllResources() as $resource_id){
            $this->showListObject($resource_id);
            ++$count;
        }
        return $count;
    }

    function showSearchList($search_array, $check_assigns = FALSE)
    {
        //create the query
        if ($search_array['resources_search_range']){
            $search_only = $this->getResourcesSearchRange($search_array['resources_search_range']);
        }

        $parameters = [];
        if ($search_array['properties']) {
            $query = "SELECT a.resource_id, COUNT(a.resource_id) AS resource_id_count
                      FROM resources_objects_properties AS a
                      LEFT JOIN resources_objects AS b USING (resource_id)
                      LEFT JOIN resources_categories USING (category_id)";
            if (!hasGlobalOccupationAccess()) {
                $query .= " LEFT JOIN `resources_user_resources` AS rur ON (rur.`resource_id` = b.`resource_id`)";
            }
            $query .= " WHERE ";

            $conditions = [];
            $i = 0;
            foreach ($search_array['properties'] as $key => $val) {
                // if ($val == 'on') {
                //     $val = 1;
                // }

                //let's create some possible wildcards
                if (mb_strpos($val, '<=') !== false) {
                    $val     = (int) mb_substr($val, mb_strpos($val, '<=') + 2);
                    $linking = '<=';
                } elseif (mb_strpos($val, '>=') !== false) {
                    $val     = (int) mb_substr($val, mb_strpos($val, '>=') + 2);
                    $linking = '>=';
                } elseif (mb_strpos($val, '<') !== false) {
                    $val     = (int) mb_substr($val, mb_strpos($val, '<') + 1);
                    $linking = '<';
                } elseif (mb_strpos($val, '>') !== false) {
                    $val     = (int) mb_substr($val, mb_strpos($val, '>') + 1);
                    $linking = '>';
                } else {
                    $linking = '=';
                }
                $conditions[] = "(property_id = :key{$i} AND state {$linking} :state{$i})";
                $parameters[':key' . $i]   = $key;
                $parameters[':state' . $i] = $val;

                $i += 1;
            }
            $query .= (count($conditions) > 0)
                    ? implode(' OR ', $conditions)
                    : '1';

            $query .= " AND b.name LIKE CONCAT('%', :needle, '%')";
            $parameters[':needle'] = $search_array['search_exp'];

            if ($this->supress_hierachy_levels) {
                $query .= " AND b.category_id != ''";
            }
            if ($this->show_only_rooms) {
                $query .= " AND is_room = 1";
            }
            if ($search_array['resources_search_range']) {
                $query .= " AND b.resource_id IN (:resource_ids)";
                $parameters[':resource_ids'] = $search_only ?: '';
            }

            if (!hasGlobalOccupationAccess()) {
                $query .= " AND (b.`owner_id`=:user OR rur.`user_id`=:user)";
                $parameters[':user'] = $GLOBALS['user']->id;
            }

            $query .= " GROUP BY a.resource_id
                        HAVING resource_id_count = :count";
            $parameters[':count'] = $i;

            $query .=" ORDER BY b.name";
        } else {
            $query = "SELECT resource_id
                      FROM resources_objects AS ro
                      LEFT JOIN resources_categories USING (category_id)";

            if (!hasGlobalOccupationAccess()) {
                $query .= " LEFT JOIN `resources_user_resources` USING (`resource_id`)";
            }
            $query .= " WHERE ro.name LIKE CONCAT('%', :needle, '%')";

            $parameters[':needle'] = $search_array['search_exp'];

            if ($this->supress_hierachy_levels) {
                $query .= " AND ro.category_id != ''";
            }
            if ($this->show_only_rooms) {
                $query .= " AND is_room = 1";
            }
            if ($search_array['resources_search_range']) {
                $query .= " AND ro.resource_id IN (:resource_ids)";
                $parameters[':resource_ids'] = $search_only ?: '';
            }

            if (!hasGlobalOccupationAccess()) {
                $query .= " AND (ro.`owner_id`=:user OR `user_id`=:user)";
                $parameters[':user'] = $GLOBALS['user']->id;
            }

            $query .= " ORDER BY ro.name";
        }

        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        $resource_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        //if we have an empty result
        if (count($resource_ids) == 0 && $level == 0) {
            return FALSE;
        }

        foreach ($resource_ids as $resource_id) {
            $found_resources[$resource_id] = TRUE;
        }
        $day_of_week = false;
        //do further checks to determine free resources inthe given time range
        if ($search_array["search_assign_begin"] && $check_assigns) {
            $multiOverlaps = new CheckMultipleOverlaps;

            // >> changed for advanced search for room administrators
            if ($search_array["search_repeating"])
            {
                // is this slot empty for the rest of the term?
                $semester = SemesterData::getSemesterDataByDate($search_array["search_assign_begin"]);
                // create the dummy assign object
                $assObj = new AssignObject('');
                $assObj->setBegin($search_array["search_assign_begin"]);
                $assObj->setEnd($search_array["search_assign_end"]);
                $assObj->setRepeatEnd($semester["vorles_ende"]);
                $assObj->setRepeatInterval(1);
                $assObj->setRepeatQuantity(-1);

                // calculate stud.IP-day-of-week
                $day_of_week = date("w", $search_array["search_assign_begin"]);
                $day_of_week = $day_of_week == 0 ? 7 : $day_of_week;

                $assObj->setRepeatDayOfWeek($day_of_week);
                // set time range for checks
                $multiOverlaps->setAutoTimeRange([$assObj]);
                // generate and get the events represented by assign object
                $events = $assObj->getEvents();

                foreach($events as $ev)
                {
                    $event[$ev->getId()] = $ev;
                }
            } else
            {
                // the code for one specific slot
                $assEvt = new AssignEvent('', $search_array["search_assign_begin"], $search_array["search_assign_end"], '', '');
                $multiOverlaps->setTimeRange($search_array["search_assign_begin"], $search_array["search_assign_end"]);
                $event[$assEvt->getId()] = $assEvt;
            }
            // << changed for advanced search for room administrators

            //add the found resources to the check-set
            foreach ($found_resources as $key=>$val) {
                $multiOverlaps->addResource($key, $day_of_week);
            }

            $multiOverlaps->checkOverlap($event, $result);
            //output
            foreach ($found_resources as $key=>$val) {
                if (!$result[$key]) {
                    $this->showListObject($key);
                    $result_count++;
                }
            }
        } else {
            //output
            foreach ($found_resources as $key=>$val) {
                $this->showListObject($key);
                $result_count++;
            }
        }

    return $result_count;
    }

    function getResourcesSearchRange($resource_id)
    {
        static $children = [];

        $query = "SELECT resource_id
                  FROM resources_objects
                  WHERE parent_id = ?
                  ORDER BY name";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$resource_id]);
        $to_add = $statement->fetchAll(PDO::FETCH_COLUMN);

        foreach ($to_add as $rid) {
            $children[] = $rid;
            $this->getResourcesSearchRange($rid);
        }
        return $children;
    }
}
