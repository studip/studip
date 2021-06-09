<?php
/*
 * files_dashboard.php - files dashboard page controller
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @since    4.1
 */

use FilesSearch\Filter;
use FilesSearch\Query;
use FilesSearch\Search;

require_once 'app/controllers/files_dashboard/helpers.php';
require_once 'app/controllers/files_dashboard/sidebar.php';

/**
 * This controller shows the files dashboard and the files dashboard's
 * search.
 *
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 4.1
 */
class FilesDashboardController extends AuthenticatedController
{
    use FilesDashboard\Helpers;
    use FilesDashboard\Sidebar;

    /**
     * Callback function being called before an action is executed.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setHelpKeyword('Basis.FilesDashboard'); // set keyword for new help

        $this->user = $GLOBALS['user'];
    }


    // ***** SEARCH *****

    /**
     * Entry point of the controller that displays the dashboard's
     * search page of Stud.IP.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function search_action()
    {
        // FilesController::getRangeLink
        require_once 'app/controllers/files.php';

        if (Navigation::hasItem('/contents/files/search')) {
            Navigation::activateItem('/contents/files/search');
        }
        PageLayout::setTitle(_('Dokumentensuche'));

        $this->query = new Query();
        $this->query
            ->setQuery($this->getQuery())
            ->setPage($this->getPage())
            ->setFilter($this->getFilter())
            ->setSort(Query::SORT_RELEVANCE);

        $this->result = Search::query($this->query);

        $this->addSearchSidebar();
    }

    private function getQuery()
    {
        return \Request::get('q', null);
    }

    /**
     * This method return the requested page.
     *
     * @return int the requested page.
     */
    public function getPage()
    {
        return \Request::get('page', 0);
    }

    /**
     * This method creates a new Filter object from the request.
     *
     * @return Filter the new Filter object
     */
    public function getFilter()
    {
        $filterArray = \Request::getArray('filter');

        $filter = new Filter();

        if (isset($filterArray['category'])) {
            $filter->setCategory($filterArray['category']);
        }

        if (isset($filterArray['semester'])) {
            $filter->setSemester(\Semester::find($filterArray['semester']));
        }

        return $filter;
    }
}
