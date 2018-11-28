<?php
# Lifter007: TODO
# Lifter010: TODO
/*
 * StudIPPlugin.class.php - generic plugin base class
 *
 * Copyright (c) 2009 - Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

abstract class StudIPPlugin {

    /**
     * plugin meta data
     */
    protected $plugin_info;

    /**
     * plugin constructor
     * TODO bindtextdomain()
     */
    public function __construct() {
        $plugin_manager = PluginManager::getInstance();
        $this->plugin_info = $plugin_manager->getPluginInfo(get_class($this));
    }

    /**
     * Return the ID of this plugin.
     */
    public function getPluginId() {
        return $this->plugin_info['id'];
    }

    /**
     * Return the name of this plugin.
     */
    public function getPluginName() {
        return $this->plugin_info['name'];
    }

    /**
     * Return the filesystem path to this plugin.
     */
    public function getPluginPath() {
        return 'plugins_packages/' . $this->plugin_info['path'];
    }

    /**
     * Return the URL of this plugin. Can be used to refer to resources
     * (images, style sheets, etc.) inside the installed plugin package.
     */
    public function getPluginURL() {
        return $GLOBALS['ABSOLUTE_URI_STUDIP'] . $this->getPluginPath();
    }

    /**
     * Return metadata stored in the manifest of this plugin.
     */
    public function getMetadata() {
        $plugin_manager = PluginManager::getInstance();
        return $plugin_manager->getPluginManifest($this->getPluginPath());
    }

    /**
     * Checks if the plugin is a core-plugin. Returns true if this is the case.
     *
     * @return boolean
     */
    public function isCorePlugin()
    {
       return $this->plugin_info['core'];
    }

    /**
     * Get the activation status of this plugin in the given context.
     * This also checks the plugin default activations.
     *
     * @param $context   context range id (optional)
     * @param $type      type of activation (optional), can be set to 'user'
     *                   in order to point to a homepage plugin
     */
    public function isActivated($context = null, $type = 'sem') {
        global $user;

        $plugin_id = $this->getPluginId();
        $plugin_manager = PluginManager::getInstance();

        /*
         * Context can be a Seminar ID or the current user ID if not set.
         * Identification is done via the "username" parameter.
         */
        if (!isset($context)) {
            if ($type == 'user') {
                $context = get_userid(Request::username('username', $user->username));
            } else {
                $context = Context::getId();
            }
        }

        if ($type == 'user') {
            $activated = $plugin_manager->isPluginActivatedForUser($plugin_id, $context);
        } else {
            $activated = $plugin_manager->isPluginActivated($plugin_id, $context);
        }

        return $activated;
    }

    /**
     * Returns whether the plugin may be activated in a certain context.
     *
     * @param Range $context
     * @return bool
     */
    public function isActivatableForContext(Range $context)
    {
        return true;
    }

    /**
     * Return a warning message to be printed before deactivation of
     * this plugin in the given context.
     *
     * @param $context   context range id
     */
    public function deactivationWarning($context) {
        return NULL;
    }

    /**
     * This method dispatches all actions.
     *
     * @param string   part of the dispatch path that was not consumed
     *
     * @return void
     */
    public function perform($unconsumed_path) {
        $args = explode('/', $unconsumed_path);
        $action = $args[0] !== '' ? array_shift($args).'_action' : 'show_action';

        if (!method_exists($this, $action)) {
            $trails_root = $this->getPluginPath();
            $trails_uri  = rtrim(PluginEngine::getLink($this, array(), null, true), '/');

            $dispatcher = new Trails_Dispatcher($trails_root, $trails_uri, 'index');
            $dispatcher->current_plugin = $this;
            try {
                $dispatcher->dispatch($unconsumed_path);
            } catch (Trails_UnknownAction $exception) {
                if (count($args) > 0) {
                    throw $exception;
                } else {
                    throw new Exception(_('unbekannte Plugin-Aktion: ') . $unconsumed_path);
                }
            }
        } else {
            call_user_func_array(array($this, $action), $args);
        }
    }

    /**
     * Callback function called after enabling a plugin.
     * The plugin's ID is transmitted for convenience.
     *
     * @param $plugin_id string The ID of the plugin just enabled.
     */
    public static function onEnable($plugin_id)
    {
    }

    /**
     * Callback function called after disabling a plugin.
     * The plugin's ID is transmitted for convenience.
     *
     * @param $plugin_id string The ID of the plugin just disabled.
     */
    public static function onDisable($plugin_id)
    {
    }

    /**
     * Includes given stylesheet in page, compiles less if neccessary
     *
     * @param String $filename Name of the stylesheet (css or less) to include
     *                         (relative to plugin directory)
     * @param Array  $variables Optional array of variables to pass to the
     *                          LESS compiler
     * @param Array  $link_attr Attributes to pass to the link element
     */
    protected function addStylesheet($filename, $variables = [], $link_attr = [])
    {
        if (mb_substr($filename, -5) !== '.less') {
            $url = $this->getPluginURL() . '/' . $filename;
            PageLayout::addStylesheet($url, $link_attr);
            return;
        }

        // Create absolute path to less file
        $less_file = $GLOBALS['ABSOLUTE_PATH_STUDIP']
                   . $this->getPluginPath() . '/'
                   . $filename;

        // Fail if file does not exist
        if (!file_exists($less_file)) {
            throw new Exception('Could not locate LESS file "' . $filename . '"');
        }

        // Get plugin version from metadata
        $metadata = $this->getMetadata();
        $plugin_version = $metadata['version'];

        // Get plugin id (or parent plugin id if any)
        $plugin_id = $this->plugin_info['depends'] ?: $this->getPluginId();

        // Get asset file from storage
        $asset = Assets\Storage::getFactory()->createCSSFile($less_file, array(
            'plugin_id'      => $this->plugin_info['depends'] ?: $this->getPluginId(),
            'plugin_version' => $metadata['version'],
        ));

        // Compile asset if neccessary
        if ($asset->isNew()) {
            $variables['plugin-path'] = $this->getPluginURL();

            $less = file_get_contents($less_file);
            $css  = Assets\Compiler::compileLESS($less, $variables);
            $asset->setContent($css);
        }

        // Include asset in page by reference or directly
        $download_uri = $asset->getDownloadLink();
        if ($download_uri === false) {
            PageLayout::addStyle($asset->getContent(), $link_attr);
        } else {
            $link_attr['rel']  = 'stylesheet';
            $link_attr['href'] = $download_uri;
            $link_attr['type'] = 'text/css';
            PageLayout::addHeadElement('link', $link_attr);
        }
    }
}
