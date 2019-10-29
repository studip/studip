#!/usr/bin/env php
<?php
/**
 * This script will check whether the help tours steps are still valid
 * regarding the controllers and actions.
 *
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 */

require_once __DIR__ . '/studip_cli_env.inc.php';
require_once __DIR__ . '/../config/config_local.inc.php';

foreach (HelpTour::findBySQL('1 ORDER BY name ASC') as $tour) {
    if (!$tour->settings->active) {
        continue;
    }

    $errors = [];
    foreach ($tour->steps->orderBy('step ASC') as $step) {
        try {
            if (strpos($step->route, 'plugins.php') === 0) {
                $result = PluginEngine::routeRequest(substr($step->route, strlen('plugins.php') + 1));

                // retrieve corresponding plugin info
                $plugin_manager = PluginManager::getInstance();
                $plugin_info = $plugin_manager->getPluginInfo($result[0]);

                $file = implode('/', [
//                    $GLOBALS['ABSOLUTE_PATH_STUDIP'],
                    Config::get()->PLUGINS_PATH,
                    $plugin_info['path'],
                    $plugin_info['class'],
                ]);

                if (file_exists($file . '.php')) {
                    $file .= '.php';
                } elseif (file_exists($file . '.class.php')) {
                    $file .= '.class.php';
                } else {
                    throw new Exception();
                }
                require_once $file;
                $plugin = new $plugin_info['class'];

                if ($result[1]) {
                    $dispatcher = new Trails_Dispatcher(
                        $GLOBALS['ABSOLUTE_PATH_STUDIP'] . $plugin->getPluginPath(),
                        rtrim(PluginEngine::getLink($plugin, [], null, true), '/'),
                        'index'
                    );
                    $dispatcher->current_plugin = $plugin;
                    $parsed = $dispatcher->parse($result[1]);
                    $controller = $dispatcher->load_controller($parsed[0]);
                    if ($parsed[1] && !$controller->has_action($parsed[1])) {
                        throw new Exception();
                    }
                }
            } elseif (strpos($step->route, 'dispatch.php') === 0) {
                $dispatcher = new StudipDispatcher();
                $parsed = $dispatcher->parse(substr($step->route, strlen('dispatch.php') + 1));
                $controller = $dispatcher->load_controller($parsed[0]);
                if ($parsed[1] && !$controller->has_action($parsed[1])) {
                    throw new Exception();
                }
            } elseif (!file_exists("{$GLOBALS['ABSOLUTE_PATH_STUDIP']}{$step->route}")) {
                throw new Exception();
            }
        } catch (Exception $e) {
            $errors[$step->step] = $step->route;
        }
    }

    if ($errors) {
        $type = ucfirst($tour->type);
        echo "{$type} '{$tour->name}' has errors in the following steps:\n";
        foreach ($errors as $step => $route) {
            echo "- Step {$step}: {$route}\n";
        }
    }
}
