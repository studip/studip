<?php
/**
 * Migration for StEP00301
 *
 * @author  Arne Schröder <schroeder@data-quest.de>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.5
 *
 * @see https://develop.studip.de/trac/ticket/6574
 */
class Step00301AdmissionConditiongroups extends Migration
{

    /**
     * short description of this migration
     */
    public function description()
    {
        return 'Adds table admission_conditiongroup.';
    }

    /**
     * perform this migration
     */
    public function up()
    {
        DBManager::get()->exec('CREATE TABLE IF NOT EXISTS `admission_conditiongroup` (
            `conditiongroup_id` varchar(32) NOT NULL,
            `quota` int(11) NOT NULL,
            PRIMARY KEY (`conditiongroup_id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC');

        DBManager::get()->exec("ALTER TABLE `admission_condition` ADD `conditiongroup_id` VARCHAR( 32 ) NOT NULL DEFAULT '' AFTER `filter_id`");
    }

    /**
     * revert this migration
     */
    public function down()
    {
        DBManager::get()->exec('DROP TABLE `admission_conditiongroup`');
        DBManager::get()->exec('ALTER TABLE `admission_condition` DROP `conditiongroup_id`;');
    }

}
