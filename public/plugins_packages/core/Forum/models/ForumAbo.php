<?php

/**
 * ForumAbo.php - Handle abonnements of areas/threads or even the whole forum
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 3 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GPL version 3
 * @category    Stud.IP
 */

require_once('lib/messaging.inc.php');

class ForumAbo
{
    /**
     * add the passed user as a watcher for the passed topic (including all
     * current and future childs)
     *
     * @param string $topic_id
     * @param string $user_id
     */
    public static function add($topic_id, $user_id = null)
    {
        if (!$user_id) $user_id = $GLOBALS['user']->id;

        $stmt = DBManager::get()->prepare("REPLACE INTO forum_abo_users
            (topic_id, user_id) VALUEs (?, ?)");
        $stmt->execute([$topic_id, $user_id]);
    }

    /**
     * remove the passed user as a watcher from the passed topic (including all
     * current and future childs)
     *
     * @param string $topic_id
     * @param string $user_id
     */
    public static function delete($topic_id, $user_id = null)
    {
        if (!$user_id) $user_id = $GLOBALS['user']->id;

        $stmt = DBManager::get()->prepare("DELETE FROM forum_abo_users
            WHERE topic_id = ? AND user_id = ?");
        $stmt->execute([$topic_id, $user_id]);
    }

    /**
     * check, if the passed user watches the passed topic. If no user_id is passed,
     * the currently logged in user is used
     *
     * @param string $topic_id
     * @param string $user_id
     *
     * @return boolean returns true if user is watching, false otherwise
     */
    public static function has($topic_id, $user_id = null)
    {
        if (!$user_id) $user_id = $GLOBALS['user']->id;

        $stmt = DBManager::get()->prepare("SELECT COUNT(*) FROM forum_abo_users
            WHERE topic_id = ? AND user_id = ?");
        $stmt->execute([$topic_id, $user_id]);

        return $stmt->fetchColumn() > 0 ? true : false;
    }

    /**
     * send out the notification messages for the passed topic. The contents
     * and a link directly to the topic are added to the message.
     *
     * @param string $topic_id
     */
    public static function notify($topic_id)
    {
        // send message to all abo-users
        $db = DBManager::get();
        $messaging = new ForumBulkMail();
        // $messaging = new Messaging();

        // get all parent topic-ids, to find out which users to notify
        $path = ForumEntry::getPathToPosting($topic_id);

        // fetch all users to notify, exclude current user
        $stmt = $db->prepare("SELECT DISTINCT user_id
            FROM forum_abo_users
            WHERE topic_id IN (:topic_ids)
                AND user_id != :user_id");
        $stmt->bindParam(':topic_ids', array_keys($path), StudipPDO::PARAM_ARRAY);
        $stmt->bindParam(':user_id', $GLOBALS['user']->id);
        $stmt->execute();

        // get details for topic
        $topic = ForumEntry::getConstraints($topic_id);

        $template_factory = new Flexi_TemplateFactory(dirname(__FILE__) . '/../views');
        $template = $template_factory->open('index/_mail_notification');

        // notify users
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_id = $data['user_id'];

            // don't notify user if view permission is not granted
            if (!ForumPerm::has('view', $topic['seminar_id'], $user_id)) {
                continue;
            }

            $user = User::find($user_id);

            // check if user wants an email for all or selected messages only
            $force_email = false;
            if ($messaging->user_wants_email($user_id)) {
                $force_email = true;
            }
            // do not send mails when account is locked or expired
            $expiration = UserConfig::get($user->id)->EXPIRATION_DATE;
            if ($user->locked || ($expiration > 0 && $expiration < time())) {
                $force_email = false;
            }
            $parent_id = ForumEntry::getParentTopicId($topic['topic_id']);

            setTempLanguage($data['user_id']);
            $notification = sprintf(_("%s hat einen Beitrag geschrieben"), ($topic['anonymous'] ? _('Anonym') : $topic['author']));
            restoreLanguage();

            PersonalNotifications::add(
                $user_id,
                UrlHelper::getUrl(
                    'plugins.php/coreforum/index/index/' . $topic['topic_id'] . '#' . $topic['topic_id'],
                    ['cid' => $topic['seminar_id']],
                    true
                ),
                $notification,
                "forumposting_" . $topic['topic_id'],
                Icon::create('forum', 'clickable')
            );

            if ($force_email) {
                $title = implode(' >> ', ForumEntry::getFlatPathToPosting($topic_id));

                $subject = _('[Forum]') . ' ' . ($title ?: _('Neuer Beitrag'));

                $htmlMessage = $template->render(
                    compact('user_id', 'topic', 'path')
                );

                $textMessage = trim(kill_format($htmlMessage));

                $userWantsHtml = UserConfig::get($user_id)->MAIL_AS_HTML;

                StudipMail::sendMessage(
                    $user->email,
                    $subject,
                    $textMessage,
                    $userWantsHtml ? $htmlMessage : null
                );
            }
        }

        $messaging->bulkSend();
    }

    /**
     * Removes all abos for a given course and user
     *
     * @param String $course_id Id of the course
     * @param String $user_id   Id of the user
     * @return int number of removed abos
     */
    public static function removeForCourseAndUser($course_id, $user_id)
    {
        $query = "DELETE FROM `forum_abo_users`
                  WHERE `user_id` = :user_id
                  AND `topic_id` IN (
                      SELECT `topic_id`
                      FROM `forum_entries`
                      WHERE `seminar_id` = :course_id
                  )";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':course_id', $course_id);
        $statement->bindValue(':user_id', $user_id);
        $statement->execute();
        return $statement->rowCount();
    }
}
