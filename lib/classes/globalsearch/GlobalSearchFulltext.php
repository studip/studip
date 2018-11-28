<?php
/**
 * Interface GlobalSearchFulltext
 *
 * Provides defeatable fulltext search with MATCH AGAINST. Only usable on MySQL 5.7.6+ or MariaDB 10.0.5+.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */

interface GlobalSearchFulltext
{
    /**
     * Enables fulltext (MATCH AGAINST) search by creating the corresponding indices.
     */
    public static function enable();

    /**
     * Disables fulltext (MATCH AGAINST) search by removing the corresponding indices.
     */
    public static function disable();

    /**
     * Executes a fulltext (MATCH AGAINST) search in database for the given search term.
     *
     * @param string $search the term to search for.
     * @return string SQL query.
     */
    public static function getFulltextSearch($search);
}
