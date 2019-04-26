<?php
/**
 * GlobalSearchModule for modules
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license   GPL2 or any later version
 * @since     4.1
 */
class GlobalSearchModules extends GlobalSearchModule
{
    /**
     * Returns the displayname for this module
     *
     * @return string
     */
    public static function getName()
    {
        return _('Module');
    }

    /**
     * Transforms the search request into an sql statement, that provides the id (same as getId) as type and
     * the object id, that is later passed to the filter.
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

        // Get language
        $language = ModuleManagementModel::getLanguage();
        if (!$language) {
            ModuleManagementModel::setLanguage($_SESSION['_language']);
            $language = ModuleManagementModel::getLanguage();
        }

        // Prepare variables
        $needle   = DBManager::get()->quote("%{$search}%");
        $language = DBManager::get()->quote($language);

        // Prepare status (according to user role)
        $status_cond = '1';
        if (!self::extendedDisplay()) {
            $status_cond = "`m`.`stat` = " . DBManager::get()->quote('genehmigt');
        }

        $query = "SELECT SQL_CALC_FOUND_ROWS `m`.`modul_id`, `m`.`code`,
                         IFNULL(`i18n`.`value`, `md`.`bezeichnung`) AS `bezeichnung`,
                         `m`.`stat`, `m`.`kp`,
                         `sd0`.`name` AS `sem_start`, `sd1`.`name` AS `sem_end`,
                         `sd0`.`semester_token` AS `token_start`,
                         `sd1`.`semester_token` AS `token_end`,
                         COUNT(`mt`.`modulteil_id`) AS `parts`
                  FROM `mvv_modul` AS `m`
                  -- Module descriptor
                  JOIN `mvv_modul_deskriptor` AS `md` USING (`modul_id`)
                  LEFT JOIN `i18n`
                    ON `md`.`deskriptor_id` = `i18n`.`object_id`
                      AND `i18n`.`table` = 'mvv_modul_deskriptor'
                      AND `i18n`.`field` = `bezeichnung`
                      AND `i18n`.`lang` = {$language}
                  -- Get semester durations
                  LEFT JOIN `semester_data` AS `sd0`
                    ON (`m`.`start` = `sd0`.`semester_id`)
                  LEFT JOIN `semester_data` AS `sd1`
                    ON (`m`.`end` = `sd1`.`semester_id`)
                  -- Get module parts (for counting)
                  LEFT JOIN `mvv_modulteil` AS `mt` USING (`modul_id`)
                  WHERE {$status_cond}
                    AND (`sd0`.`semester_id` IS NULL OR `sd0`.`beginn` <= UNIX_TIMESTAMP())
                    AND (`sd1`.`semester_id` IS NULL OR `sd1`.`ende` >= UNIX_TIMESTAMP())
                    AND (
                      `m`.`code` LIKE {$needle}
                      OR IFNULL(`i18n`.`value`, `md`.`bezeichnung`) LIKE {$needle}
                    )
                  GROUP BY `m`.`modul_id`
                  ORDER BY `m`.`code`, IFNULL(`i18n`.`value`, `md`.`bezeichnung`)
                  LIMIT " . $limit;
        return $query;
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
     * @return array
     */
    public static function filter($module_data, $search)
    {
        extract($module_data);

        $label = $module_data['code'] . ' ' . $module_data['bezeichnung'];

        // Get icon according to permissions
        $icon_role = Icon::ROLE_CLICKABLE;
        if (self::extendedDisplay()) {
            if ($module_data['stat'] === 'genehmigt') {
                $icon_role = Icon::ROLE_STATUS_GREEN;
            } elseif ($module_data['stat'] === 'planung') {
                $icon_role = Icon::ROLE_STATUS_YELLOW;
            } elseif ($module_data['stat'] === 'ausgelaufen') {
                $icon_role = Icon::ROLE_STATUS_RED;
            }
        }

        // Get semester durations
        if (!$sem_start && $sem_end) {
            $duration = sprintf(_('bis %s'), $sem_token ?: $sem_end);
        } else {
            $duration = [
                $token_start ?: $sem_start ?: _('unbegrenzt'),
                $token_end ?: $sem_end ?: _('unbegrenzt'),
            ];
            $duration = implode(' - ', array_unique($duration));
        }

        // Construct additional information
        $additional = (float) $kp . ' ' . _('CP');
        if ($parts > 1) {
            $additional .= " ({$parts} " . _('Modulteile') . ")";
        }

        return [
            'name'       => self::mark($code . ' ' . $bezeichnung, $search),
            // TODO: The following will unfortunately NOT open the details
            'url'        => URLHelper::getURL("dispatch.php/search/module/index/{$modul_id}?sterm={$code}"),
            'img'        => Icon::create('learnmodule', $icon_role)->asImagePath(),
            'date'       => $duration,
            'expand'     => self::getSearchURL($search),
            'additional' => $additional,
        ];
    }

    /**
     * Returns the url to the global modules search populated with the current
     * search term.
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

     // TODO: This probably needs some roles or something else
    private static function extendedDisplay()
    {
        return $GLOBALS['perm']->have_perm('root');
    }
}
