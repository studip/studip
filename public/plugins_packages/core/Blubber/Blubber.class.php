<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

require_once __DIR__ . '/models/BlubberPosting.class.php';
require_once __DIR__ . '/models/BlubberExternalContact.class.php';
require_once __DIR__ . '/models/BlubberStream.class.php';
require_once __DIR__ . '/models/BlubberProfileNavigation.php';
require_once __DIR__ . '/models/StreamAvatar.class.php';

class Blubber extends StudIPPlugin implements StandardPlugin, SystemPlugin {

    public $config = [];

    /**
     * Constructor of Plugin : adds Navigation and collects information for javascript-update.
     */
    public function __construct() {
        parent::__construct();
        if (UpdateInformation::isCollecting()) {
            $data = Request::getArray("page_info");
            if (mb_stripos(Request::get("page"), "plugins.php/blubber") !== false && isset($data['Blubber'])) {
                $output = [];
                switch ($data['Blubber']['stream']) {
                    case "global":
                        $stream = BlubberStream::getGlobalStream();
                        break;
                    case "course":
                        $stream = BlubberStream::getCourseStream($data['Blubber']['context_id']);
                        break;
                    case "profile":
                        $stream = BlubberStream::getProfileStream($data['Blubber']['context_id']);
                        break;
                    case "thread":
                        $stream = BlubberStream::getThreadStream($data['Blubber']['context_id']);
                        break;
                    case "custom":
                        $stream = new BlubberStream($data['Blubber']['context_id']);
                        break;
                }
                $last_check = $data['Blubber']['last_check'] ? $data['Blubber']['last_check'] : (time() - 5 * 60);

                $new_postings = $stream->fetchNewPostings($last_check, time());

                $factory = new Flexi_TemplateFactory($this->getPluginPath()."/views");
                foreach ($new_postings as $new_posting) {
                    if ($new_posting['root_id'] === $new_posting['topic_id']) {
                        $thread = $new_posting;
                        $template = $factory->open("streams/thread.php");
                        $template->set_attribute('thread', $new_posting);
                    } else {
                        $thread = new BlubberPosting($new_posting['root_id']);
                        $template = $factory->open("streams/comment.php");
                        $template->set_attribute('posting', $new_posting);
                    }
                    BlubberPosting::$course_hashes = ($thread['user_id'] !== $thread['Seminar_id'] ? $thread['Seminar_id'] : false);
                    $template->set_attribute("course_id", $data['Blubber']['seminar_id']);
                    $output['postings'][] = [
                        'posting_id' => $new_posting['topic_id'],
                        'discussion_time' => $new_posting['discussion_time'],
                        'mkdate' => $new_posting['mkdate'],
                        'root_id' => $new_posting['root_id'],
                        'content' => $template->render()
                    ];
                }
                UpdateInformation::setInformation("Blubber.getNewPosts", $output);

                //Events-Queue:
                $db = DBManager::get();
                $events = $db->query(
                    "SELECT event_type, item_id " .
                    "FROM blubber_events_queue " .
                    "WHERE mkdate >= ".$db->quote($last_check)." " .
                    "ORDER BY mkdate ASC " .
                "")->fetchAll(PDO::FETCH_ASSOC);
                UpdateInformation::setInformation("Blubber.blubberEvents", $events);
                $db->exec(
                    "DELETE FROM blubber_events_queue " .
                    "WHERE mkdate < UNIX_TIMESTAMP() - 60 * 60 * 6 " .
                "");
            }
        }
        if (Navigation::hasItem("/community")) {
            $nav = new Navigation($this->getDisplayTitle(), PluginEngine::getURL($this, [], "streams/global"));
            $nav->addSubNavigation("global", new AutoNavigation(_("Globaler Stream"), PluginEngine::getURL($this, [], "streams/global")));
            foreach (BlubberStream::findMine() as $stream) {
                $url = PluginEngine::getURL($this, [], "streams/custom/".$stream->getId());
                $nav->addSubNavigation($stream->getId(), new AutoNavigation($stream['name'], $url));
                if ($stream['defaultstream']) {
                    $nav->setURL($url);
                }
            }
            $nav->addSubNavigation("add", new AutoNavigation(_("Neuen Stream erstellen"), PluginEngine::getURL($this, [], "streams/edit")));
            Navigation::insertItem("/community/blubber", $nav, "online");
            Navigation::getItem("/community")->setURL($nav->getURL());
        }

        if (Navigation::hasItem("/profile")) {
            $nav = new BlubberProfileNavigation(
                _('Blubber'),
                PluginEngine::getURL($this, [], 'streams/profile')
            );
            $this->isActivated(get_userid(Request::username('username',
                $GLOBALS['auth']->auth['uname'])), 'user');
            Navigation::addItem("/profile/blubber", $nav);
        }
    }

    /**
     * Returns a navigation for the tab displayed in the course.
     * @param string $course_id of the course
     * @return \AutoNavigation
     */
    public function getTabNavigation($course_id) {
        $tab = new AutoNavigation($this->getDisplayTitle(), PluginEngine::getLink($this, [], "streams/forum"));
        $tab->setImage(Icon::create('blubber', 'info_alt'));
        return ['blubberforum' => $tab];
    }

    /**
     * Returns a navigation-object with the grey/red icon for displaying in the
     * my_courses.php page.
     * @param string  $course_id
     * @param int $last_visit
     * @param string|null  $user_id
     * @return \AutoNavigation
     */
    public function getIconNavigation($course_id, $last_visit, $user_id = null) {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        $icon = new AutoNavigation($this->getDisplayTitle(), PluginEngine::getLink($this, [], "streams/forum"));
        $db = DBManager::get();
        $last_own_posting_time = (int) $db->query(
            "SELECT mkdate " .
            "FROM blubber " .
            "WHERE user_id = ".$db->quote($user_id)." " .
                "AND Seminar_id = ".$db->quote($course_id)." " .
                "AND context_type = 'course' " .
            "ORDER BY mkdate DESC " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        $new_ones = $db->query(
            "SELECT COUNT(*) " .
            "FROM blubber " .
            "WHERE chdate > ".$db->quote(max($last_visit, $last_own_posting_time))." " .
                "AND user_id != ".$db->quote($user_id)." " .
                "AND Seminar_id = ".$db->quote($course_id)." " .
                "AND context_type = 'course' " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        if ($new_ones) {
            $title = $new_ones > 1 ? sprintf(_("%s neue Blubber"), $new_ones) : _("1 neuer Blubber");
            $icon->setImage(Icon::create('blubber', 'attention', compact('title')));
            $icon->setTitle($title);
            $icon->setBadgeNumber($new_ones);
        } else {
            $icon->setImage(Icon::create('blubber', 'inactive', ["title" => $this->getDisplayTitle()]));
        }
        return $icon;
    }

    /**
     * Returns no template, because this plugin doesn't want to insert an
     * info-template in the course-overview.
     * @param string $course_id
     * @return null
     */
    public function getInfoTemplate($course_id)  {
        return null;
    }

    /**
     * Returns localized title of this plugin.
     * @return type
     */
    public function getDisplayTitle() {
        return _("Blubber");
    }

    public function perform($unconsumed)
    {
        $this->addStylesheet('assets/stylesheets/blubber.less');

        $this->addScripts([
            'autoresize.jquery.min.js',
            'blubber.js',
            'formdata.js',
        ], [], 'assets/javascripts/');

        parent::perform($unconsumed);
    }
}
