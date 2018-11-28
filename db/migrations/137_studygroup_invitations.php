<?php
class StudygroupInvitations extends Migration
{
    function description()
    {
        return 'Create database for studygroup invitations.';
    }

    function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `studygroup_invitations` (
                    `sem_id` varchar(32) NOT NULL,
                    `user_id` varchar(32) NOT NULL,
                    `mkdate` int(20) NOT NULL,
                    PRIMARY KEY (`sem_id`,`user_id`)
                  )";
        DBManager::get()->exec($query);
    }

    function down()
    {
        DBManager::get()->exec("DROP TABLE IF EXISTS `studygroup_invitations`");
    }
}
