<?php
# Lifter010: TODO
/*
 * StudygroupController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Klaßen <aklassen@uos.de>
 * @copyright   2009-2010 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     studygroup
 */

class StudygroupController extends AuthenticatedController
{
    private static $request_params = ['searchtext', 'closed_groups'];
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setTitle(_('Studiengruppen suchen'));
        Navigation::activateItem('/community/studygroups/browse');
        PageLayout::setHelpKeyword('Basis.SuchenStudiengruppen');
        // add skip link
        SkipLinks::addIndex(Navigation::getItem('/community/studygroups/browse')->getTitle(), 'layout_content', 100);

        $this->setupSidebar();
    }

    /**
     * Displays a pageable and sortable overview of all studygoups combined with
     * a search form to query for specific studygroup
     *
     * @param $page
     * @param $sort
     */
    public function browse_action($page = 1, $sort = "founded_desc")
    {
        $this->sort             = preg_replace('/\\W/', '', $sort);
        $this->page             = intval($page);
        $this->user             = $GLOBALS['user'];
        $searchtext             = Request::get('searchtext');
        $closed_groups          = Request::get('closedGroups');
        $this->entries_per_page = Config::get()->ENTRIES_PER_PAGE;

        if (Request::int('reset-search')) {
            $this->clear_flash();
            $this->search = null;
            $this->page   = 1;
            $this->sort   = 'founded_desc';
        }

        $this->lower_bound = ($this->page - 1) * $this->entries_per_page;

        list ($this->sort_type, $this->sort_order) = explode('_', $this->sort);

        if (empty($searchtext) && isset($this->flash['searchtext'])) {
            $searchtext = $this->flash['searchtext'];
        }

        if (empty($closed_groups) && isset($this->flash['closed_groups'])) {
            $closed_groups = $this->flash['closed_groups'];
        }

        if (!empty($searchtext)) {
            $groups = StudygroupModel::getAllGroups(
                $this->sort,
                $this->lower_bound,
                $this->entries_per_page,
                $searchtext,
                $closed_groups
            );

            foreach (self::$request_params as $value) {
                $this->flash[$value] = $$value;
                $this->flash->keep($value);
            }

            $this->anzahl = StudygroupModel::countGroups($searchtext, $closed_groups);

            if (Request::submitted('searchtext') && empty($searchtext)) {
                PageLayout::postInfo(_('Der Suchbegriff ist zu kurz.'));
            }
            if ((int)$this->anzahl === 0) {
                PageLayout::postInfo(_('Es wurden keine Studiengruppen für den Suchbegriff gefunden'));
                $this->clear_flash();
            }
        } else {
            $this->anzahl = StudygroupModel::countGroups();
            $groups       = StudygroupModel::getAllGroups(
                $this->sort,
                $this->lower_bound,
                $this->entries_per_page,
                null,
                $closed_groups
            );
        }

        if (!empty($groups)) {
            if ($this->page < 1 || $this->page > ceil($this->anzahl / $this->entries_per_page)) {
                $this->page = 1;
            }
        }

        $this->groups = $groups;
        $this->closed_groups = $closed_groups;
    }

    private function setupSidebar()
    {
        $sidebar = Sidebar::get();
        $sidebar->setImage('sidebar/studygroup-sidebar.png');

        if ($GLOBALS['perm']->have_perm('autor')) {
            $actions = new ActionsWidget();
            $actions->addLink(
                _('Neue Studiengruppe anlegen'),
                URLHelper::getURL('dispatch.php/course/wizard', ['studygroup' => 1]),
                Icon::create('add')
            )->asDialog();
            $sidebar->addWidget($actions);
        }

        if (isset($this->flash['searchtext'])) {
            $default_value = $this->flash['searchtext'];
        }
        $search = new SearchWidget($this->url_for('studygroup/browse'));
        $search->addNeedle(
            _('Suchbegriff'),
            'searchtext',
            true,
            null,
            null,
            $default_value
        );
        $search->addFilter(_('Geschlossene Studiengruppen'), 'closedGroups');
        $sidebar->addWidget($search);
    }

    /**
     * Clear flash values
     */
    private function clear_flash()
    {
        foreach (self::$request_params as $value) {
            unset($this->flash[$value]);
        }
    }
}
