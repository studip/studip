<?php
/**
 * StudIPPlugin.class.php - generic plugin base class
 *
 * @author    Elmar Ludwig <ludwig@uos.de>
 * @copyright 2009 Authors
 * @license   GPL2 or any later version
 */
abstract class StudIPPlugin
{
    use PluginAssetsTrait;

    /**
     * plugin meta data
     */
    protected $plugin_info;

    /**
     * Plugin manifest
     */
    protected $manifest = null;

    /**
     * plugin constructor
     * TODO bindtextdomain()
     */
    public function __construct()
    {
        $plugin_manager = PluginManager::getInstance();
        $this->plugin_info = $plugin_manager->getPluginInfo(static::class);
    }

    /**
     * Return the ID of this plugin.
     */
    public function getPluginId()
    {
        return $this->plugin_info['id'];
    }

    /**
     * Return the name of this plugin.
     */
    public function getPluginName()
    {
        return $this->plugin_info['name'];
    }

    /**
     * Return the filesystem path to this plugin.
     */
    public function getPluginPath()
    {
        return "plugins_packages/{$this->plugin_info['path']}";
    }

    /**
     * Return the URL of this plugin. Can be used to refer to resources
     * (images, style sheets, etc.) inside the installed plugin package.
     */
    public function getPluginURL()
    {
        return $GLOBALS['ABSOLUTE_URI_STUDIP'] . $this->getPluginPath();
    }

    /**
     * Return metadata stored in the manifest of this plugin.
     */
    public function getMetadata()
    {
        if ($this->manifest === null) {
            $plugin_manager = PluginManager::getInstance();
            $this->manifest = $plugin_manager->getPluginManifest($this->getPluginPath());
        }
        return $this->manifest;
    }

    /**
     * Returns the version of this plugin as defined in manifest.
     * @return string
     */
    public function getPluginVersion()
    {
        return $this->getMetadata()['version'];
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
    public function isActivated($context = null, $type = 'sem')
    {
        $plugin_id = $this->getPluginId();
        $plugin_manager = PluginManager::getInstance();

        /*
         * Context can be a Seminar ID or the current user ID if not set.
         * Identification is done via the "username" parameter.
         */
        if (!isset($context)) {
            if ($type === 'user') {
                $context = get_userid(Request::username('username', $GLOBALS['user']->username));
            } else {
                $context = Context::getId();
            }
        }

        if ($type === 'user') {
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
     * This method dispatches all actions.
     *
     * @param string   part of the dispatch path that was not consumed
     *
     * @return void
     */
    public function perform($unconsumed_path)
    {
        $args = explode('/', $unconsumed_path);
        $action = $args[0] !== '' ? array_shift($args).'_action' : 'show_action';

        if (!method_exists($this, $action)) {
            $trails_root = $this->getPluginPath();
            $trails_uri  = rtrim(PluginEngine::getLink($this, [], null, true), '/');

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
            call_user_func_array([$this, $action], $args);
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
}
