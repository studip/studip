<?php
class AdjustTokenTable extends Migration
{
    public function description()
    {
        return 'Adjusts token table to absolutely unique tokens and a smaller index';
    }

    public function up()
    {
        $query = "ALTER TABLE `user_token`
                    DROP PRIMARY KEY,
                    DROP INDEX `index_token`,
                    DROP INDEX `index_user_id`,
                    MODIFY COLUMN `token` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL FIRST,
                    MODIFY COLUMN `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    MODIFY COLUMN `expiration` INT(11) UNSIGNED NOT NULL,
                    ADD PRIMARY KEY (`token`)";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `user_token`
                    DROP PRIMARY KEY,
                    ADD INDEX `index_token` (`token`),
                    ADD INDEX `index_user_id` (`user_id`),
                    MODIFY COLUMN `token` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL AFTER `user_id`,
                    MODIFY COLUMN `user_id` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                    MODIFY COLUMN `expiration` INT(11) NOT NULL,
                    ADD PRIMARY KEY (`user_id`, `token`, `expiration`)";
        DBManager::get()->exec($query);
    }
}
