<?php
class Step00129EmailRestriction extends Migration
{
    public function description ()
    {
        return 'Adds the new Value EMAIL_DOMAIN_RESTRICTION to table config.';
    }

    public function up ()
    {
        $this->announce("add new value EMAIL_DOMAIN_RESTRICTION to table config");

        DBManager::get()->exec("INSERT INTO `config` VALUES ('cb92d5bb08f346567dbd394d0d553454', '', 'EMAIL_DOMAIN_RESTRICTION', '', 1, 'string', 'global', '', 0, 1157107088, 1157107088, 'Beschränkt die gültigkeit von Email-Adressen bei freier Registrierung auf die angegebenen Domains. Komma-separierte Liste von Domains ohne vorangestelltes @.', '', '')");

        $this->announce("done.");

    }

    public function down ()
    {
        $this->announce("remove value EMAIL_DOMAIN_RESTRICTION from table config");

        DBManager::get()->exec("DELETE FROM `config` WHERE `field` = 'EMAIL_DOMAIN_RESTRICTION'");

        $this->announce("done.");
    }
}
