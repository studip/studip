<?php
# refers to https://develop.studip.de/trac/ticket/2568
class Tic2568CommentInternal extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'adds to column comment_internal to the table resources_assign';
    }

    /**
     * perform this migration
     */
    function up()
    {
        DBManager::get()->exec("ALTER TABLE `resources_assign` ADD `comment_internal` TEXT NULL DEFAULT NULL");
    }

    /**
     * revert this migration
     */
    function down()
    {
        DBManager::get()->exec("ALTER TABLE `resources_assign` DROP COLUMN `comment_internal`");
    }
}
