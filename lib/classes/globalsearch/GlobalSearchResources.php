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
     * @return mixed
     */
    public static function getName()
    {
        return _('Ressourcen');
    }

    public static function getSQL($search)
    {
        if (!Config::get()->RESOURCES_ENABLE || !$search || !$GLOBALS['perm']->have_perm('admin')) {
            return null;
        }
        $query = DBManager::get()->quote("%$search%");
        return "SELECT `resource_id`, `name`, `description`
                FROM `resources_objects`
                WHERE `name` LIKE $query
                  OR `description` LIKE $query
                  OR REPLACE(`name`, ' ', '') LIKE $query
                  OR REPLACE(`description`, ' ', '') LIKE $query
                ORDER BY `name` ASC
                LIMIT " . (4 * Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE);
    }

    public static function filter($res, $search)
    {
        return array(
            'name' => self::mark($res['name'], $search),
            'url'  => URLHelper::getURL('resources.php', [
                'view'        => 'view_schedule',
                'show_object' => $res['resource_id'],
            ]),
            'img'        => Icon::create('resources', 'clickable')->asImagePath(),
            'additional' => self::mark($res['description'], $search),
            'expand'     => self::getSearchURL($search),
        );
    }

    /**
     * Returns the URL that can be called for a full search.
     *
     * This could become obsolete when we have a real global search page.
     *
     * @param string $searchterm what to search for?
     */
    public static function getSearchURL($searchterm)
    {
        return URLHelper::getURL('resources.php', [
            'view'         => 'search',
            'search_exp'   => $searchterm,
            'start_search' => ''
        ]);
    }
}
