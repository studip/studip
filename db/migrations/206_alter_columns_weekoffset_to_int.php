<?php

/**
 * Migration for Ticket #6971 (BIEST)
 *
 * @author  Dominik Feldschnieders <dofeldsc@uos.de>
 * @license GPL2 or any later version
 * Date: 19.10.16
 */
class AlterColumnsWeekoffsetToInt extends Migration
{

    function description()
    {
        return 'Alter the two columns week_offset and end_offset in table seminar_cycle_dates .';
    }

    function up()
    {
        DBManager::get()->exec(
            "ALTER table `seminar_cycle_dates`
             CHANGE column `week_offset` `week_offset` INT NOT NULL DEFAULT '0', CHANGE column `end_offset` `end_offset` INT DEFAULT NULL");
    }

    function down()
    {
        DBManager::get()->exec(
            "ALTER table `seminar_cycle_dates`
             CHANGE column `week_offset` `week_offset` TINYINT NOT NULL DEFAULT '0', CHANGE column `end_offset` `end_offset` TINYINT DEFAULT NULL");
    }

}
