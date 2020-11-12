<?php
class Tic9101 extends Migration
{
    const MAPPING = [
        0 => 4,
        1 => 2,
        2 => 3,
        3 => 8,
        4 => 6,
        6 => 7,
        7 => 0,
        8 => 1,
    ];

    public function description()
    {
        return 'Adjusts color mapping for my courses';
    }

    public function up()
    {
        $query = "ALTER TABLE `seminar_user`
                  CHANGE COLUMN `gruppe` `gruppe` TINYINT(2) UNSIGNED NOT NULL DEFAULT 4";
        DBManager::get()->exec($query);

        $query = "UPDATE `seminar_user`
                  SET `gruppe` = :new
                  WHERE `gruppe` = :old";
        $statement = DBManager::get()->prepare($query);

        foreach (self::MAPPING as $old => $new) {
            $statement->bindValue(':old', $old);
            $statement->bindValue(':new', $new);
            $statement->execute();
        }
    }

    public function down()
    {
        $query = "ALTER TABLE `seminar_user`
                  CHANGE COLUMN `gruppe` `gruppe` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0";
        DBManager::get()->exec($query);

        $query = "UPDATE `seminar_user`
                  SET `gruppe` = :new
                  WHERE `gruppe` = :old";
        $statement = DBManager::get()->prepare($query);

        foreach (self::MAPPING as $old => $new) {
            $statement->bindValue(':old', $new);
            $statement->bindValue(':new', $old);
            $statement->execute();
        }
    }
}
