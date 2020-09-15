<?php
class RemoveInvalidModulUserAssignments extends Migration
{
    public function description()
    {
        return 'Removes invalid user assignemnts from table `mvv_modul_user` (see BIEST #9809)';
    }

    public function up()
    {

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `mvv_modul_user` (
            `modul_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            `user_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            `gruppe` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
            `position` int(11) NOT NULL DEFAULT '9999',
            `author_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
            `editor_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
            `mkdate` bigint(20) NOT NULL,
            `chdate` bigint(20) NOT NULL,
            PRIMARY KEY (`modul_id`,`user_id`,`gruppe`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC");


        $query = "DELETE
                  FROM `mvv_modul_user`
                  WHERE `user_id` NOT IN (
                      SELECT `user_id`
                      FROM `auth_user_md5`
                  )";
        DBManager::get()->exec($query);
    }
}
