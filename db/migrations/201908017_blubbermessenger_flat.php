<?php
class BlubbermessengerFlat extends Migration
{
    public function description()
    {
        return "Makes blubber flat so that we only have one global thread.";
    }

    public function up()
    {
        DBManager::get()->exec("
            DELETE FROM blubber_comments
            WHERE mkdate < 1364601600
        "); //30th March 2013

        DBManager::get()->exec("
            INSERT IGNORE INTO blubber_threads
            SET thread_id = 'global',
                context_type = 'public',
                context_id = '',
                user_id = '',
                external_contact = '0',
                `content` = NULL,
                display_class = 'BlubberGlobalThread',
                visible_in_stream = '1',
                commentable = '1',
                metadata = NULL,
                chdate = UNIX_TIMESTAMP(),
                mkdate = UNIX_TIMESTAMP()
        ");
        DBManager::get()->exec("
            UPDATE blubber_comments, blubber_threads
            SET blubber_comments.thread_id = 'global'
            WHERE blubber_comments.thread_id = blubber_threads.thread_id
                AND blubber_threads.context_type = 'public'
        ");
        DBManager::get()->exec("
            INSERT IGNORE INTO blubber_comments (comment_id, thread_id, user_id, external_contact, `content`, network, chdate, mkdate)
            SELECT thread_id, 'global', user_id, external_contact, `content`, null, chdate, mkdate
            FROM blubber_threads
            WHERE context_type = 'public'
                AND thread_id != 'global'
        ");
        DBManager::get()->exec("
            DELETE FROM blubber_threads
            WHERE context_type = 'public'
                AND thread_id != 'global'
        ");

        $select_threads = DBManager::get()->prepare("
            SELECT *
            FROM blubber_threads
            WHERE context_type = 'course'
                AND `content` IS NOT NULL AND `content` != ''
                AND display_class IS NULL
        ");
        $select_threads->execute();
        $insert_comments = DBManager::get()->prepare("
            UPDATE blubber_comments
            SET thread_id = :thread_id
            WHERE thread_id = :old_thread 
        ");
        $insert_comment = DBManager::get()->prepare("
            INSERT INTO blubber_comments
            SET thread_id = :thread_id,
                comment_id = :comment_id,
                user_id = :user_id,
                external_contact = :external_contact,
                `content` = :content,
                network = NULL,
                chdate = :chdate,
                mkdate = :mkdate
        ");
        $delete_thread = DBManager::get()->prepare("
            DELETE FROM blubber_threads
            WHERE thread_id = ?
        ");
        $select_course_main_thread = DBManager::get()->prepare("
            SELECT *
            FROM blubber_threads
            WHERE (content IS NULL OR content = '')
                AND thread_id != :main_thread_id
                AND context_id = :course_id
                AND context_type = 'course'
                AND display_class IS NULL
        ");
        while ($row = $select_threads->fetch(PDO::FETCH_ASSOC)) {
            $course_thread_id = $this->getCourseThreadId($row['context_id']);

            //Alle anderen mit !content löschen
            $select_course_main_thread->execute([
                'main_thread_id' => $course_thread_id,
                'course_id' => $row['context_id']
            ]);
            foreach ($select_course_main_thread->fetchAll(PDO::FETCH_ASSOC) as $row2) {
                $insert_comments->execute([
                    'thread_id' => $course_thread_id,
                    'old_thread' => $row2['thread_id']
                ]);
                $delete_thread->execute([
                    $row2['thread_id']
                ]);
            }

            if ($row['thread_id'] !== $course_thread_id) {
                //Alle Kommentare aus diesem Thread in den Main-Thread verschieben:
                $insert_comments->execute([
                    'thread_id' => $course_thread_id,
                    'old_thread' => $row['thread_id']
                ]);

                if ($row['content']) {
                    //Und noch einen Startkommentar in den Haupthread packen, wenn der zu löschende Thread noch einen Hauptinhalt hatte:
                    $insert_comment->execute([
                        'comment_id' => $row['thread_id'],
                        'thread_id' => $course_thread_id,
                        'user_id' => $row['user_id'],
                        'external_contact' => $row['external_contact'],
                        'content' => $row['content'],
                        'chdate' => $row['chdate'],
                        'mkdate' => $row['mkdate']
                    ]);
                }

                $delete_thread->execute([
                    $row['thread_id']
                ]);
            }
        }

        $select_private_threads = DBManager::get()->prepare("
            SELECT *
            FROM blubber_threads
            WHERE context_type = 'private'
                AND `content` IS NOT NULL AND content != ''
        ");
        $select_private_threads->execute();
        $clean_thread = DBManager::get()->prepare("
            UPDATE blubber_threads 
            SET `content` = NULL
            WHERE thread_id = ?
        ");
        while ($row3 = $select_private_threads->fetch(PDO::FETCH_ASSOC)) {
            $insert_comment->execute([
                'comment_id' => md5($row3['thread_id']."_ersterkommentar"),
                'thread_id' => $row3['thread_id'],
                'user_id' => $row3['user_id'],
                'external_contact' => $row3['external_contact'],
                'content' => $row3['content'],
                'chdate' => $row3['chdate'],
                'mkdate' => $row3['mkdate']
            ]);
            $clean_thread->execute([$row3['thread_id']]);
        }

        DBManager::get()->exec("
            CREATE TABLE IF NOT EXISTS `blubber_threads_unfollow` (
                `thread_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `user_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `mkdate` int(11) DEFAULT NULL,
                PRIMARY KEY (`thread_id`,`user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // activate routes:
        require_once $GLOBALS['STUDIP_BASE_PATH'] . '/app/routes/Blubber.php';
        RESTAPI\ConsumerPermissions::get()->activateRouteMap(new RESTAPI\Routes\Blubber());
    }

    protected function getCourseThreadId($course_id)
    {
        $select = DBManager::get()->prepare("
            SELECT *
            FROM blubber_threads
            WHERE context_type = 'course'
                AND context_id = ?
                AND display_class IS NULL
                AND (`content` IS NULL OR `content` = '')
        ");
        $select->execute([$course_id]);
        $thread_id = $select->fetch(PDO::FETCH_COLUMN, 0);
        if (!$thread_id) {
            $thread_id = md5(uniqid($course_id));
            $insert = DBManager::get()->prepare("
                INSERT IGNORE INTO blubber_threads
                SET thread_id = :thread_id,
                    context_type = 'course',
                    context_id = :course_id,
                    user_id = '',
                    external_contact = '0',
                    `content` = NULL,
                    display_class = NULL,
                    visible_in_stream = '1',
                    commentable = '1',
                    chdate = UNIX_TIMESTAMP(),
                    mkdate = UNIX_TIMESTAMP()
            ");
            $insert->execute([
                'thread_id' => $thread_id,
                'course_id' => $course_id
            ]);
        }
        return $thread_id;
    }

    public function down()
    {
        DBManager::exec("
            DROP TABLE `blubber_threads_unfollow`;
        ");
    }
}
