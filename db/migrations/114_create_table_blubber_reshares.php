<?php
class CreateTableBlubberReshares extends Migration
{
    function description()
    {
        return 'Creates the table that allows blubber reshares.';
    }

    function up()
    {
        //Add table
        $query = "CREATE TABLE IF NOT EXISTS `blubber_reshares` (
            `topic_id` varchar(32) NOT NULL,
            `user_id` varchar(32) NOT NULL,
            `external_contact` tinyint(4) NOT NULL DEFAULT '0',
            `chdate` int(11) NOT NULL,
            UNIQUE KEY `unique_reshares` (`topic_id`,`user_id`,`external_contact`),
            KEY `topic_id` (`topic_id`),
            KEY `user_id` (`user_id`)
        ) ENGINE=MyISAM";
        $statement = DBManager::get()->prepare($query);
        $statement->execute();
    }

    function down()
    {
    }
}
