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
    public function find_action()
    {
        // Perform search by mysqli (=async) or by PDO (=sync)?
        $async = Config::get()->GLOBALSEARCH_ASYNC_QUERIES;

        // Now load all modules
        $modules = Config::get()->GLOBALSEARCH_MODULES;

        $search = trim(Request::get('search'));

        $result = $classes = [];

        // Global config setting says to use mysqli
        if ($async) {
            foreach ($modules as $className => $data) {
                if ($data['active']) {
                    $class = new $className();
                    $classes[$className] = $class;
                    $partSQL = $class->getSQL($search);
                    if ($partSQL) {
                        $new = mysqli_connect($GLOBALS['DB_STUDIP_HOST'], $GLOBALS['DB_STUDIP_USER'],
                            $GLOBALS['DB_STUDIP_PASSWORD'], $GLOBALS['DB_STUDIP_DATABASE']);
                        mysqli_set_charset($new, 'UTF8');
                        $new->query($partSQL, MYSQLI_ASYNC);
                        $new->id = $className;
                        $all_links[] = $new;
                    }
                }
            }

            $read = $error = $reject = array();
            while (count($read) + count($error) + count($reject) < count($all_links)) {

                // Parse all links
                $error = $reject = $read = $all_links;

                // Poll will reject connection that have no query running
                mysqli_poll($read, $error, $reject, 1);

                foreach ($read as $r) {
                    if ($r && $set = $r->reap_async_query()) {
                        $id = $r->id;

                        // Walk through each fetched entry.
                        while ($data = $set->fetch_assoc()) {

                            /*
                             * We found more results than needed,
                             * add "more" link for full search.
                             */
                            if (isset($result[$id]['content'])
                                && count($result[$id]['content']) >= Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE)
                            {
                                $result[$id]['more'] = true;
                                $result[$id]['fullsearch'] = $classes[$id]->getSearchURL($search) ?: '';
                            }

                            $arg = $data['type'] && count($data) == 2 ? $data['id'] : $data;

                            // Filter item and add to result if necessary.
                            if ($item = $classes[$id]->filter($arg, $search)) {
                                $result[$id]['name'] = $classes[$id]->getName();
                                $result[$id]['content'][] = $item;
                            }
                        }
                    }
                }
            }

        // Global config setting calls for PDO
        } else {

            // Process active search modules...
            foreach ($modules as $className => $data) {

                if ($data['active']) {
                    $class = new $className();
                    $classes[$className] = $class;
                    $partSQL = $class->getSQL($search);

                    // ... and execute corresponding SQL.
                    if ($partSQL) {
                        $entries = DBManager::get()->fetchAll($partSQL);

                        // Walk through results
                        foreach ($entries as $one) {

                            /*
                             * We found more results than needed,
                             * add "more" link for full search.
                             */
                            if (count($result[$className]['content']) >= Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE) {
                                $result[$className]['more'] = true;
                                $result[$className]['fullsearch'] = $classes[$className]->getSearchURL($search);
                            }

                            if (count($result[$className]['content']) < Config::get()->GLOBALSEARCH_MAX_RESULT_OF_TYPE) {

                                // Filter item and add to result if necessary.
                                if ($item = $classes[$className]->filter($one, $search)) {
                                    $result[$className]['name'] = $classes[$className]->getName();
                                    $result[$className]['content'][] = $item;
                                }
                            }
                        }
                    }
                }
            }
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
        $GLOBALS['perm']->check('root');

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
