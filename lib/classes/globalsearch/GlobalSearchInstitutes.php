<?php
/**
 * GlobalSearchModule for institutes
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchInstitutes extends GlobalSearchModule
{
    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName()
    {
        return _('Einrichtungen');
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
        $search = str_replace(' ', '% ', $search);
        $query = DBManager::get()->quote("%{$search}%");
        $sql = "SELECT SQL_CALC_FOUND_ROWS *
                FROM `Institute`
                WHERE `Name` LIKE {$query}
                ORDER BY `Name` DESC
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
    public static function filter($inst_id, $search)
    {
        $inst = Institute::buildExisting($inst_id);
        $result = [
            'id'     => $inst->id,
            'name'   => self::mark($inst->getFullname(), $search),
            'url'    => URLHelper::getURL('dispatch.php/institute/overview', [
                'cid' => $inst->id,
            ]),
            'expand' => self::getSearchURL($search),
        ];
        $avatar = InstituteAvatar::getAvatar($inst->id);
        $result['img'] = $avatar->getUrl(Avatar::MEDIUM);
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

}
