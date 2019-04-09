<?php

class Step00247ForumPerformance extends Migration
{
    function description()
    {
        return 'some performance improvements for the new forum';
    }

    function up()
    {
        DBManager::get()->exec("ALTER TABLE forum_entries ADD latest_chdate INT(11) AFTER mkdate");

        $db = DBManager::get()->query("SELECT * FROM forum_entries");
        $stmt = DBManager::get()->prepare("SELECT chdate FROM forum_entries
            WHERE lft > ? AND rgt < ? AND seminar_id = ?
            ORDER BY chdate DESC LIMIT 1");
        $stmt_update = DBManager::get()->prepare("UPDATE forum_entries 
            SET latest_chdate = ? WHERE topic_id = ?");

        while ($data = $db->fetch(PDO::FETCH_ASSOC)) {
            $stmt->execute([$data['lft'], $data['rgt'], $data['seminar_id']]);
            $chdate = $stmt->fetchColumn();

            if ($chdate) {
                $stmt_update->execute([$chdate, $data['topic_id']]);
            } else {
                $stmt_update->execute([$data['chdate'], $data['topic_id']]);
            }
        }
    }

    function down()
    {
        DBManager::get()->exec("ALTER TABLE forum_entries DROP latest_chdate");
    }
}
