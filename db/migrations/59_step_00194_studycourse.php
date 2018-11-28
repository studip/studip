<?
class Step00194Studycourse extends Migration
{
    function description()
    {
        return 'adding a new table abschluss and columns into the table user_studiengang';
    }

    function up()
    {
        $db = DBManager::get();
        // create database table for privacy settings
        $db->exec("CREATE TABLE IF NOT EXISTS `abschluss` (
                          `abschluss_id` char(32) NOT NULL default '',
                          `name` varchar(255) NOT NULL default '',
                          `beschreibung` text,
                          `mkdate` int(20) default NULL,
                          `chdate` int(20) default NULL,
                          PRIMARY KEY  (`abschluss_id`)
                        ) ENGINE=MyISAM");
        $db->exec("ALTER TABLE `user_studiengang` ADD `semester` TINYINT(2) DEFAULT 0");
        $db->exec("ALTER TABLE `user_studiengang` ADD `abschluss_id` CHAR(32) DEFAULT 0");
        $db->exec("ALTER TABLE `user_studiengang` ADD INDEX ( `studiengang_id` )");
    }

    function down()
    {
        $db = DBManager::get();
        $db->exec("DROP TABLE IF EXISTS `abschluss`");
        $db->exec("ALTER TABLE `user_studiengang` DROP `semester`");
        $db->exec("ALTER TABLE `user_studiengang` DROP `abschluss_id`");
    }
}
