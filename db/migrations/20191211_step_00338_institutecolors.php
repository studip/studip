<?php
class Step00338Institutecolors extends Migration
{
    public function description()
    {
        return 'add datafield for institute planning default colors';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec("DELETE FROM `datafields_entries` WHERE `datafields_entries`.`datafield_id` = '41cda2be71fe9efd6e28b853fc0681f3';");
        $db->exec("INSERT INTO `datafields` (`datafield_id`, `name`, `object_type`, `edit_perms`, `view_perms`, `priority`, `type`, `typeparam`, `is_required`, `is_userfilter`, `description`, `system`) VALUES
                    ('0c63321a8e93b3ccc927611709248e07', 'default Planungsfarbe', 'inst', 'admin', 'root', 0, 'textline', '', 0, 0, 'Default Farben im Veranstaltungsplaner', 0);");

    }

    public function down()
    {
        $db = DBManager::get();
        $db->exec("DELETE FROM `datafields` WHERE `datafields`.`datafield_id` IN('0c63321a8e93b3ccc927611709248e07');");

    }
}
