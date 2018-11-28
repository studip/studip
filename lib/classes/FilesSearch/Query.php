<?php

namespace FilesSearch;

/**
 * Simple class to hold everything about a files search query.
 *
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 4.1
 */
class Query
{
    // minimum length of a search term; this is a constraint of MYSQL too
    const MIN_LENGTH = 4;

    // symbol for sorting by relevance
    const SORT_RELEVANCE = 'relevance';

    // symbol for sorting by chdate
    const SORT_CHDATE = 'chdate';

    private $error;
    private $filter;
    private $page;
    private $query;
    private $resultsPerPage;
    private $sort;
    private $user;

    /**
     * Creates an empty search query.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct(\User $user = null)
    {
        $this->user = isset($user) ? $user : $GLOBALS['user'];
        $this->error = false;
        $this->resultsPerPage = \Config::get()->ENTRIES_PER_PAGE;
        $this->sort = self::SORT_RELEVANCE;
    }

    /**
     * Returns a possible error.
     *
     * @return string if there is an error, returns a string,
     *                otherwise null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Returns the associated filter.
     *
     * @return Filter the associated filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Returns the user performing the search.
     *
     * @return User the associated user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Returns the offset of the first result using the current page
     * and the setting of results per page.
     *
     * @return int the offset of the first result
     */
    public function getOffset()
    {
        return $this->getResultsPerPage() * $this->getPage();
    }

    /**
     * Returns the current page.
     *
     * @return int the current page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Returns the search query term.
     *
     * @return string the search query term
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Returns the current number of results per page.
     *
     * @return int the current number of results per page
     */
    public function getResultsPerPage()
    {
        return $this->resultsPerPage;
    }

    /**
     * Returns the sorting type.
     *
     * @return string the sorting type
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Has the query an error?
     *
     * @return bool either true, if there is an error; false otherwise
     */
    public function hasError()
    {
        return $this->error !== false;
    }

    /**
     * Set the filter of this search query.
     *
     * @param Filter $filter the filter to associate
     *
     * @return Query returns `$this` for chaining
     */
    public function setFilter(Filter $filter)
    {
        if (!$filter->validate()) {
            $this->error = _('Ungültige Filter.');
        } else {
            $this->filter = $filter;
        }

        return $this;
    }

    /**
     * Set the current page.
     *
     * @param int $page the current page
     *
     * @return Query returns `$this` for chaining
     */
    public function setPage($page)
    {
        if ($page < 0) {
            $this->error = _('Die Seitennummer muss positiv sein.');
        } else {
            $this->page = (int) $page;
        }

        return $this;
    }

    /**
     * Set the search query term.
     *
     * @param string $query the search query term
     *
     * @return Query returns `$this` for chaining
     */
    public function setQuery($query)
    {
        if ($query) {
            if (strlen($query) < self::MIN_LENGTH) {
                $this->error = _('Der eingegebene Suchbegriff ist zu kurz');
            }
            $this->query = $query;
        }

        return $this;
    }

    /**
     * Set the number of results per page.
     *
     * @param int $resultsPerPage the number of results per page
     *
     * @return Query returns `$this` for chaining
     */
    public function setResultsPerPage($resultsPerPage)
    {
        if ($resultsPerPage < 1) {
            $this->error = _('Es kann nicht weniger als 1 Ergebnis pro Seite angezeigt werden.');
        } else {
            $this->resultsPerPage = (int) $resultsPerPage;
        }

        return $this;
    }

    /**
     * Set the sort order.
     *
     * @param string $sort either one of the two symbols
     *                     Query::SORT_CHDATE or Query:SORT_RELEVANCE
     *
     * @return Query returns `$this` for chaining
     */
    public function setSort($sort)
    {
        if (!in_array($sort, [self::SORT_CHDATE, self::SORT_RELEVANCE])) {
            $this->error = _('Ungültige Sortierung.');
        } else {
            $this->sort = $sort;
        }

        return $this;
    }
}
