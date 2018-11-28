<?php

class Step228Raumanfragen extends Migration
{
    function description ()
    {
        return 'adds field metadate_id to resources_requests';
    }

    function up ()
    {
        $db = DBManager::get();
        $db->exec(
            "ALTER TABLE `resources_requests` ADD `metadate_id` VARCHAR( 32 ) NOT NULL DEFAULT '' AFTER `termin_id`");
        $db->exec(
            "ALTER TABLE `resources_requests` DROP INDEX  `closed` , ADD INDEX  `closed` (  `closed` ,  `request_id`, `resource_id` )");
        $db->exec(
            "ALTER TABLE `resources_requests` ADD INDEX (  `metadate_id` )");
        SimpleORMap::expireTableScheme();
    }

    function down ()
    {
        $db = DBManager::get();
        $db->exec(
            "ALTER TABLE `resources_requests` " .
            "DROP COLUMN `metadate_id`");
    }
}