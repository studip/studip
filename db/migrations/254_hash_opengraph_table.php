<?php
class HashOpengraphTable extends Migration
{
    public function up()
    {
        $query = "ALTER TABLE `opengraphdata`
                  ADD COLUMN `hash` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '' AFTER `opengraph_id`";
        DBManager::get()->exec($query);

        $query = "UPDATE `opengraphdata` SET `hash` = MD5(`url`)";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `opengraphdata`
                  DROP INDEX `url`,
                  ADD UNIQUE KEY `hash` (`hash`)";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `opengraphdata`
                  DROP COLUMN `hash`,
                  ADD UNIQUE INDEX `url` (`url`(512))";
        DBManager::get()->exec($query);
    }
}
