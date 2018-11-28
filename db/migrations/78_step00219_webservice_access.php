<?php

class Step00219WebserviceAccess extends Migration
{

    function description()
    {
        return 'Step00219: new table webservice_access_rules';
    }

    function up()
    {
        $db = DBManager::get();
        $db->exec("CREATE TABLE `webservice_access_rules` (
                `api_key` VARCHAR( 100 ) NOT NULL DEFAULT '',
                `method` VARCHAR( 100 ) NOT NULL DEFAULT '',
                `ip_range` VARCHAR( 200 ) NOT NULL DEFAULT '',
                `type` ENUM( 'allow', 'deny' ) NOT NULL DEFAULT 'allow',
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY
                ) ENGINE=MyISAM;");
        SimpleORMap::expireTableScheme();
        if ($GLOBALS['STUDIP_API_KEY'] && $GLOBALS['WEBSERVICES_ENABLE']) {
            $db->exec("INSERT INTO `webservice_access_rules`  (`api_key`, `method`, `ip_range`, `type`) VALUES (".$db->quote($GLOBALS['STUDIP_API_KEY']).", '', '', 'allow')");
        }
    }

    function down()
    {
        $db = DBManager::get();
        $db->exec("DROP TABLE `webservice_access_rules` ");
        SimpleORMap::expireTableScheme();
    }
}
