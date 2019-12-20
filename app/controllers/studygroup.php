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
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setTitle(_('Studiengruppen suchen'));
        Navigation::activateItem('/community/studygroups/browse');
        PageLayout::setHelpKeyword('Basis.SuchenStudiengruppen');

        // add skip link
        SkipLinks::addIndex(
            Navigation::getItem('/community/studygroups/browse')->getTitle(),
            'layout_content',
            100
        );

        $this->setupSidebar();
    }

    /**
     * Displays a pageable and sortable overview of all studygoups combined with
     * a search form to query for specific studygroup
     *
     * @param int    $page Page to display
     * @param string $sort Sorting order and direction
     */
    public function browse_action($page = 1, $sort = 'founded_desc')
    {
        $this->sort             = preg_replace('/\\W/', '', $sort);
        $this->page             = (int) $page;
        $this->user             = $GLOBALS['user'];
        $this->searchtext       = Request::get('q');
        $this->closed           = Request::int('closed', Request::submitted('q') ? 0 : null);
        $this->entries_per_page = Config::get()->ENTRIES_PER_PAGE;

        if (Request::int('reset-search')) {
            $this->sort       = 'founded_desc';
            $this->page       = 1;
            $this->searchtext = null;
            $this->closed     = null;
        } elseif ($this->searchtext && mb_strlen($this->searchtext) < 3) {
            PageLayout::postInfo(_('Der Suchbegriff ist zu kurz.'));

            $this->searchtext = null;
        }

        list($this->sort_type, $this->sort_order) = explode('_', $this->sort);

        $this->anzahl = StudygroupModel::countGroups($this->searchtext, $this->closed);
        $this->groups = StudygroupModel::getAllGroups(
            $this->sort,
            ($this->page - 1) * $this->entries_per_page,
            $this->entries_per_page,
            $this->searchtext,
            $this->closed
        );

        if ($this->searchtext && $this->anzahl === 0) {
            PageLayout::postInfo(_('Es wurden keine Studiengruppen für den Suchbegriff gefunden'));
        }

        if (!empty($this->groups)) {
            if ($this->page < 1 || $this->page > ceil($this->anzahl / $this->entries_per_page)) {
                $this->page = 1;
            }
        }

        $this->q = $this->searchtext;
    }

    /**
     * Adds the creation action and the search widget to the sidebar.
     */
    private function setupSidebar()
    {
        $sidebar = Sidebar::get();

        if ($GLOBALS['perm']->have_perm('autor')) {
            $actions = new ActionsWidget();
            $actions->addLink(
                _('Neue Studiengruppe anlegen'),
                URLHelper::getURL('dispatch.php/course/wizard', ['studygroup' => 1]),
                Icon::create('add')
            )->asDialog();
            $sidebar->addWidget($actions);
        }

        $search = new SearchWidget($this->url_for('studygroup/browse'));
        $search->addNeedle(_('Suchbegriff'), 'q', true);
        $search->addFilter(_('Geschlossene Studiengruppen'), 'closed');
        $sidebar->addWidget($search);
    }
}
