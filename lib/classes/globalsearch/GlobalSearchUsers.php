<?php
/**
 * GlobalSearchModule for user
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchUsers extends GlobalSearchModule implements GlobalSearchFulltext
{

    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName()
    {
        return _('Personen');
    }

    /**
     * Transforms the search request into an sql statement, that provides the id (same as getId) as type and
     * the object id, that is later passed to the filter.
     *
     * This function is required to make use of the mysql union parallelism
     *
     * @param $search the input query string
     * @param $filter an array with search limiting filter information (e.g. 'category', 'semester', etc.)
     * @return String SQL Query to discover elements for the search
     */
    public static function getSQL($search, $filter, $limit)
    {
        if (!$search) {
            return null;
        }

        // if you're no admin respect visibilty
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $visQuery = get_vis_query('user', 'search') . " AND ";
        }

        $search = str_replace(' ', '% ', $search);
        $query = DBManager::get()->quote("%{$search}%");

        $sql = "SELECT SQL_CALC_FOUND_ROWS user.`user_id`, user.`Vorname`, user.`Nachname`, user.`username`, `user_info`.`title_front`, `user_info`.`title_rear`
                FROM `auth_user_md5` AS user
                JOIN `user_info` USING (`user_id`)
                LEFT JOIN `user_visibility` USING (`user_id`)
                WHERE {$visQuery}
                    (CONCAT_WS(', ', user.`Nachname`, user.`Vorname`) LIKE {$query}
                        OR CONCAT_WS(' ', user.`Nachname`, user.`Vorname`, user.`Nachname`) LIKE {$query}
                        OR `username` LIKE {$query}
                    )
                ORDER BY user.`Nachname`, user.`Vorname`
                LIMIT " . $limit;
        return $sql;
    }

    /**
     * Returns an array of information for the found element. Following informations (key: description) are necessary
     *
     * - name: The name of the object
     * - url: The url to send the user to when he clicks the link
     *
     * Additional informations are:
     *
     * - additional: Subtitle for the hit
     * - expand: Url if the user further expands the search
     * - img: Avatar for the
     *
     * @param $id
     * @param $search
     * @return mixed
     */
    public static function filter($data, $search)
    {
        $user = User::buildExisting($data);
        $result = [
            'id'         => $user->id,
            'name'       => self::markMany($user->getFullname(), $search),
            'url'        => URLHelper::getURL('dispatch.php/profile', ['username' => $user->username]),
            'additional' => '<a href="' . URLHelper::getLink('dispatch.php/profile', ['username' => $user->username]) . '">' . self::mark($user->username, $search) . '</a>',
            'expand'     => self::getSearchURL($search),
            'img'        => Avatar::getAvatar($user->id)->getUrl(Avatar::MEDIUM),
        ];
        return $result;
    }

    /**
     * Returns the URL that can be called for a full search.
     *
     * @param string $searchterm what to search for?
     * @return URL to the full search, containing the searchterm and the category
     */
    public static function getSearchURL($searchterm)
    {
        return URLHelper::getURL('dispatch.php/search/globalsearch', [
            'q'        => $searchterm,
            'category' => self::class
        ]);
    }

    /**
     * Enables fulltext (MATCH AGAINST) search by creating the corresponding indices.
     */
    public static function enable()
    {
        DBManager::get()->exec("ALTER TABLE `auth_user_md5` ADD FULLTEXT INDEX globalsearch (`username`, `Vorname`, `Nachname`)");
    }

    /**
     * Disables fulltext (MATCH AGAINST) search by removing the corresponding indices.
     */
    public static function disable()
    {
        DBManager::get()->exec("DROP INDEX globalsearch ON `auth_user_md5`");
    }

    /**
     * Executes a fulltext (MATCH AGAINST) search in database for the given search term.
     *
     * @param string $search the term to search for.
     * @return string SQL query.
     */
    public static function getFulltextSearch($search)
    {
        if (!$search) {
            return null;
        }

        // if you're no admin respect visibilty
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $visQuery = get_vis_query('user', 'search') . " AND ";
        }
        $query = DBManager::get()->quote(preg_replace("/(\w+)[*]*\s?/", "+$1* ", $search));
        $sql = "SELECT SQL_CALC_FOUND_ROWS user.`user_id`, user.`Vorname`, user.`Nachname`, user.`username`
                FROM `auth_user_md5` AS user
                LEFT JOIN `user_visibility` USING (`user_id`)
                WHERE {$visQuery} MATCH(`username`, `Vorname`, `Nachname`) AGAINST($query IN BOOLEAN MODE)
                LIMIT " . Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE;
        return $sql;
    }

    /**
     * Function to mark a querystring in a resultstring
     *
     * @param $string
     * @param $query
     * @param bool|true $filename
     * @return mixed
     */
    public static function markMany($string, $query)
    {
        if (stripos($string, $query) !== false) {
            return self::mark($string, $query);
        }

        // Create regexp for replacement
        $chunks = preg_split('/[,\s]+/', $query, -1,  PREG_SPLIT_NO_EMPTY);
        rsort($chunks); // Ensure larger string will be replaced too (food <- foo)
        $chunks = array_map(function ($chunk) {
            return preg_quote($chunk, '/');
        }, $chunks);
        $regexp = '/' . implode('|', $chunks) . '/i';

        return preg_replace($regexp, '<mark>$0</mark>', strip_tags($string));
    }
}
