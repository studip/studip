<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: test
# Lifter010: TODO
/**
* ExternConfigDb.class.php
*
* This class is a wrapper class for configuration files.
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternConfig
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternConfigDb.class.php
// This is a wrapper class for configuration data stored in the database.
// Copyright (C) 2007 Peter Thienel <pthienel@web.de>,
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

class ExternConfigDb extends ExternConfig {

    /**
    *
    */
    function __construct ($range_id, $module_name, $config_id = '') {
        parent::__construct ($range_id, $module_name, $config_id);
    }

    /**
    *
    */
    function store () {
        parent::store();
        $serialized_config = json_encode($this->config);

        if (mb_strlen($serialized_config)) {
            $stmt = DBManager::get()->prepare("UPDATE extern_config
                SET config = ?, chdate = UNIX_TIMESTAMP()
                WHERE config_id = ? AND range_id = ?");
            $stmt->execute($data = [$serialized_config, $this->id, $this->range_id]);

            return($this->updateConfiguration());
        } else {
            ExternModule::printError();
            return FALSE;
        }

    }

    /**
    *
    */
    function parse () {
        $query = "SELECT config FROM extern_config WHERE config_id = ?";
        $parameters = [$this->id];
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        $row = $statement->fetchColumn();
        if ($row) {
            $this->config = json_decode($row, true);
        } else {
            ExternModule::printError();
        }
    }

    function insertConfiguration () {
        if (!parent::insertConfiguration()) {
            return false;
        }
         $serialized_config = json_encode($config_obj->config);
         $time = time();
         $query = "INSERT INTO extern_config VALUES (?,?,?,?,0,?,?,?)";
         $statement = DBManager::get()->prepare($query);
         $statement->execute([$this->id, $this->range_id, $this->module_type,
                $this->config_name, $serialized_config, $time, $time
            ]);
            if (!$statement->rowCount()) {
                return FALSE;
            }
        return TRUE;
    }
}

?>
