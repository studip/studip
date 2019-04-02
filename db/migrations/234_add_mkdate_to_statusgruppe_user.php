<?php
class AddMkdateToStatusgruppeUser extends Migration
{
    public function up()
    {
        $query = "ALTER TABLE `statusgruppe_user`
                  ADD COLUMN `mkdate` INT(11) UNSIGNED NULL DEFAULT NULL";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `statusgruppe_user`
                  DROP COLUMN `mkdate`";
        DBManager::get()->exec($query);
    }
}
