<?php
class Tic9101 extends Migration
{
    const MAPPING = [
         1 =>  7,
         2 =>  1,
         3 =>  2,
         4 =>  8,
         5 =>  4,
         6 =>  9,
         7 =>  6,
         8 =>  3,
         9 =>  5,
        10 => 14,
        11 => 10,
        12 => 11,
        13 => 14,
        14 => 13,
        15 => 12,
    ];

    public function description()
    {
        return 'Align colors in schedule with color mapping in my courses';
    }

    public function up()
    {
        $query = "UPDATE `schedule_seminare`
                  SET `color` = :new
                  WHERE `color` = :old";
        $statement = DBManager::get()->prepare($query);

        foreach (self::MAPPING as $old => $new) {
            $statement->bindValue(':new', $new);
            $statement->bindValue(':old', $old);
            $statement->execute();
        }
    }

    public function down()
    {
        $query = "UPDATE `schedule_seminare`
                  SET `color` = :new
                  WHERE `color` = :old";
        $statement = DBManager::get()->prepare($query);

        foreach (self::MAPPING as $old => $new) {
            $statement->bindValue(':new', $old);
            $statement->bindValue(':old', $new);
            $statement->execute();
        }
    }
}
