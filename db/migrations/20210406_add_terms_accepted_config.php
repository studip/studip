<?php
class AddTermsAcceptedConfig extends Migration
{
    public function description()
    {
        return 'Adds missing config entry for TERMS_ACCEPTED';
    }

    protected function up()
    {
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`
                  ) VALUES (
                    'TERMS_ACCEPTED', '0', 'boolean', 'user', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                    'Die Nutzungsbedingungen wurden akzeptiert'
                  )";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "DELETE FROM `config`
                  WHERE `field` = 'TERMS_ACCEPTED'";
        DBManager::get()->exec($query);
    }
}
