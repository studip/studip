<?php
/**
 * Migration for StEP00299
 *
 * @author  Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.5
 *
 * @see https://develop.studip.de/trac/ticket/6590
 */
class StEP00299Statusgroups extends Migration
{

    /**
     * Describe what the migration does.
     * @return string
     */
    public function description()
    {
        return 'Adds columns for selfassign start and end time to table statusgruppen';
    }

    /**
     * Add a new database column to table statusgruppen: optional start time for self assignment.
     */
    public function up()
    {
        DBManager::get()->execute("ALTER TABLE `statusgruppen`
            ADD `selfassign_start` INT NOT NULL DEFAULT 0 AFTER `selfassign`,
            ADD `selfassign_end` INT NOT NULL DEFAULT 0 AFTER `selfassign_start`");
    }

    /**
     * Drops database column for start time of self assignment.
     */
    public function down()
    {
        DBManager::get()->execute("ALTER TABLE `statusgruppen` DROP `selfassign_start`");
    }

}
