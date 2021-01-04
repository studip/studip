<?php

class Tic11041 extends Migration
{
    public function description()
    {
        return 'Extends table lti_tool, add oauth_signature_method';
    }

    public function up()
    {
        $query = "ALTER TABLE `lti_tool`
                  ADD COLUMN `oauth_signature_method` VARCHAR(10) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'sha1'";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `lti_tool`
                  DROP COLUMN `oauth_signature_method`";
        DBManager::get()->exec($query);
    }
}
