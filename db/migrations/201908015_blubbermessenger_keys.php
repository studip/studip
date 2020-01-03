<?php
class BlubbermessengerKeys extends Migration
{
    public function description()
    {
        return "Adds primary key to table 'blubber_mentions'";
    }

    public function up()
    {
        $query = "ALTER TABLE `blubber_mentions`
                  ADD COLUMN `mention_id` INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `blubber_mentions`
                  DROP COLUMN `mention_id`";
        DBManager::get()->exec($query);
    }
}
