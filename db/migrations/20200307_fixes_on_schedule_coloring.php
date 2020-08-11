<?php
class FixesOnScheduleColoring extends Migration
{
    public function description()
    {
        return 'Try to map color values with category indexes';
    }

    public function up()
    {
        global $PERS_TERMIN_KAT;

        foreach ($PERS_TERMIN_KAT as $index => $cat) {
            $query = "UPDATE `schedule`
                  SET `color` = :index
                  WHERE `color` = :color";
            $st = DBManager::get()->prepare($query);
            $st->bindValue(':index', $index);
            $st->bindValue(':color', $cat['color']);
            $st->execute();

            $query = "UPDATE `schedule_seminare`
                  SET `color` = :index
                  WHERE `color` = :color";
            $st = DBManager::get()->prepare($query);
            $st->bindValue(':index', $index);
            $st->bindValue(':color', $cat['color']);
            $st->execute();
        }

        $default = 1;
        $indexes = array_keys($PERS_TERMIN_KAT);

        $query = "UPDATE `schedule`
                    SET `color` = :default
                     WHERE `color` NOT IN ( :indexes )";
        $st = DBManager::get()->prepare($query);
        $st->bindValue(':default', $default);
        $st->bindValue(':indexes', $indexes);
        $st->execute();

        $query = "UPDATE `schedule_seminare`
                    SET `color` = :default
                     WHERE `color` NOT IN ( :indexes )";
        $st = DBManager::get()->prepare($query);
        $st->bindValue(':default', $default);
        $st->bindValue(':indexes', $indexes);
        $st->execute();

        DBManager::get()->exec("ALTER TABLE `schedule` MODIFY `color` tinyint");
        DBManager::get()->exec("ALTER TABLE `schedule_seminare` MODIFY `color` tinyint");
    }

    public function down()
    {
        global $PERS_TERMIN_KAT;

        DBManager::get()->exec("ALTER TABLE `schedule` CHANGE `color` `color` varchar(7) COMMENT 'color, rgb in hex'");
        DBManager::get()->exec("ALTER TABLE `schedule_seminare` CHANGE `color` `color` varchar(7) COMMENT 'color, rgb in hex'");

        foreach ($PERS_TERMIN_KAT as $index => $cat) {
            $query = "UPDATE `schedule`
                  SET `color` = :color
                  WHERE `color` = :index";
            $st = DBManager::get()->prepare($query);
            $st->bindValue(':index', $index);
            $st->bindValue(':color', $cat['color']);
            $st->execute();

            $query = "UPDATE `schedule_seminare`
                  SET `color` = :color
                  WHERE `color` = :index";
            $st = DBManager::get()->prepare($query);
            $st->bindValue(':index', $index);
            $st->bindValue(':color', $cat['color']);
            $st->execute();
        }
    }
}
