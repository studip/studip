<?
class AddOptionStep152 extends Migration
{
    function description ()
    {
        return 'add config option ENABLE_PROTECTED_DOWNLOAD_RESTRICTION';
    }

    function up ()
    {
        $db = DBManager::get();
        $db->exec("
            INSERT IGNORE INTO `config` (
            `config_id` ,
            `parent_id` ,
            `field` ,
            `value` ,
            `is_default` ,
            `type` ,
            `range` ,
            `section` ,
            `position` ,
            `mkdate` ,
            `chdate` ,
            `description` ,
            `comment` ,
            `message_template`
            )
            VALUES (
            MD5( 'ENABLE_PROTECTED_DOWNLOAD_RESTRICTION' ) , '', 'ENABLE_PROTECTED_DOWNLOAD_RESTRICTION',
            '0', '1', 'boolean', 'global', '', '0', UNIX_TIMESTAMP( ) , UNIX_TIMESTAMP( ) ,
            'Schaltet die Überprüfung (fester Teilnehmerkreis) bei Download von als geschützt markierten Dateien ein',
            '', ''
            )
            ");
 
    }

    function down ()
    {
        $db = DBManager::get();

        $db->exec("DELETE FROM config WHERE field = 'ENABLE_PROTECTED_DOWNLOAD_RESTRICTION'");
    }
}
?>
