<?php
/**
 * GlobalSearchModule for resources
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchResources extends GlobalSearchModule
{
    /**
     * Returns the displayname for this module
     *
     * @return string
     */
    public static function getName()
    {
        return _('Ressourcen');
    }

    /**
     * Transforms the search request into an sql statement, that provides the id (same as getId) as type and
     * the object id, that is later passed to the filter.
     *
     * This function is required to make use of the mysql union parallelism
     *
     * @param string $search the input query string
     * @param array $filter an array with search limiting filter information (e.g. 'category', 'semester', etc.)
     * @return string SQL Query to discover elements for the search
     */
    public static function getSQL($search, $filter, $limit)
    {
        if (!Config::get()->RESOURCES_ENABLE || !$search) {
            return null;
        }
        $query = DBManager::get()->quote("%{$search}%");
        return "SELECT SQL_CALC_FOUND_ROWS `id`, `name`, `description`
                FROM `resources`
                WHERE `name` LIKE {$query}
                  OR `description` LIKE {$query}
                  OR REPLACE(`name`, ' ', '') LIKE {$query}
                  OR REPLACE(`description`, ' ', '') LIKE {$query}
                ORDER BY `name` ASC
                LIMIT " . $limit;
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
     * @param array $res
     * @param string $search
     * @return attay
     */
    public static function filter($res, $search)
    {
        $resource = Resource::find($res['id']);
        if (!($resource instanceof Resource)) {
            return [];
        }
        try {
            $resource = $resource->getDerivedClassInstance();
        } catch (Exception $e) {
            //Leave the resource as it is.
        }

        return [
            'name' => self::mark($resource->getFullName(), $search),
            'url'  => $resource->getActionURL('show'),
            'img'        => $resource->getIcon('clickable')->asImagePath(),
            'additional' => self::mark($resource->description, $search),
            'expand'     => self::getSearchURL($search),
        ];
    }

    /**
     * Returns the URL that can be called for a full search.
     *
     * @param string $searchterm what to search for?
     * @return string URL to the full search, containing the searchterm and the category
     */
    public static function getSearchURL($searchterm)
    {
        return URLHelper::getURL('dispatch.php/search/globalsearch', [
            'q'        => $searchterm,
            'category' => self::class
        ]);
    }
}
