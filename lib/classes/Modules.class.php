<?php
# Lifter002: TODO
# Lifter007: TEST - documentation and definition array still to do
# Lifter003: TEST
# Lifter010: TODO
/**
* Modules.class.php
*
* check for modules (global and local for institutes and Veranstaltungen), read and write
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      core
* @module       Modules.class.php
* @package      studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Modules.class.php
// Checks fuer Module (global und lokal fuer Veranstaltungen und Einrichtungen), Schreib-/Lesezugriff
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


class Modules {
    var $registered_modules = [
        'overview' => ['id' => 20, 'const' => '', 'sem' => true, 'inst' => false],
        'admin' => ['id' => 17, 'const' => '', 'sem' => true, 'inst' => false],
        'forum' => ['id' => 0, 'const' => '', 'sem' => true, 'inst' => true],
        'documents' => ['id' => 1, 'const' => '', 'sem' => true, 'inst' => true],
        'schedule' => ['id' => 2, 'const' => '', 'sem' => true, 'inst' => false],
        'participants' => ['id' => 3, 'const' => '', 'sem' => true, 'inst' => false],
        'personal' => ['id' => 4, 'const' => '', 'sem' => false, 'inst' => true],
        'literature' => ['id' => 5, 'const' => 'LITERATURE_ENABLE', 'sem' => true, 'inst' => true],
        'wiki' => ['id' => 8, 'const' => 'WIKI_ENABLE', 'sem' => true, 'inst' => true],
        'scm' => ['id' => 12, 'const' => 'SCM_ENABLE', 'sem' => true, 'inst' => true],
        'elearning_interface' => ['id' => 13, 'const' => 'ELEARNING_INTERFACE_ENABLE', 'sem' => true, 'inst' => true],
        'calendar' => ['id' => 16, 'const' => 'COURSE_CALENDAR_ENABLE', 'sem' => true, 'inst' => true],
        'resources' => ['id' => 21, 'const' => 'RESOURCES_ENABLE', 'sem' => true, 'inst' => true]
    ];

    function __construct() {
    }

    function getStatus($modul, $range_id, $range_type = '') {
        $bitmask = $this->getBin($range_id, $range_type);
        $id = $this->registered_modules[$modul]['id'];
        return $this->isBit($bitmask, $id);
    }

    function getLocalModules($range_id, $range_type = '', $modules = false, $type = false) {
        if (!$range_type) {
            $range_type = get_object_type($range_id, ['sem','inst']);
        }

        if ($modules === false || $type === false) {
            if ($range_type == 'sem') {
                $query = "SELECT modules, status FROM seminare WHERE Seminar_id = ?";
            } else {
                $query = "SELECT modules, type as status FROM Institute WHERE Institut_id = ? ";
            }
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$range_id]);
            $data = $statement->fetch(PDO::FETCH_ASSOC);
            if ($type === false) {
                $type = $data['status'];
            }
            if ($modules === false) {
                $modules = $data['modules'];
            }
        }
        if ($modules === null || $modules === false) {
            $modules = $this->getDefaultBinValue($range_id, $range_type, $type);
        }
        if ($range_type == 'sem') {
            $sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$type]['class']] ?: SemClass::getDefaultSemClass();
        } else {
            $sem_class = SemClass::getDefaultInstituteClass($type);
        }
        foreach ($this->registered_modules as $key => $val) {
            if ($sem_class) {
                $module = $sem_class->getSlotModule($key);
            }
            if (!$sem_class || $sem_class->isModuleAllowed($module)) {
                $modules_list[$key] = $this->isBit($modules, $val['id']);

                if ($sem_class && $sem_class->isSlotMandatory($key)) {
                    $modules_list[$key] = pow(2, $val['id']);
                }
            } else {
                $modules_list[$key] = 0;
            }
        }
        return $modules_list;
    }

    function getDefaultBinValue($range_id, $range_type = '', $type = false) {
        global $SEM_TYPE, $SEM_CLASS;

        $bitmask = 0;
        if (!$range_type) {
            $range_type = get_object_type($range_id, ['sem','inst']);
        }

        if ($type === false) {
            if ($range_type == "sem") {
                $query = "SELECT status FROM seminare WHERE Seminar_id = ?";
            } else {
                $query = "SELECT type FROM Institute WHERE Institut_id = ?";
            }
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$range_id]);
            $type = $statement->fetchColumn();
        }

        if ($range_type == 'sem') {
            $sem_class = $SEM_CLASS[$SEM_TYPE[$type]['class']] ?: SemClass::getDefaultSemClass();
        } else {
            $sem_class = SemClass::getDefaultInstituteClass($type);
        }

        foreach ($this->registered_modules as $slot => $val) {
            if ($val[$range_type == 'sem' ? 'sem' : 'inst']) {
                if ($sem_class->isModuleActivated($sem_class->getSlotModule($slot)) && $this->checkGlobal($slot)) {
                    $this->setBit($bitmask, $val['id']);
                }
            }
        }
        return $bitmask;
    }

    function getBin($range_id, $range_type = '') {
        if (!$range_type) {
            $range_type = get_object_type($range_id);
        }

        if ($range_type == 'sem') {
            $query = "SELECT modules FROM seminare WHERE Seminar_id = ?";
        } else {
            $query = "SELECT modules FROM Institute WHERE Institut_id = ?";
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$range_id]);
        $modules = $statement->fetchColumn();

        if ($modules === null || $modules === false) {
            $bitmask = $this->getDefaultBinValue($range_id, $range_type);
        } else {
            $bitmask = $modules;
        }

        return $bitmask;
    }

    function writeBin($range_id, $bitmask, $range_type = '') {
        if (!$range_type) {
            $range_type = get_object_type($range_id);
        }

        if ($range_type == 'sem') {
            $query = "UPDATE seminare SET modules = ? WHERE Seminar_id = ?";
        } else {
            $query = "UPDATE Institute SET modules = ? WHERE Institut_id = ?";
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$bitmask, $range_id]);
        return (bool)$statement->rowCount();
    }


    function writeDefaultStatus($range_id, $range_type = '') {
        if (!$range_type) {
            $range_type = get_object_type($range_id);
        }

        if ($range_type == 'sem') {
            $query = "UPDATE seminare SET modules = ? WHERE Seminar_id = ?";
        } else {
            $query = "UPDATE Institute SET modules = ? WHERE Institut_id = ?";
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $this->getDefaultBinValue($range_id, $range_type),
            $range_id
        ]);
        return (bool)$statement->rowCount();
    }

    function writeStatus($modul, $range_id, $value, $range_type = '') {
        if (!$range_type) {
            $range_type = get_object_type($range_id);
        }

        $bitmask = $this->getBin($range_id, $range_type);

        if ($value) {
            $this->setBit($bitmask, $this->registered_modules[$modul]['id']);
        } else {
            $this->clearBit($bitmask, $this->registered_modules[$modul]['id']);
        }

        if (!$this->checkGlobal($modul)) {
            return false;
        }

        if ($range_type == 'sem') {
            $query = "UPDATE seminare SET modules = ? WHERE Seminar_id = ?";
        } else {
            $query = "UPDATE Institute SET modules = ? WHERE Institut_id = ?";
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$bitmask, $range_id]);
        return (bool)$statement->rowCount();
    }

    function checkGlobal($modul) {
        $const = $this->registered_modules[$modul]['const'];
        return !$const or Config::get()->$const;
    }

    function checkLocal($modul, $range_id, $range_type = '') {
        return $this->getStatus($modul, $range_id, $range_type);
    }

    function isEnableable($modul, $range_id, $range_type = '') {
        if (!$range_type) {
            $range_type = get_object_type($range_id);
        }
        $type = ($range_type == 'sem' ? 'sem' : 'inst');
        return $this->checkGlobal($modul) and $this->registered_modules[$modul][$type];
    }

    function setBit(&$bitField,$n) {
        // Ueberprueft, ob der Wert zwischen 0-31 liegt
        // $n ist hier der Wert der aktivierten Checkbox, z.B. 15
        // Somit waere hier die 15. Checkbox aktiviert
        if ($n < 0 or $n > 31) {
            return false;
        }

        // Bit Shifting
        // Hier wird nun der Binaerwert fuer die aktuelle Checkbox gesetzt.
        // In unserem Beispiel wird hier nun die 15. Stelle von rechts auf 1 gesetzt
        // 100000000000000 <-- Dieses entspricht der Zahl 16384
        // | ist nicht das logische ODER sondern das BIT-oder
        $bitField |= (0x01 << $n);
        return true;
    }

    function clearBit(&$bitField, $n) {
        // Loescht ein Bit oder ein Bitfeld
        // & ist nicht das logische UND sondern das BIT-and
        $bitField &= ~(0x01 << ($n));
        return true;
    }

    function isBit($bitField, $n) {
        // Ist die x-te Stelle eine 1?
        return $bitField & (0x01 << $n);
    }
}
