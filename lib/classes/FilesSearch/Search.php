<?php

namespace FilesSearch;

/**
 * Simple class to start a search.
 *
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 4.1
 */
class Search
{
    /**
     * Search for result using a query object.
     *
     * @param Query $query the query object to search for
     *
     * @return Result the result of the search
     */
    public static function query(Query $query)
    {
        return ($query->getQuery() && !$query->hasError()) ? new Result($query) : null;
    }
}
