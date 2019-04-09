<?php
/**
 * PublicCoursesController - Shows an overview of all courses with public
 * access
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 */

class PublicCoursesController extends AuthenticatedController
{
    protected $allow_nobody = true;

    /**
     * Initializes the controller.
     *
     * @param string $action Action to execute
     * @param array  $args   Passed parameters
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!Config::get()->ENABLE_FREE_ACCESS) {
            throw new AccessDeniedException(_('Öffentliche Veranstaltungen sind nicht aktiviert.'));
        }

        Navigation::activateItem('/browse');

        PageLayout::setTitle(_('Öffentliche Veranstaltungen'));
        PageLayout::setHelpKeyword('Basis.SymboleFreieVeranstaltungen');

        // we are definitely not in an lexture or institute
        closeObject();
    }

    /**
     * Displays a list of all public courses
     */
    public function index_action()
    {
        $query = "SELECT Seminar_id, seminare.Name AS name, seminare.status, seminare.Schreibzugriff,
                         Institute.Name AS Institut, Institut_id AS id
                  FROM seminare
                  LEFT JOIN Institute USING (Institut_id)
                  WHERE Lesezugriff = '0' AND seminare.visible = '1'
                  ORDER BY :order";
        $statement = DBManager::get()->prepare($query);
        $statement->bindParam(':order', Request::option('sortby', 'Name'), StudipPDO::PARAM_COLUMN);
        $statement->execute();

        $seminars = $statement->fetchGrouped(PDO::FETCH_ASSOC);

        $seminars = $this->get_seminar_navigations($seminars);
        $seminars = $this->get_plugin_navigations($seminars);

        $this->seminars = $seminars;
    }

    /**
     * Loads all possible standard plugins for the given seminars and adds
     * a navigation entry for each one.
     *
     * @param array $seminars List of seminars
     * @return array Extended list of seminars
     */
    protected function get_plugin_navigations($seminars)
    {
        foreach ($seminars as $id => $seminar) {
            foreach (PluginEngine::getPlugins('StandardPlugin', $id) as $plugin) {
                $seminars[$id]['navigations'][] = $plugin->getIconNavigation($id, time(), $GLOBALS['user']->id);
            }
        }
        return $seminars;
    }

    /**
     * Adds all navigation entries for each passed seminar.
     *
     * @param array $seminars List of seminars
     * @return array Extended list of seminars
     */
    protected function get_seminar_navigations($seminars)
    {
        if (empty($seminars)) {
            return [];
        }

        foreach ($seminars as $id => $seminar) {
            $seminar['navigations'] = [];

            foreach (words('forum files news scm schedule wiki vote literature') as $key) {
                $seminar['navigations'][$key] = false;
            }

            $seminars[$id] = $seminar;
        }

        $seminar_ids = array_keys($seminars);

        // Documents
        $query = "SELECT range_id, COUNT(*) AS count
                  FROM folders
                  INNER JOIN file_refs ON folder_id = folders.id
                  WHERE range_id IN (?) AND range_type = 'course'
                  AND folder_type IN ('RootFolder', 'StandardFolder')
                  GROUP BY range_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar_ids]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $nav = new Navigation('files', 'dispatch.php/course/files/index');
            $nav->setImage(Icon::create('files', 'inactive', ["title" => sprintf(_('%s Dokumente'),$row['count'])]));
            $seminars[$row['range_id']]['navigations']['files'] = $nav;
        }

        // News
        $query = "SELECT range_id, COUNT(*) AS count
                  FROM news_range
                  LEFT JOIN news USING (news_id)
                  WHERE range_id IN (?)
                  GROUP BY range_id
                  HAVING count > 0";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar_ids]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $nav = new Navigation('news', '');
            $nav->setImage(Icon::create('news', 'inactive', ["title" => sprintf(_('%s Ankündigungen'),$row['count'])]));
            $seminars[$row['range_id']]['navigations']['news'] = $nav;
        }

        // Information
        $query = "SELECT range_id, COUNT(*) AS count
                  FROM scm
                  WHERE range_id IN (?)
                  GROUP BY range_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar_ids]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $nav = new Navigation('scm', 'dispatch.php/course/scm');
            $nav->setImage(Icon::create('infopage', 'inactive', ["title" => sprintf(_('%s Einträge'),$row['count'])]));
            $seminars[$row['range_id']]['navigations']['scm'] = $nav;
        }

        // Literature
        $query = "SELECT range_id, COUNT(list_id) AS count
                  FROM lit_list
                  WHERE range_id IN (?) AND visibility = 1
                  GROUP BY range_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar_ids]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $nav = new Navigation('literature', 'dispatch.php/course/literatur');
            $nav->setImage(Icon::create('literature', 'inactive', ["title" => sprintf(_('%s Literaturlisten'),$row['count'])]));
            $seminars[$row['range_id']]['navigations']['literature'] = $nav;
        }

        // Appointments
        $query = "SELECT range_id, COUNT(*) AS count
                  FROM termine
                  WHERE range_id IN (?)
                  GROUP BY range_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar_ids]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $nav = new Navigation('schedule', 'dispatch.php/course/dates');
            $nav->setImage(Icon::create('schedule', 'inactive', ["title" => sprintf(_('%s Termine'),$row['count'])]));
            $seminars[$row['range_id']]['navigations']['schedule'] = $nav;
        }

        // Wiki
        if (Config::get()->WIKI_ENABLE) {
            $query = "SELECT range_id, COUNT(DISTINCT keyword) AS count
                      FROM wiki
                      WHERE range_id IN (?)
                      GROUP BY range_id";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$seminar_ids]);
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $nav = new Navigation('wiki', 'wiki.php');
                $nav->setImage(Icon::create('wiki', 'inactive', ["title" => sprintf(_('%s WikiSeiten'),$row['count'])]));
                $seminars[$row['range_id']]['navigations']['wiki'] = $nav;
            }
        }

        // Votes
        if (Config::get()->VOTE_ENABLE) {
            $query = "SELECT questionnaire_assignments.range_id, COUNT(DISTINCT questionnaires.questionnaire_id) AS count
                      FROM questionnaires
                          INNER JOIN questionnaire_assignments ON (questionnaire_assignments.questionnaire_id = questionnaires.questionnaire_id)
                      WHERE questionnaires.visible = '1'
                          AND questionnaire_assignments.range_id IN (?)
                      GROUP BY questionnaire_assignments.range_id ";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$seminar_ids]);
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $nav = new Navigation('vote', '#vote');
                $nav->setImage(Icon::create('vote', 'inactive', ["title" => sprintf(_('%s Umfrage(n)'),$row['count'])]));
                $seminars[$row['range_id']]['navigations']['vote'] = $nav;
            }
        }

        return $seminars;
    }
}
