<?php
class ChangeScheduleColorWithCategoryIndex extends Migration
{
    public function description()
    {
        return 'Change schedule table colors from rgb hex to category index number';
    }

    public function up()
    {

        global $PERS_TERMIN_KAT;

        DBManager::get()->exec("ALTER TABLE `schedule` CHANGE `color` `color` varchar(255) COMMENT 'category index'");
        DBManager::get()->exec("ALTER TABLE `schedule_seminare` CHANGE `color` `color` varchar(255) COMMENT 'category index'");

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
