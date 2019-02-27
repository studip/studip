<?php
/**
 * globalsearch.php - controller to perform global search operations and provide settings.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 */

class GlobalSearchController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (in_array($action, ['settings', 'saveconfig'])) {
            $GLOBALS['perm']->check('root');
        }
    }

    /**
     * Perform search in all registered modules for the given search term.
     */
    public function find_action($limit)
    {
        // Perform search by mysqli (=async) or by PDO (=sync)?
        $async = Config::get()->GLOBALSEARCH_ASYNC_QUERIES;

        // Now load all modules
        $modules = Config::get()->GLOBALSEARCH_MODULES;

        $search = trim(Request::get('search'));

        $filter = json_decode(Request::get('filters'), true);

        $result = $classes = [];

        foreach ($modules as $className => $data) {
            // Not active? Leave.
            if (!$data['active']) {
                continue;
            }

            $class = new $className();
            $classes[$className] = $class;
            $partSQL = $class->getSQL($search, $filter, $limit);

            // No valid sql? Leave.
            if (!$partSQL) {
                continue;
            }

            // Global config setting says to use mysqli
            if ($async) {
                $mysqli = new mysqli($GLOBALS['DB_STUDIP_HOST'], $GLOBALS['DB_STUDIP_USER'],
                    $GLOBALS['DB_STUDIP_PASSWORD'], $GLOBALS['DB_STUDIP_DATABASE']);
                mysqli_set_charset($mysqli, 'UTF8');
                if ($mysqli->multi_query($partSQL . '; SELECT FOUND_ROWS() as found_rows;')) {
                    do {
                        if ($res = $mysqli->store_result()) {
                            $all_links[$className][] = $res->fetch_all(MYSQLI_ASSOC);
                        }
                    } while ($mysqli->more_results() && $mysqli->next_result());
                }
                $entries = $all_links[$className][0];
                $entries_count = (int)$all_links[$className][1][0]['found_rows'];
            // Global config setting calls for PDO
            } else {
                $entries = DBManager::get()->fetchAll($partSQL);
                $entries_count_array = DBManager::get()->fetchAll('SELECT FOUND_ROWS() as found_rows');
                $entries_count = (int)$entries_count_array[0]['found_rows'];
            }

            // No results? Leave.
            if (!is_array($entries)) {
                continue;
            }


            // Walk through results
            $found = [];
            foreach ($entries as $one) {
                // Filter item and add to result if necessary.
                if ($item = $classes[$className]->filter($one, $search)) {
                    $found[] = $item;
                }
            }

            // Nothing found? Leave.
            if (count($found) === 0) {
                continue;
            }

            $result[$className] = [
                'name'       => $classes[$className]->getName(),
                'fullsearch' => $classes[$className]->getSearchURL($search),
                'content'    => $found,
                // If we found more results than needed, indicate a "more" link
                // for full search.
                'more'       => count($found) > Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE,
                // If there are more results than our arbitrary LIMIT, a plus
                // ('+') should be shown besides the category result count
                'plus'       => count($found) < $entries_count,
            ];
        }

        // Sort
        uksort($result, function($a, $b) use ($modules) {
            return $modules[$a]['order'] - $modules[$b]['order'];
        });

        // Send me an answer
        $this->render_json($result);
    }

    /**
     * Provide a GUI for configuring the search module order and other settings.
     */
    public function settings_action()
    {
        PageLayout::setTitle(_('Globale Suche: Einstellungen'));
        Navigation::activateItem('/admin/config/globalsearch');

        $this->config = Config::get()->GLOBALSEARCH_MODULES;

        $this->modules = [];

        // Scan for available modules.
        foreach (scandir($GLOBALS['STUDIP_BASE_PATH'] . '/lib/classes/globalsearch') as $filename) {
            $path = pathinfo($filename);
            if ($path['extension'] === 'php') {
                class_exists($path['filename']);
            }
        }

        // Search declared classes for GlobalSearchModules
        $endindex = 100;
        foreach (get_declared_classes() as $className) {
            if (is_a($className, 'GlobalSearchModule', true)
                    && $className !== 'GlobalSearchModule') {
                $class = new $className();

                // Add new classes at module array end and not activated.
                if (in_array($className, array_keys($this->config))) {
                    $this->modules[$this->config[$className]['order']] = $class;
                } else {
                    $this->modules[$endindex++] = $class;
                }
            }
        }

        ksort($this->modules);
    }

    /**
     * Saves the set values to global configuration.
     */
    public function saveconfig_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $config = [];

        $order = 1;
        foreach (Request::getArray('modules') as $module) {
            $config[$module['class']] = [
                'order'    => $order,
                'active'   => (bool)$module['active'],
                'fulltext' => is_a($module['class'], 'GlobalSearchFulltext', true) && $module['fulltext']
            ];
            $order++;
        }

        $success = true;
        if (Request::int('async_queries', 0) != Config::get()->GLOBALSEARCH_ASYNC_QUERIES) {
            $success = Config::get()->store('GLOBALSEARCH_ASYNC_QUERIES',
                ['value' => Request::int('async_queries', 0)]);
            Config::get()->GLOBALSEARCH_ASYNC_QUERIES = Request::int('async_queries', 0);
        }

        if (Request::int('entries_per_type', 3) != Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE) {
            $success = Config::get()->store('GLOBALSEARCH_MAX_RESULT_OF_TYPE',
                ['value' => Request::int('entries_per_type', 3)]);
            Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE = Request::int('entries_per_type', 3);
        }

        if ($config != Config::get()->GLOBALSEARCH_MODULES) {
            $success = Config::get()->store('GLOBALSEARCH_MODULES', ['value' => $config]);
        }

        if ($success) {
            PageLayout::postSuccess(_('Die Einstellungen wurden gespeichert.'));
        } else {
            PageLayout::postError(_('Die Einstellungen konnten nicht gespeichert werden.'));
        }

        $this->relocate('globalsearch/settings');
    }

}
