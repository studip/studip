<?php
/*
 * Forum.class.php - Forum
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <till.gloeggler@elan-ev.de>
 * @copyright   2011 ELAN e.V. <http://www.elan-ev.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once 'app/models/smiley.php';
require_once 'controllers/forum_controller.php';
require_once 'lib/activities/Activity.php';
require_once 'models/ForumEntry.php';

// Setup autoloader
StudipAutoloader::addAutoloadPath(__DIR__ . '/models');

// Notifications
NotificationCenter::addObserver('CoreForum', 'overviewDidClear', "OverviewDidClear");
NotificationCenter::addObserver('CoreForum', 'removeAbosForUserAndCourse', 'UserDidLeaveCourse');

NotificationCenter::addObserver('ForumActivity', 'newEntry', 'ForumAfterInsert');
NotificationCenter::addObserver('ForumActivity', 'updateEntry', 'ForumAfterUpdate');
NotificationCenter::addObserver('ForumActivity', 'deleteEntry', 'ForumBeforeDelete');

class CoreForum extends StudipPlugin implements ForumModule
{
    /**
     * This method dispatches all actions.
     *
     * @param string $unconsumed_path  part of the dispatch path that was not consumed
     */
    public function perform($unconsumed_path)
    {
        // Add JS and StyleSheet to header
        $this->addScript('javascript/forum.js');
        $this->addStylesheet('stylesheets/forum.less');

        parent::perform($unconsumed_path);
    }

    /* interface method */
    public function getTabNavigation($course_id)
    {
        $navigation = new Navigation(_('Forum'), PluginEngine::getURL($this, [], 'index'));
        $navigation->setImage(Icon::create('forum', 'info_alt'));

        // add main third-level navigation-item
        $navigation->addSubNavigation('index', new Navigation(_('Übersicht'), PluginEngine::getURL($this, [], 'index')));

        if (ForumPerm::has('fav_entry', $course_id)) {
            $navigation->addSubNavigation('newest', new Navigation(_("Neue Beiträge"), PluginEngine::getURL($this, [], 'index/newest')));
            $navigation->addSubNavigation('latest', new Navigation(_("Letzte Beiträge"), PluginEngine::getURL($this, [], 'index/latest')));
            $navigation->addSubNavigation('favorites', new Navigation(_('Gemerkte Beiträge'), PluginEngine::getURL($this, [], 'index/favorites')));

            // mass-administrate the forum
            if (ForumPerm::has('admin', $course_id)) {
                $navigation->addSubNavigation('admin', new Navigation(_('Administration'), PluginEngine::getURL($this, [], 'admin')));
            }
        }

        return ['forum2' => $navigation];
    }

    /* interface method */
    public function getIconNavigation($course_id, $last_visit, $user_id = null)
    {
        if ($GLOBALS['perm']->have_studip_perm('user', $course_id)) {
            $num_entries = ForumVisit::getCount($course_id, ForumVisit::getVisit($course_id));
            $text = ForumHelpers::getVisitText($num_entries, $course_id);
        } else {
            $num_entries = 0;
            $text = 'Forum';
        }

        $navigation = new Navigation('forum', PluginEngine::getURL($this, [], 'index/enter_seminar'));
        $navigation->setBadgeNumber($num_entries);

        if ($num_entries > 0) {
            $navigation->setImage(Icon::create('forum+new', 'attention', ["title" => $text]));
        } else {
            $navigation->setImage(Icon::create('forum', 'inactive', ["title" => $text]));
        }

        return $navigation;
    }

    /**
     * This method is called, whenever an user clicked to clear the visit timestamps
     * and set everything as visited
     *
     * @param object $notification
     * @param string $user_id
     */
    public static function overviewDidClear($notification, $user_id)
    {
        $stmt = DBManager::get()->prepare("UPDATE forum_visits
            SET visitdate = UNIX_TIMESTAMP(), last_visitdate = UNIX_TIMESTAMP()
            WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }

    /**
     * This method is called whenever a user is removed from a course and thus
     * the forum abos will be removed.
     *
     * @param object $notification
     * @param string $course_id
     * @param string $user_id
     */
    public static function removeAbosForUserAndCourse($notification, $course_id, $user_id)
    {
        ForumAbo::removeForCourseAndUser($course_id, $user_id);
    }

    public function getInfoTemplate($course_id)
    {
        return null;
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    /* * IMPLEMENTATION OF METHODS FROM FORUMMODULE-INTERFACE  * */
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    public function getLinkToThread($issue_id)
    {
        if ($topic_id = ForumIssue::getThreadIdForIssue($issue_id)) {
            return PluginEngine::getLink($this, [], '/index/index/' . $topic_id);
        }

        return false;
    }

    public function setThreadForIssue($issue_id, $title, $content)
    {
        ForumIssue::setThreadForIssue(Context::getId(), $issue_id, $title, $content);
    }

    public function getNumberOfPostingsForUser($user_id, $seminar_id = null)
    {
        return ForumEntry::countUserEntries($user_id, $seminar_id);
    }

    public function getNumberOfPostingsForIssue($issue_id)
    {
        $topic_id = ForumIssue::getThreadIdForIssue($issue_id);

        return $topic_id ? ForumEntry::countEntries($topic_id) : 0;
    }

    public function getNumberOfPostingsForSeminar($seminar_id)
    {
        return floor(ForumEntry::countEntries($seminar_id));
    }

    public function getNumberOfPostings()
    {
        return ForumEntry::countAllEntries();
    }

    public function getEntryTableInfo()
    {
        return [
            'table'      => 'forum_entries',
            'content'    => 'content',
            'chdate'     => 'chdate',
            'seminar_id' => 'seminar_id',
            'user_id'    => 'user_id'
        ];
    }

    public function getTopTenSeminars()
    {
        return ForumEntry::getTopTenSeminars();
    }

    public function migrateUser($user_from, $user_to)
    {
        return ForumEntry::migrateUser($user_from, $user_to);
    }

    public function deleteContents($seminar_id)
    {
        return ForumEntry::delete($seminar_id);
    }

    public function getDump($seminar_id)
    {
        return ForumEntry::getDump($seminar_id);
    }

    public static function getDescription()
    {
        return _('Textbasierte und zeit- und ortsunabhängige '.
            'Diskursmöglichkeit. Lehrende können parallel zu '.
            'Veranstaltungsthemen Fragen stellen, die von den Studierenden '.
            'per Meinungsaustausch besprochen werden.');
    }
}
