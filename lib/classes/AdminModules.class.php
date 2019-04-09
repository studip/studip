<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
/**
* AdminModules.class.php
*
* administrate modules (global and local for institutes and Veranstaltungen)
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      core
* @module       AdminModules.class.php
* @package      studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// AdminModules.class.php
// Administration fuer Module (global und lokal fuer Veranstaltungen und Einrichtungen)
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


class AdminModules extends ModulesNotification {

    public function __construct() {
        parent::__construct();
        //please add here the special messages for modules you need consistency checks (defined below in this class)
        $this->registered_modules["forum"]["msg_warning"] = _("Wollen Sie wirklich das Forum deaktivieren?");
        //$this->registered_modules["forum"]["msg_pre_warning"] = _("Achtung: Beim Deaktivieren des Forums werden <b>%s</b> Postings ebenfalls gelöscht!");
        $this->registered_modules["forum"]["msg_activate"] = _("Das Forum kann jederzeit aktiviert werden.");
        $this->registered_modules["forum"]["msg_deactivate"] = _("Das Forum kann jederzeit deaktiviert werden.");


        $this->registered_modules["documents"]["msg_warning"] = _("Wollen Sie wirklich den Dateiordner deaktivieren?");
        //$this->registered_modules["documents"]["msg_pre_warning"] = _("Achtung: Beim Deaktivieren des Dateiordners werden <b>%s</b> Dateien und Ordner ebenfalls gelöscht!");
        $this->registered_modules["documents"]["msg_activate"] = _("Der Dateiordner kann jederzeit aktiviert werden.");
        $this->registered_modules["documents"]["msg_deactivate"] = _("Der Dateiordner kann jederzeit deaktiviert werden.");

        $this->registered_modules["schedule"]["msg_activate"] = _("Die Ablaufplanverwaltung kann jederzeit aktiviert werden.");
        $this->registered_modules["schedule"]["msg_deactivate"] = _("Die Ablaufplanverwaltung kann jederzeit deaktiviert werden.");

        $this->registered_modules["participants"]["msg_activate"] = _("Die Teilnehmendenverwaltung kann jederzeit aktiviert werden.");
        $this->registered_modules["participants"]["msg_deactivate"] = _("Die Teilnehmendenverwaltung kann jederzeit deaktiviert werden. Beachten Sie, dass Sie dann keine normalen Teilnehmenden verwalten können!");

        $this->registered_modules["personal"]["msg_activate"] = _("Die Personalliste kann jederzeit aktiviert werden.");
        $this->registered_modules["personal"]["msg_deactivate"] = _("Die Personalliste kann jederzeit deaktiviert werden.");

        $this->registered_modules["literature"]["msg_warning"] = _("Wollen Sie wirklich die Literaturverwaltung deaktivieren?");
        //$this->registered_modules["literature"]["msg_pre_warning"] = _("Achtung: Beim Deaktivieren der Literaturverwaltung werden <b>%s</b> öffentliche / nicht öffentliche Literaturlisten ebenfalls gelöscht!");
        $this->registered_modules["literature"]["msg_activate"] = _("Die Literaturverwaltung kann jederzeit aktiviert werden.");
        $this->registered_modules["literature"]["msg_deactivate"] = _("Die Literaturverwaltung kann jederzeit deaktiviert werden.");

        $this->registered_modules["wiki"]["msg_warning"] = _("Wollen Sie wirklich das Wiki deaktivieren?");
        //$this->registered_modules["wiki"]["msg_pre_warning"] = _("Achtung: Beim Deaktivieren des Wiki-Webs werden <b>%s</b> Seitenversionen ebenfalls gelöscht!");
        $this->registered_modules["wiki"]["msg_activate"] = _("Das Wiki-Web kann jederzeit aktiviert werden.");
        $this->registered_modules["wiki"]["msg_deactivate"] = _("Das Wiki-Web kann jederzeit deaktiviert werden.");

        $this->registered_modules["scm"]["msg_activate"] = _("Die freie Informationsseite kann jederzeit aktiviert werden.");
        $this->registered_modules["scm"]["msg_warning"] = _("Wollen Sie wirklich die freie Informationsseite deaktivieren?");
        //$this->registered_modules["scm"]["msg_pre_warning"] = _("Achtung: Beim Deaktivieren der freien Informationsseite werden die eingestellten Inhalte gelöscht!");
        $this->registered_modules["scm"]["msg_deactivate"] = _("Die freie Informationsseite kann jederzeit deaktiviert werden.");

        $this->registered_modules["elearning_interface"]["name"] = _("Lernmodul-Schnittstelle");
        $this->registered_modules["elearning_interface"]["msg_warning"] = _("Wollen Sie wirklich die Schnittstelle für die Integration von Content-Modulen deaktivieren?");
        $this->registered_modules["elearning_interface"]["msg_pre_warning"] = _("Achtung: Beim Deaktivieren der Schnittstelle für die Integration von Content-Modulen werden <b>%s</b> Verknüpfungen mit Lernmodulen nicht mehr in Stud.IP verfügbar sein!");
        $this->registered_modules["elearning_interface"]["msg_activate"] = _("Die Schnittstelle für die Integration von Content-Modulen kann jederzeit aktiviert werden.");
        $this->registered_modules["elearning_interface"]["msg_deactivate"] = _("Die Schnittstelle für die Integration von Content-Modulen kann jederzeit deaktiviert werden.");

        if (get_config('CALENDAR_GROUP_ENABLE')) {
            $this->registered_modules["calendar"]["name"] = _("Kalender");
            $this->registered_modules["calendar"]["msg_activate"] = _("Der Kalender kann jederzeit aktiviert werden.");
            $this->registered_modules["calendar"]["msg_warning"] = _("Wollen Sie wirklich den Kalender deaktivieren?");
            //$this->registered_modules["calendar"]["msg_pre_warning"] = _("Achtung: Beim Deaktivieren des Kalenders werden <b>%s</b> Termine ebenfalls gelöscht!");
            $this->registered_modules["calendar"]["msg_deactivate"] = _("Der Kalender kann jederzeit deaktiviert werden.");
        }
        $this->registered_modules["overview"]['name'] = _("Übersicht");
        $this->registered_modules["overview"]["msg_activate"] = _("Die Veranstaltungsübersicht kann jederzeit aktiviert werden.");
        $this->registered_modules["overview"]["msg_deactivate"] = _("Die Veranstaltungsübersicht kann jederzeit deaktiviert werden.");

        $this->registered_modules["admin"]['name'] = _("Verwaltung");
        $this->registered_modules["admin"]["msg_activate"] = _("Wenn die Verwaltungsseite aktiviert wird, kann die Veranstaltung wieder von Admin und Dozenten bearbeitet werden.");
        $this->registered_modules["admin"]["msg_deactivate"] = _("Wenn Sie die Verwaltungsseite deaktivieren, können Sie sie eventuell nicht mehr aktivieren.");
        $this->registered_modules["admin"]["msg_warning"] = _("Wenn die Verwaltungsseite deaktiviert wird, können Dozenten und Admin (Sie eventuell eingeschlossen) die Veranstaltung nicht mehr administrieren.");

        $this->registered_modules["resources"]['name'] = _("Ressourcen");
        $this->registered_modules["resources"]["msg_activate"] = _("Sie können die Ressourcenseite jederzeit aktivieren.");
        $this->registered_modules["resources"]["msg_deactivate"] = _("Sie können die Ressourcenseite jederzeit deaktivieren.");
    }

    public function getDocumentsExistingItems($range_id) { //getModuleDocumentsExistingItems
        return 0;
    }

    public function moduleDocumentsActivate($range_id) {
    }

    public function getModuleWikiExistingItems($range_id) {
        $query = "SELECT COUNT(keyword) FROM wiki WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$range_id]);
        return $statement->fetchColumn();
    }


    /**
     * prepares the database when activating the scm module.
     *
     * @param $range_id id
     */
    public function moduleScmActivate($range_id) {
        // check if existing items are available
        $query = "SELECT COUNT(scm_id) FROM scm WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$range_id]);
        $existingItems = $statement->fetchColumn();

        global $user, $SCM_PRESET;
        if ($existingItems) {
            return;
        }
        //create a default folder
        $query = "INSERT IGNORE INTO scm "
               . "(scm_id, range_id, user_id, tab_name, content, mkdate, chdate) "
               . "VALUES (?, ?, ?, ?, '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";
        DBManager::get()
            ->prepare($query)
            ->execute([
                md5(uniqid('simplecontentmodule')),
                $range_id,
                $GLOBALS['user']->id,
                $GLOBALS['SCM_PRESET'][1]['name']
            ]);
    }

    public function getModuleElearning_interfaceExistingItems($range_id) {
        if (Config::get()->ELEARNING_INTERFACE_ENABLE) {
            $object_connections = new ObjectConnections($range_id);
            return is_array($object_connections->getConnections()) ? count($object_connections->getConnections()) : 0;
        } else {
            return 0;
        }
    }

    public function moduleElearning_interfaceDeactivate($range_id) {
        if (Config::get()->ELEARNING_INTERFACE_ENABLE) {
            global $connected_cms;
            foreach(ObjectConnections::GetConnectedSystems($range_id) as $system){
                ELearningUtils::loadClass($system);
                $connected_cms[$system]->deleteConnectedModules($range_id);
            }
        }
    }

    public function getModuleCalendarExistingItems($range_id)
    {
        return CalendarEvent::countBySql('range_id = ?', [$range_id]);
    }
}
