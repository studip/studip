<?php
# refers to https://develop.studip.de/trac/ticket/2395
class Tic2395Smileys extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'removes unused column "smiley_favorite_publish" from table "user_info"';
    }

    /**
     * perform this migration
     */
    function up()
    {
        DBManager::get()->exec("ALTER TABLE `user_info` DROP COLUMN `smiley_favorite_publish`");
    }

    /**
     * revert this migration
     */
    function down()
    {
        DBManager::get()->exec("ALTER TABLE `user_info` ADD COLUMN `smiley_favorite_publish` VARCHAR(255) NOT NULL DEFAULT ''");
    }
}
