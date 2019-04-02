<?php
/**
 * Add a numeric id column to table "opengraphdata" in order to avoid problems
 * with innodb and to reduce key size.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class ChangeOpengraphDataPk extends Migration
{
    public function up()
    {
        $query = "ALTER TABLE `opengraphdata`
                  DROP PRIMARY KEY,
                  ADD COLUMN `opengraph_id` INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT FIRST,
                  ADD UNIQUE KEY `url` (`url`(512))";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `opengraphdata`
                  DROP PRIMARY KEY,
                  DROP INDEX `url`,
                  DROP COLUMN `opengraph_id`,
                  ADD PRIMARY KEY (`url`)";
        DBManager::get()->exec($query);
    }
}
