<?php
/**
 * GlobalSearchModule for buzzwords: words that trigger some manual info,
 * e.g. links to Campus systems etc.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchBuzzwords extends SimpleORMap
{

    /**
     * SimpleORMap metadata.
     * @param array $config configuration for SORM.
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'globalsearch_buzzwords';
        $config['additional_fields']['rightsname'] = true;
        parent::configure($config);
    }

    /**
     * Gets the Stud.IP name for a given permission level.
     * @return false|int|string
     */
    public function getRightsname()
    {
        return array_search($this->rights, $GLOBALS['perm']->permissions);
    }

    /**
     * Returns the displayname for this module
     *
     * @return string
     */
    public static function getName()
    {
        return _('Stichwörter');
    }

    /**
     * Transforms the search request into an sql statement, that provides the id (same as getId) as type and
     * the object id, that is later passed to the filter.
     *
     * This function is required to make use of the mysql union parallelism
     *
     * @param $search the input query string
     * @return String SQL Query to discover elements for the search
     */
    public static function getSQL($search, $filter, $limit)
    {
        if (!$search) {
            return null;
        }

        $query = DBManager::get()->quote("%{$search}%");
        $rights = $GLOBALS['perm']->permissions[$GLOBALS['perm']->get_perm()];

        return "SELECT SQL_CALC_FOUND_ROWS *
                FROM `globalsearch_buzzwords`
                WHERE `buzzwords` LIKE {$query}
                  AND {$rights} >= rights";
    }

    /**
     * Returns an array of information for the found element. Following information (key: description) is necessary
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
    public static function filter($buzz, $search)
    {
        return [
            'name'       => htmlReady($buzz['name']),
            'url'        => $buzz['url'],
            'additional' => $buzz['subtitle']
        ];
    }
}
