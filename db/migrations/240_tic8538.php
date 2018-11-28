<?php

class Tic8538 extends Migration
{
    function description()
    {
        return 'change ALLOW_DOZENT_ARCHIV to ALLOW_DOZENT_DELETE';
    }

    function up()
    {
        $db = DBManager::get();
        $db->exec("UPDATE `config` SET `field` = 'ALLOW_DOZENT_DELETE', `description` = 'Schaltet ein oder aus, ob ein Dozent eigene Veranstaltungen selbst löschen darf oder nicht' WHERE `config`.`field` = 'ALLOW_DOZENT_ARCHIV';");
        $db->exec("UPDATE `config_values` SET `field` = 'ALLOW_DOZENT_DELETE' WHERE `config_values`.`field` = 'ALLOW_DOZENT_ARCHIV' AND `config_values`.`range_id` = 'studip';");
    }

    function down()
    {
        $db = DBManager::get();
        $db->exec("UPDATE `config` SET `field` = 'ALLOW_DOZENT_ARCHIV', `description` = 'Schaltet ein oder aus, ob ein Dozent eigene Veranstaltungen selbst archivieren darf oder nicht' WHERE `config`.`field` = 'ALLOW_DOZENT_DELETE';");
        $db->exec("UPDATE `config_values` SET `field` = 'ALLOW_DOZENT_ARCHIV' WHERE `config_values`.`field` = 'ALLOW_DOZENT_DELETE' AND `config_values`.`range_id` = 'studip';");
    }
}
