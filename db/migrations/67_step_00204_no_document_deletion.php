<?php
class Step00204NoDocumentDeletion extends Migration
{

    function description()
    {
        return 'add field `author_name` to table dokumente and fill it with current names';
    }

    function up()
    {
        $db = DBManager::get();
        $db->exec("ALTER TABLE `dokumente` ADD `author_name` VARCHAR( 255 ) NOT NULL DEFAULT ''");
        $db->exec(" UPDATE dokumente
                    LEFT JOIN auth_user_md5 USING ( user_id )
                    LEFT JOIN user_info USING ( user_id )
                    SET author_name =  TRIM( CONCAT( title_front, ' ', Vorname, ' ', Nachname, IF( title_rear != '', CONCAT( ', ', title_rear ) , '' ) ) )");
    }

    function down()
    {
        $db = DBManager::get();
        $db->exec("ALTER TABLE `dokumente` DROP `author_name`");
    }
}
?>
