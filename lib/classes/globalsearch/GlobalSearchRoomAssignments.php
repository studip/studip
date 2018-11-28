<?php
/**
 * GlobalSearchModule for room assignments
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */
class GlobalSearchRoomAssignments extends GlobalSearchModule
{

    /**
     * Returns the displayname for this module
     *
     * @return mixed
     */
    public static function getName()
    {
        return _('Raumbuchungen');
    }

    /**
     * Search freetext resource assignments for the given search term.
     *
     * @param string $search The term or date to search for. You can either use
     *                       part of the room assignment free text or a date.
     * @return null|string
     */
    public static function getSQL($search)
    {
        if (!Config::get()->RESOURCES_ENABLE || !$search || !$GLOBALS['perm']->have_perm('root')) {
            return null;
        }

        $query = DBManager::get()->quote('%' . trim($search) . '%');

        $sql = "SELECT DISTINCT a.`assign_id`, a.`user_free_name`, r.`resource_id`, r.`name`, a.`begin`, a.`end`
                FROM `resources_assign` a
                JOIN `resources_objects` r USING (`resource_id`)
                WHERE a.`user_free_name` != ''
                  AND a.`user_free_name` IS NOT NULL
                  AND (a.`user_free_name` LIKE {$query}";

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

        $sql .= " OR a.`user_free_name` LIKE $search)";

        if ($datefilter != '') {
            $sql .= $datefilter;
        }

        $sql .= " ORDER BY `begin` DESC, `user_free_name` LIMIT " .
            (Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE + 1);

        return $sql;
    }

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
                'start_time'  => strtotime('last monday', $res['begin'] + 24*60*60)
            ]),
            'img'        => Icon::create('room-clear', 'clickable')->asImagePath(),
            'additional' => self::mark($additional, $search),
            'expand'     => null
        ];
    }
}
