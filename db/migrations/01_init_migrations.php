<?
class InitMigrations extends Migration
{
    function description ()
    {
        return 'initialize database schema for migrations';
    }

    function up ()
    {
        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `schema_version` (
              `domain` varchar(255) NOT NULL default '',
              `version` int(11) NOT NULL default '0',
              PRIMARY KEY  (`domain`)
            ) ENGINE=MyISAM;
        ");
    }
}
?>
