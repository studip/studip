<?php
/**
 * Adds another visibility setting to datafields
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class Tic6000DatafieldsVisibility extends Migration
{
    public function description()
    {
        return 'Adds another visibility setting to datafields';
    }

    public function up()
    {
        $query = "ALTER TABLE `datafields`
                    ADD COLUMN `system` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `datafields` DROP COLUMN `system`";
        DBManager::get()->exec($query);
    }
}
