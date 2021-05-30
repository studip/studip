<?php
/**
 * CorePlugin.class.php - base class
 *
 * @author    André Noack <noack@data-quest.de>
 * @copyright 2021 Authors
 * @license   GPL2 or any later version
 */
abstract class CorePlugin
{

    /**
     * plugin meta data
     */
    protected $plugin_info;

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

    public function isEnabled()
    {
        return $this->plugin_info['enabled'];
    }

    /**
     * Return the name of this plugin.
     */
    public function getPluginName()
    {
        return $this->plugin_info['name'];
    }


    public function getPluginURL()
    {
        return $GLOBALS['ABSOLUTE_URI_STUDIP'];
    }

    /**
     * Returns the version of this plugin as defined in manifest.
     * @return string
     */
    public function getPluginVersion()
    {
        return '';
    }

    /**
     * Checks if the plugin is a core-plugin. Returns true if this is the case.
     *
     * @return boolean
     */
    public function isCorePlugin()
    {
        return true;
    }

    /**
     * Get the activation status of this plugin in the given context.
     * This also checks the plugin default activations.
     *
     * @param $context   context range id (optional)
     */
    public function isActivated($context = null)
    {
        $plugin_id = $this->getPluginId();
        $plugin_manager = PluginManager::getInstance();

        if (!isset($context)) {
            $context = Context::getId();
        }
        $activated = $plugin_manager->isPluginActivated($plugin_id, $context);
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
     * Callback function called after enabling a plugin.
     * The plugin's ID is transmitted for convenience.
     *
     * @param $plugin_id string The ID of the plugin just enabled.
     */
    public static function onActivation($plugin_id, $range_id)
    {
    }

    /**
     * Callback function called after disabling a plugin.
     * The plugin's ID is transmitted for convenience.
     *
     * @param $plugin_id string The ID of the plugin just disabled.
     */
    public static function onDeactivation($plugin_id, $range_id)
    {
    }

    /**
     * @param $range_id string
     * @return bool
     */
    public static function checkActivation($range_id)
    {
        $core_plugin = PluginEngine::getPlugin(static::class);
        return $core_plugin && $core_plugin->isActivated($range_id);
    }
}
