#!/usr/bin/env php
<?php
# Lifter007: TODO
# Lifter003: TODO
/**
* show_room_groups.php
* 
* use this script to get a sample config_room_groups.inc.php based on existing
* rooms and resources structure, writes to STDOUT
*
* @author       André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// show_room_groups.php
// 
// Copyright (C) 2006 André Noack <noack@data-quest.de>,
// Suchi & Berg GmbH <info@data-quest.de>
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

require_once dirname(__FILE__) . '/studip_cli_env.inc.php';
require_once "lib/resources/lib/ResourceObject.class.php";

$query = "SELECT `parent_id`, `resource_id`
          FROM `resources_objects`
          INNER JOIN `resources_categories` USING(`category_id`)
          WHERE `is_room` = 1";
$resources = DBManager::get()->fetchGroupedPairs($query);

if (count($resources) > 0) {
    $res_obj = ResourceObject::Factory();

    fwrite(STDOUT, "<?php\n//copy to \$STUDIP_BASE_PATH/config/config_room_groups.inc.php\n//generated ". date('r') ."\n");
    foreach ($resources as $parent_id => $rooms){
        if (is_array($rooms)){
            $res_obj->restore($parent_id);
            fwrite(STDOUT, "//--------------------------------------------------------------------\n");
            fwrite(STDOUT, "\$room_groups[\$c]['name'] = '" . $res_obj->getPathToString(true) . "';\n");
            foreach ($rooms as $room_id){
                $res_obj->restore($room_id);
                fwrite(STDOUT, "\$room_groups[\$c]['rooms'][] = '$room_id';  //" . $res_obj->getPathToString(true) . "\n");
            }       
        }
    }
} else {
    trigger_error('No rooms found in database.', E_USER_ERROR);
}
