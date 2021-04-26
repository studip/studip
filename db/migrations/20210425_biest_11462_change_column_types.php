<?php
/**
 * @see https://develop.studip.de/trac/ticket/11462
 */
final class Biest11462ChangeColumnTypes extends Migration
{
    public function description()
    {
        return 'Changes the column types on columns type, value and compare_op for table userfilter_fields';
    }

    protected function up()
    {
        $query = "ALTER TABLE `userfilter_fields`
                  MODIFY COLUMN `type` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                  MODIFY COLUMN `value` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                  MODIFY COLUMN `compare_op` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "ALTER TABLE `userfilter_fields`
                  MODIFY COLUMN `type` VARCHAR(255) DEFAULT NULL,
                  MODIFY COLUMN `value` VARCHAR(255) DEFAULT NULL,
                  MODIFY COLUMN `compare_op` VARCHAR(255) DEFAULT NULL";
        DBManager::get()->exec($query);
    }
}
