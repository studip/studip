<?php
class Step345WikiAncestor extends Migration
{
    public function description()
    {
        return 'Adds column ancestor to table wiki.';
    }

    public function up()
    {
        $query = "ALTER TABLE `wiki`
            ADD COLUMN `ancestor` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL AFTER `body`;";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `wiki` DROP COLUMN `ancestor`";
        DBManager::get()->exec($query);
    }
}
