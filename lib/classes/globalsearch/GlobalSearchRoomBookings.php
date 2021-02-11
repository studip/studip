<?php
/**
 * GlobalSearchModule for room bookings
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchRoomBookings extends GlobalSearchModule
{

    /**
     * Returns the displayname for this module
     *
     * @return string
     */
    public static function getName()
    {
        return _('Raumbuchungen');
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

    /**
     * Search freetext resource bookings for the given search term.
     *
     * @param string $search The term or date to search for. You can either use
     *                       part of the room bookings free text or a date.
     * @param array $filter an array with search limiting filter information (e.g. 'category', 'semester', etc.)
     * @return null|string
     */
    public static function getSQL($search, $filter, $limit)
    {
        if (!Config::get()->RESOURCES_ENABLE || !$search || !$GLOBALS['perm']->have_perm('root')) {
            return null;
        }

        $query = DBManager::get()->quote('%' . trim($search) . '%');

        $sql = "SELECT SQL_CALC_FOUND_ROWS DISTINCT a.`id`, a.`description`, r.`id`, r.`name`, a.`begin`, a.`end`
                FROM `resource_bookings` a
                JOIN `resources` r
                ON a.`resource_id` = r.`id`
                WHERE a.`description` != ''
                  AND a.`description` IS NOT NULL
                  AND (a.`description` LIKE {$query}";

        $datefilter = '';

        foreach (explode(' ', $search) as $part) {
            if (is_numeric($part)) {
                $datefilter .= " AND (FROM_UNIXTIME(a.`begin`, '%Y') = " . DBManager::get()->quote($part) .
                    " OR FROM_UNIXTIME(a.`end`, '%Y') = " . DBManager::get()->quote($part) . ")";
                $search = str_replace([$part . ' ', ' ' . $part], '', $search);
            } else if (preg_match('/\d+\.\d+\.\d+/', $part)) {
                $datefilter .= " AND (FROM_UNIXTIME(a.`begin`, '%d.%m.%Y') = " . DBManager::get()->quote($part) .
                    " OR FROM_UNIXTIME(a.`end`, '%d.%m.%Y') = " . DBManager::get()->quote($part) . ")";
                $search = str_replace([$part . ' ', ' ' . $part], '', $search);
            }
        }

        $search = DBManager::get()->quote('%' . $search . '%');

        $sql .= " OR a.`description` LIKE $search)";

        if ($datefilter != '') {
            $sql .= $datefilter;
        }

        $sql .= " ORDER BY `begin` DESC, `description` LIMIT " .
            $limit;

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
     * @param array $res
     * @param string $search
     * @return array
     */
    public static function filter($res, $search)
    {
        $additional  = $res['name'] . ', ';
        $additional .= date('d.m.Y H:i', $res['begin']) . ' - ';
        $additional .= date('d.m.Y H:i', $res['end']);

        return [
            'name' => self::mark($res['user_free_name'], $search),
            'url'  => URLHelper::getURL('resources.php', [
                'view'        => 'view_schedule',
                'show_object' => $res['resource_id'],
                'start_time'  => strtotime('last monday', $res['begin'] + 24 * 60 * 60)
            ], true),
            'img'        => Icon::create('room-clear')->asImagePath(),
            'additional' => self::mark($additional, $search),
            'expand'     => null
        ];
    }
}
