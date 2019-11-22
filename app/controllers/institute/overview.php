<?php
# Lifter010: TODO

/*
 * Copyright (C) 2014 - Arne Schröder <schroeder@data-quest.de>
 *
 * formerly institut_main.php - Die Eingangsseite fuer ein Institut
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class Institute_OverviewController extends AuthenticatedController
{
    protected $allow_nobody = true;

    function before_filter(&$action, &$args) {

        //Check if anonymous access is really allowed:
        $config = Config::get();
        if (($config->ENABLE_FREE_ACCESS && ($config->ENABLE_FREE_ACCESS == 'courses_only'))) {
            $this->allow_nobody = false;
        }

        if (Request::option('auswahl')) {
            Request::set('cid', Request::option('auswahl'));
        }

        parent::before_filter($action, $args);

        checkObject();
        $this->institute = Institute::findCurrent();
        if (!$this->institute) {
            throw new CheckObjectException(_('Sie haben kein Objekt gewählt.'));
        }
        $this->institute_id = $this->institute->id;

        //set visitdate for institute, when coming from meine_seminare
        if (Request::option('auswahl')) {
            object_set_visit($this->institute_id, "inst");
        }

        //gibt es eine Anweisung zur Umleitung?
        if (Request::get('redirect_to')) {
            $query_parts = explode('&', mb_stristr(urldecode($_SERVER['QUERY_STRING']), 'redirect_to'));
            list( , $where_to) = explode('=', array_shift($query_parts));
            $new_query = $where_to . '?' . join('&', $query_parts);
            page_close();
            $new_query = preg_replace('/[^:0-9a-z+_\-\.#?&=\/]/i', '', $new_query);
            header('Location: '.URLHelper::getURL($new_query, ['cid' => $this->institute_id]));
            die;
        }

        PageLayout::setHelpKeyword("Basis.Einrichtungen");
        PageLayout::setTitle($this->institute->getFullName() . " - " ._("Kurzinfo"));
        Navigation::activateItem('/course/main/info');

    }

    /**
     * show institute overview page
     *
     * @return void
     */
    function index_action()
    {
        $this->sidebar = Sidebar::get();
        $this->sidebar->setImage('sidebar/institute-sidebar.png');

        if (get_config('NEWS_RSS_EXPORT_ENABLE') && $this->institute_id){
            $rss_id = StudipNews::GetRssIdFromRangeId($this->institute_id);
            if ($rss_id) {
                PageLayout::addHeadElement('link', ['rel'   => 'alternate',
                                                         'type'  => 'application/rss+xml',
                                                         'title' => 'RSS',
                                                         'href'  => 'rss.php?id='.$rss_id]);
            }
        }

        URLHelper::bindLinkParam("inst_data", $this->institut_main_data);

        // (un)subscribe to institute
        if (Config::get()->ALLOW_SELFASSIGN_INSTITUTE && $GLOBALS['user']->id !== 'nobody' && !$GLOBALS['perm']->have_perm('admin')) {
            $widget = new ActionsWidget();
            if (! $GLOBALS['perm']->have_studip_perm('user', $this->institute_id)) {
                $url = URLHelper::getURL('dispatch.php/institute/overview', [
                    'follow_inst' => 'on'
                ]);
                $widget->addLink(_('Einrichtung abonnieren'), $url);
            } elseif (! $GLOBALS['perm']->have_studip_perm('autor', $this->institute_id)) {
                $url = URLHelper::getURL('dispatch.php/institute/overview', [
                    'follow_inst' => 'off'
                ]);
                $widget->addLink(_('Austragen aus der Einrichtung'), $url);
            }
            $this->sidebar->addWidget($widget);

            if (! $GLOBALS['perm']->have_studip_perm('user', $this->institute_id) AND (Request::option('follow_inst') == 'on')) {
                $query = "INSERT IGNORE INTO user_inst
                          (user_id, Institut_id, inst_perms)
                          VALUES (?, ?, 'user')";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([
                    $GLOBALS['user']->user_id,
                    $this->institute_id
                ]);
                if ($statement->rowCount() > 0) {
                    StudipLog::log('INST_USER_ADD', $this->institute_id, $GLOBALS['user']->user_id, 'user');
                    NotificationCenter::postNotification('UserInstitutionDidCreate', $this->institute_id, $GLOBALS['user']->user_id);

                    PageLayout::postMessage(MessageBox::success(_("Sie haben die Einrichtung abonniert.")));
                    header('Location: '.URLHelper::getURL('', ['cid' => $this->institute_id]));
                    die;
                }
            } elseif (! $GLOBALS['perm']->have_studip_perm('autor', $this->institute_id) AND (Request::option('follow_inst') == 'off')) {
                $query = "DELETE FROM user_inst
                          WHERE user_id = ?  AND Institut_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([
                    $GLOBALS['user']->user_id,
                    $this->institute_id
                ]);
                if ($statement->rowCount() > 0) {
                    StudipLog::log('INST_USER_DEL', $this->institute_id, $GLOBALS['user']->user_id, 'user');
                    NotificationCenter::postNotification('UserInstitutionDidDelete', $this->institute_id, $GLOBALS['user']->user_id);
                    PageLayout::postMessage(MessageBox::success(_("Sie haben sich aus der Einrichtung ausgetragen.")));
                    header('Location: '.URLHelper::getURL('', ['cid' => $this->institute_id]));
                    die;
                }
            }
        }

        // Fetch news
        $response = $this->relay('news/display/' . $this->institute_id);
        $this->news = $response->body;

        // Fetch  votes
        if (get_config('VOTE_ENABLE')) {
            $response = $this->relay('evaluation/display/' . $this->institute_id . '/institute');
            $this->evaluations = $response->body;

            $response = $this->relay('questionnaire/widget/' . $this->institute_id . '/institute');
            $this->questionnaires = $response->body;
        }

        // Fetch dates
        $response = $this->relay("calendar/contentbox/display/$this->institute_id/1210000");
        $this->dates = $response->body;
    }

}
