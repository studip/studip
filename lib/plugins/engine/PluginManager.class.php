<?php
# Lifter010: TODO
/*
 * PluginManager.class.php - plugin manager for Stud.IP
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class PluginManager
{
    /**
     * meta data of installed plugins
     */
    private $plugins;

    /**
     * cache of created plugin instances
     */
    private $plugin_cache;

    /**
     * RolePersistence object
     */
    private $rolemgmt;

    /**
     * cache of activated plugins by context
     */
    private $plugins_activated_cache = [];

    /**
     * cache of plugin default activations
     */
    private $plugins_default_activations_cache = [];

    /**
     * Returns the PluginManager singleton instance.
     */
    public static function getInstance ()
    {
        static $instance;

        if (isset($instance)) {
            return $instance;
        }

        return $instance = new PluginManager();
    }

    /**
     * Initialize a new PluginManager instance.
     */
    private function __construct ()
    {
        $this->readPluginInfos();
        $this->plugin_cache = [];
        $this->rolemgmt = new RolePersistence();
    }

    /**
     * Comparison function used to order plugins by position.
     */
    private static function positionCompare ($plugin1, $plugin2)
    {
        return $plugin1['position'] - $plugin2['position'];
    }

    /**
     * Read meta data for all plugins registered in the data base.
     */
    private function readPluginInfos ()
    {
        $db = DBManager::get();
        $this->plugins = [];

        $result = $db->query('SELECT * FROM plugins ORDER BY pluginname');

        foreach ($result as $plugin) {
            $id = (int) $plugin['pluginid'];

            $this->plugins[$id] = [
                'id'                      => $id,
                'name'                    => $plugin['pluginname'],
                'class'                   => $plugin['pluginclassname'],
                'path'                    => $plugin['pluginpath'],
                'type'                    => explode(',', $plugin['plugintype']),
                'enabled'                 => $plugin['enabled'] === 'yes',
                'position'                => $plugin['navigationpos'],
                'depends'                 => (int) $plugin['dependentonid'],
                'core'                    => $plugin['pluginpath'] === '' || strpos($plugin['pluginpath'], 'core/') === 0,
                'automatic_update_url'    => $plugin['automatic_update_url'],
                'automatic_update_secret' => $plugin['automatic_update_secret']
            ];
        }
    }

    /**
     * @addtogroup notifications
     *
     * Enabling or disabling a plugin triggers a PluginDidEnable or
     * respectively PluginDidDisable notification. The plugin's ID
     * is transmitted as subject of the notification.
     */
    /**
     * Set the enabled/disabled status of the given plugin.
     *
     * If the plugin implements the method "onEnable" or "onDisable", this
     * method will be called accordingly. If the method returns false or
     * throws and exception, the plugin's activation state is not updated.
     *
     * @param string $id        id of the plugin
     * @param bool   $enabled   plugin status (true or false)
     * @param bool   $force     force (de)activation regardless of the result
     *                           of on(en|dis)able
     * @return bool  indicating whether the plugin was updated or null if the
     *               passed state equals the current state or if the plugin is
     *               missing.
     */
    public function setPluginEnabled ($id, $enabled, $force = false)
    {
        $info = $this->getPluginInfoById($id);

        // Plugin is not present or no changes
        if (!$info || $info['enabled'] == $enabled) {
            return;
        }

        if ($info['core'] || !$this->isPluginsDisabled()) {
            $plugin_class = $this->loadPlugin($info['class'], $info['path']);
        }

        if ($plugin_class) {
            $method = $enabled ? 'onEnable' : 'onDisable';
            $result = $plugin_class->getMethod($method)->invoke(null, $id);

            // if callback returns false, don't enable or disable the plugin
            if ($result === false && !$force) {
                return false;
            }
        }

        // Update plugin
        $state = $enabled ? 'yes' : 'no';

        $query = "UPDATE plugins SET enabled = ? WHERE pluginid = ?";
        DBManager::get()->execute($query, [$state, $id]);

        $this->plugins[$id]['enabled'] = (boolean) $enabled;

        NotificationCenter::postNotification(
            $enabled ? 'PluginDidEnable' : 'PluginDidDisable',
            $id
        );

        return true;
    }

    /**
     * Set the navigation position of the given plugin.
     *
     * @param $id        id of the plugin
     * @param $position  plugin navigation position
     * @return bool indicating whether any change occured
     */
    public function setPluginPosition ($id, $position)
    {
        $info = $this->getPluginInfoById($id);
        $position = (int) $position;

        if (!$info || $info['position'] == $position) {
            return false;
        }

        $query = "UPDATE plugins SET navigationpos = ? WHERE pluginid = ?";
        DBManager::get()->execute($query, [$position, $id]);

        $this->plugins[$id]['position'] = $position;
        $this->readPluginInfos();

        return true;
    }

    /**
     * Get the activation status of a plugin in the given context.
     * This also checks the plugin default activations and sem_class-settings.
     *
     * @param $id        id of the plugin
     * @param $context   context range id
     */
    public function isPluginActivated ($id, $context)
    {
        $plugin_class = $this->plugins[$id]['class'];
        if (!$context) return;
        if (!isset($this->plugins_activated_cache[$context])) {
            $query = "SELECT pluginid, state "
                . "FROM plugins_activated "
                . "WHERE range_type IN ('sem', 'inst') AND range_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$context]);
            $this->plugins_activated_cache[$context] = $statement->fetchGrouped(PDO::FETCH_COLUMN);
        }
        $state = $this->plugins_activated_cache[$context][$id];
        if (get_object_type($context, ['sem']) === 'sem') {
            if ($state === null) {
                if (!isset($this->plugins_default_activations_cache[$context])) {
                    $query = "SELECT pluginid, 1 as state "
                        . "FROM plugins_default_activations "
                        . "JOIN seminar_inst ON (institutid = institut_id) "
                        . "WHERE seminar_id = ?";
                    $statement = DBManager::get()->prepare($query);
                    $statement->execute([$context]);
                    $this->plugins_default_activations_cache[$context] = $statement->fetchGrouped(PDO::FETCH_COLUMN);
                }
                $inst_default = $this->plugins_default_activations_cache[$context][$id];
            }
            $sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][Seminar::GetInstance($context)->status]['class']];
            if ($sem_class) {
                $modules = $sem_class->getModules();
                $sem_class_default = $modules[$plugin_class]['activated'];
                $mandatory = $modules[$plugin_class]['sticky'] && $sem_class_default;
                $forbidden = $modules[$plugin_class]['sticky'] && !$sem_class_default;
            }
        }
        return ((($inst_default || $sem_class_default) && $state !== '0' || $state === '1') && !$forbidden)
            || $mandatory;
    }

    /**
     * Get the activation status of a plugin for the given user.
     * This also checks the plugin default activations and sem_class-settings.
     *
     * @param $pluginId  id of the plugin
     * @param $userId    id of the user
     */
    public function isPluginActivatedForUser($pluginId, $userId)
    {
        if (!$userId) {
            $userId = $GLOBALS['user']->id;
        }
        if (!isset($this->plugins_activated_cache[$userId])) {
            $query = "SELECT pluginid, state "
                . "FROM plugins_activated "
                . "WHERE range_type = 'user' AND range_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$userId]);
            $this->plugins_activated_cache[$userId] = $statement->fetchGrouped(PDO::FETCH_COLUMN);

        }
        $state = $this->plugins_activated_cache[$userId][$pluginId];
        if ($state === null) {
            $activated = (bool) Config::get()->HOMEPAGEPLUGIN_DEFAULT_ACTIVATION;
        } else {
            $activated = (bool) $state;
        }

        return $activated;
    }

    /**
     * Sets the activation status of a plugin in the given context.
     *
     * @param $id        id of the plugin
     * @param $rangeId   context range id
     * @param $active    plugin status (true or false)
     * @param $context   context of plugin activation
     */
    public function setPluginActivated ($id, $rangeId, $active, $context = 'sem')
    {
        $db = DBManager::get();
        $state = $active ? 1 : 0;
        unset($this->plugins_activated_cache[$rangeId]);

        $stmt = $db->prepare('REPLACE INTO plugins_activated (pluginid, range_type, range_id, state)
                              VALUES (?, ?, ?, ?)');
        return $stmt->execute([$id, $context, $rangeId, $state]);
    }

    /**
     * Returns the list of institutes for which a specific plugin is
     * enabled by default.
     *
     * @param $id        id of the plugin
     */
    public function getDefaultActivations ($id)
    {
        $db = DBManager::get();

        $result = $db->query("SELECT institutid FROM plugins_default_activations
                              WHERE pluginid = '$id'");

        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Set the list of institutes for which a specific plugin should
     * be enabled by default.
     *
     * @param $id          id of the plugin
     * @param $institutes  array of institute ids
     */
    public function setDefaultActivations ($id, $institutes)
    {
        $db = DBManager::get();

        $result = $db->query("DELETE FROM plugins_default_activations
                              WHERE pluginid = '$id'");

        $stmt = $db->prepare("INSERT INTO plugins_default_activations
                              (pluginid, institutid) VALUES (?,?)");
        $this->plugins_default_activations_cache = [];
        foreach ($institutes as $instid) {
            $stmt->execute([$id, $instid]);
        }
    }

    /**
     * Disable loading of all non-core plugins for the current session.
     *
     * @param $status      true: disable non-core plugins
     */
    public function setPluginsDisabled($status)
    {
        $_SESSION['plugins_disabled'] = (bool) $status;
    }

    /**
     * Check whether loading of non-core plugins is currently disabled.
     */
    public function isPluginsDisabled()
    {
        return $_SESSION['plugins_disabled'];
    }

    /**
     * Load a plugin class from the given file system path and
     * return the ReflectionClass instance for the plugin.
     *
     * @param $class     plugin class name
     * @param $path      plugin relative path
     */
    private function loadPlugin ($class, $path)
    {
        if ($path) {
            $basepath = Config::get()->PLUGINS_PATH;
        } else {
            $basepath = $GLOBALS['STUDIP_BASE_PATH'];
            $path = 'lib/modules';
        }

        $pluginfile = $basepath.'/'.$path.'/'.$class.'.class.php';

        if (!file_exists($pluginfile)) {
            $pluginfile = $basepath.'/'.$path.'/'.$class.'.php';

            if (!file_exists($pluginfile)) {
                return NULL;
            }
        }

        require_once $pluginfile;

        return new ReflectionClass($class);
    }

    /**
     * Determine the type of a plugin to be installed.
     *
     * @param $class     plugin class name
     * @param $path      plugin relative path
     */
    private function getPluginType ($class, $path)
    {
        $plugin_class = $this->loadPlugin($class, $path);
        $types = [];

        if ($plugin_class) {
            $plugin_base = new ReflectionClass('StudIPPlugin');
            $interfaces = $plugin_class->getInterfaces();

            if ($plugin_class->isSubclassOf($plugin_base)) {
                foreach ($interfaces as $interface) {
                    $types[] = $interface->getName();
                }
            }
        }

        sort($types);

        return $types;
    }

    /**
     * Register a new plugin or update an existing plugin entry in the
     * data base. Returns the id of the new or updated plugin.
     *
     * @param $name      plugin name
     * @param $class     plugin class name
     * @param $path      plugin relative path
     * @param $depends   id of plugin this plugin depends on
     */
    public function registerPlugin ($name, $class, $path, $depends = NULL)
    {

        $db = DBManager::get();
        $info = $this->getPluginInfo($class);
        $type = $this->getPluginType($class, $path);
        $position = 1;

        // plugin must implement at least one interface
        if (count($type) == 0) {
            throw new Exception(_("Plugin implementiert kein gültiges Interface."));
        }

        if ($info) {
            $id = $info['id'];
            $sql = 'UPDATE plugins SET pluginname = ?, pluginpath = ?,
                                       plugintype = ? WHERE pluginid = ?';
            $stmt = $db->prepare($sql);
            $stmt->execute([$name, $path, join(',', $type), $id]);

            $this->plugins[$id]['name'] = $name;
            $this->plugins[$id]['path'] = $path;
            $this->plugins[$id]['type'] = $type;

            return $id;
        }

        foreach ($this->plugins as $plugin) {
            $common_types = array_intersect($type, $plugin['type']);

            if (count($common_types) > 0 && $plugin['position'] >= $position) {
                $position = $plugin['position'] + 1;
            }
        }

        $sql = 'INSERT INTO plugins (
                    pluginname, pluginclassname, pluginpath,
                    plugintype, navigationpos, dependentonid
                ) VALUES (?,?,?,?,?,?)';
        $stmt = $db->prepare($sql);
        $stmt->execute([$name, $class, $path, join(',', $type), $position, $depends]);
        $id = $db->lastInsertId();

        $this->plugins[$id] = [
            'id'          => $id,
            'name'        => $name,
            'class'       => $class,
            'path'        => $path,
            'type'        => $type,
            'enabled'     => false,
            'position'    => $position,
            'depends'     => $depends
        ];

        $this->readPluginInfos();

        $db->exec("INSERT INTO roles_plugins (roleid, pluginid)
                   SELECT roleid, $id FROM roles WHERE system = 'y' AND rolename != 'Nobody'");

        return $id;
    }

    /**
     * Remove registration for the given plugin from the data base.
     *
     * @param $id        id of the plugin
     */
    public function unregisterPlugin ($id)
    {
        $db = DBManager::get();
        $info = $this->getPluginInfoById($id);

        if ($info) {
            $db->exec("DELETE FROM plugins WHERE pluginid = '$id'");
            $db->exec("DELETE FROM plugins_activated WHERE pluginid = '$id'");
            $db->exec("DELETE FROM plugins_default_activations WHERE pluginid = '$id'");
            $db->exec("DELETE FROM roles_plugins WHERE pluginid = '$id'");

            unset($this->plugins[$id]);
            $this->plugins_default_activations_cache = [];
            $this->plugins_activated_cache = [];
        }
    }

    /**
     * Get meta data for the plugin specified by plugin class name.
     *
     * @param $class   class name of plugin
     */
    public function getPluginInfo ($class)
    {
        foreach ($this->plugins as $plugin) {
            if (strcasecmp($plugin['class'], $class) == 0) {
                return $plugin;
            }
        }

        return NULL;
    }

    /**
     * Get meta data for the plugin specified by plugin id.
     *
     * @param $id   id of the plugin
     */
    public function getPluginInfoById ($id)
    {
        if (isset($this->plugins[$id])) {
            return $this->plugins[$id];
        }

        return NULL;
    }

    /**
     * Get meta data for all plugins of the specified type. A type of NULL
     * returns meta data for all installed plugins.
     *
     * @param $type      plugin type or NULL (all types)
     */
    public function getPluginInfos ($type = NULL)
    {
        $result = [];

        foreach ($this->plugins as $id => $plugin) {
            if ($type === NULL || in_array($type, $plugin['type'])) {
                $result[$id] = $plugin;
            }
        }

        return $result;
    }

    /**
     * Check user access permission for the given plugin.
     *
     * @param $plugin   plugin meta data
     * @param $user     user id of user
     */
    protected function checkUserAccess ($plugin, $user)
    {
        if (!$plugin['enabled']) {
            return false;
        }

        $plugin_roles = $this->rolemgmt->getAssignedPluginRoles($plugin['id']);
        $user_roles = $this->rolemgmt->getAssignedRoles($user, true);

        foreach ($plugin_roles as $plugin_role) {
            foreach ($user_roles as $user_role) {
                if ($plugin_role->getRoleid() === $user_role->getRoleid()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get instance of the plugin specified by plugin meta data.
     *
     * @param $plugin_info   plugin meta data
     */
    protected function getCachedPlugin ($plugin_info)
    {
        $class = $plugin_info['class'];
        $path  = $plugin_info['path'];

        if (isset($this->plugin_cache[$class])) {
            return $this->plugin_cache[$class];
        }

        if ($plugin_info['core'] || !$this->isPluginsDisabled()) {
            $plugin_class = $this->loadPlugin($class, $path);
        }

        if ($plugin_class) {
            $plugin = $plugin_class->newInstance();
        }

        return $this->plugin_cache[$class] = $plugin;
    }

    /**
     * Get instance of the plugin specified by plugin class name.
     *
     * @param $class   class name of plugin
     */
    public function getPlugin ($class)
    {
        $user = $GLOBALS['user']->id;
        $plugin_info = $this->getPluginInfo($class);

        if (isset($plugin_info) && $this->checkUserAccess($plugin_info, $user)) {
            $plugin = $this->getCachedPlugin($plugin_info);
        }

        return $plugin;
    }

    /**
     * Get instance of the plugin specified by plugin id.
     *
     * @param $id   id of the plugin
     */
    public function getPluginById ($id)
    {
        $user = $GLOBALS['user']->id;
        $plugin_info = $this->getPluginInfoById($id);

        if (isset($plugin_info) && $this->checkUserAccess($plugin_info, $user)) {
            $plugin = $this->getCachedPlugin($plugin_info);
        }

        return $plugin;
    }

    /**
     * Get instances of all plugins of the specified type. A type of NULL
     * returns all enabled plugins. The optional context parameter can be
     * used to get only plugins that are activated in the given context.
     *
     * @param $type      plugin type or NULL (all types)
     * @param $context   context range id (optional)
     */
    public function getPlugins ($type, $context = NULL)
    {
        $user = $GLOBALS['user']->id ?: 'nobody';
        $plugin_info = $this->getPluginInfos($type);
        $plugins = [];

        usort($plugin_info, ['self', 'positionCompare']);

        foreach ($plugin_info as $info) {
            $activated = $context == NULL
                || $this->isPluginActivated($info['id'], $context);

            if ($this->checkUserAccess($info, $user) && $activated) {
                $plugin = $this->getCachedPlugin($info);

                if ($plugin !== NULL) {
                    $plugins[] = $plugin;
                }
            }
        }

        return $plugins;
    }

    /**
     * Read the manifest of the plugin in the given directory.
     * Returns NULL if the manifest cannot be found.
     *
     * @return array    containing the manifest information
     */
    public function getPluginManifest($plugindir)
    {
        $manifest = @file($plugindir . '/plugin.manifest');
        $result = [];

        if ($manifest === false) {
            return NULL;
        }

        foreach ($manifest as $line) {
            list($key, $value) = explode('=', $line);
            $key = trim($key);
            $value = trim($value);

            // skip empty lines and comments
            if ($key === '' || $key[0] === '#') {
                continue;
            }

            $key_array = explode('.',$key,2);
            if(count($key_array) > 1){
                if($key_array[0] === 'screenshots'){
                    $screenshot_data['source'] = $key_array[1];
                    $screenshot_data['title'] = $value;
                    $result['screenshots']['pictures'][] = $screenshot_data;
                }
            } elseif($key === 'screenshots') {
                $result['screenshots']['path'] = $value;
            } elseif ($key === 'pluginclassname' && isset($result[$key])) {
                $result['additionalclasses'][] = $value;
            } elseif ($key === 'screenshot' && isset($result[$key])) {
                $result['additionalscreenshots'][] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}