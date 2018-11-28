<?php
class AddSwitchToPreselectSemester extends Migration {
    function description() {
        return "adds a configuration switch to turn semester preselection on/off into the config table";
    }

    function up() {
         DBManager::get()->exec("INSERT INTO config (`config_id`, `parent_id`, `field`, " .
                "`value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`," .
                "`description`, `comment`, `message_template`) VALUES " .
                "('06d703c3de37cdae942c66e18f7dcd02', '', 'ASSI_SEMESTER_PRESELECT', " .
                "'TRUE', 0, 'boolean', 'global', '', 0, ".time().",". time().
                ", 'Wenn ausgeschaltet wird im admin_seminare_assi beim" .
                "Erstellen einer Veranstaltung als Semester bitte auswählen " .
                "angezeigt und nicht das voreingestellte Semester.', '', '')");
    }

    function down() {
         DBManager::get()->exec("DELETE FROM `config` WHERE `config_id`='06d703c3de37cdae942c66e18f7dcd02' LIMIT 1 ");
    }
}

?>
