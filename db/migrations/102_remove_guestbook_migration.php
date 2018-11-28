<?php

class RemoveGuestbookMigration extends Migration
{

    function description()
    {
        return 'puts all data from active guestbooks into blubber';
    }

    function up()
    {
        $db = DBManager::get();
        $guestbook_text = _("Gästebuch");
        $db->exec("
            INSERT IGNORE INTO blubber (topic_id, parent_id, root_id, context_type, name, description, mkdate, chdate, Seminar_id, user_id, external_contact)
                SELECT MD5(CONCAT('guestbook_', user_info.user_id)), '0', MD5(CONCAT('guestbook_', user_info.user_id)), 'public', ".$db->quote($guestbook_text).", ".$db->quote($guestbook_text).", user_info.mkdate, user_info.mkdate, user_info.user_id, user_info.user_id, '0'
                FROM user_info
                WHERE user_info.guestbook = '1'
        ");
        $db->exec("
            INSERT IGNORE INTO blubber (topic_id, parent_id, root_id, context_type, name, description, mkdate, chdate, Seminar_id, user_id, external_contact)
                SELECT guestbook.post_id, MD5(CONCAT('guestbook_', guestbook.range_id)), MD5(CONCAT('guestbook_', guestbook.range_id)), 'public', ".$db->quote($guestbook_text).", guestbook.content, guestbook.mkdate, guestbook.mkdate, guestbook.range_id, guestbook.user_id, '0'
                FROM guestbook
                    INNER JOIN user_info ON (guestbook.range_id = user_info.user_id)
                WHERE user_info.guestbook = '1'
        ");
        $db->exec("
            DROP TABLE guestbook
        ");
    }

    function down()
    {
    }
}
