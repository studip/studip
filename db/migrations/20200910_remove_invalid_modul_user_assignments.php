<?php
class RemoveInvalidModulUserAssignments extends Migration
{
    public function description()
    {
        return 'Removes invalid user assignemnts from table `mvv_modul_user` (see BIEST #9809)';
    }

    public function up()
    {
        $query = "DELETE
                  FROM `mvv_modul_user`
                  WHERE `user_id` NOT IN (
                      SELECT `user_id`
                      FROM `auth_user_md5`
                  )";
        DBManager::get()->exec($query);
    }
}
