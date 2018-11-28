<?
class HighPriorityMessages extends Migration
{
    function description ()
    {
        return 'Allows forwarding and displaying of messages with a high priority';
    }

    function up ()
    {
        $db = DBManager::get();

        $db->exec("INSERT INTO `config` (".
            "`config_id`,`parent_id`,`field`,`value`,`is_default`,`type`,`range`,`section`,".
            " `position`,`mkdate`,`chdate`,`description`,`comment`,`message_template`)".
            "VALUES (MD5( 'MESSAGE_PRIORITY' ) , '', 'MESSAGE_PRIORITY', '0', '1', 'boolean',".
            " 'global', '', '0', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'If enabled, messages of high priority are displayed reddish','', '')");
        $db->exec("ALTER TABLE `message` ADD `priority` ENUM( 'normal', 'high' ) NOT NULL DEFAULT 'normal'");
        $this->announce("done.");
    }

    function down ()
    {
        $db = DBManager::get();

        $db->exec("DELETE FROM config WHERE config_id = MD5('MESSAGE_PRIORITY')");
        $db->exec("ALTER TABLE `message` DROP `priority`");
        $this->announce("done.");
    }
}
?>
