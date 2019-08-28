<?php
# Lifter010: TODO
/*
 * plugin_administration.php - plugin administration model class
 *
 * Copyright (c) 2009  Dennis Reil, Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * Model code for plugin administration tasks.
 */
class PluginAdministration
{
    /**
     * Install a new plugin. Extracts the contents of the uploaded file,
     * checks the manifest, creates the new plugin directory und finally
     * registers the plugin in the database.
     *
     * @param string $filename path to the uploaded file
     */
    public function installPlugin($filename)
    {
        global $SOFTWARE_VERSION;

        $packagedir = get_config('PLUGINS_PATH') . '/tmp_' . md5($filename);

        // extract plugin files
        if (!file_exists($packagedir) && mkdir($packagedir) === false) {
            throw new PluginInstallationException(_('Fehler beim Entpacken des Plugins (fehlende Schreibrechte?).'));
        }

        if (!Studip\ZipArchive::extractToPath($filename, $packagedir)) {
            rmdirr($packagedir);
            throw new PluginInstallationException(_('Fehler beim Entpacken des Plugins.'));
        } else {
            $tmpplugindir = $packagedir;
            $files = scandir($packagedir);
            if (count($files) === 3) {
                foreach ($files as $file) {
                    if (!in_array($file, [".",".."])) {
                        $tmpplugindir .= "/" . $file;
                    }
                }
            }
        }

        // check if the plugin might be located in a subfolder
        $files = glob($packagedir . '/*');
        $dirs  = array_filter($files, 'is_dir');
        if (!file_exists($packagedir . '/plugin.manifest') && count($dirs) === 1) {
            $packagedir = $dirs[0];
        }

        // load the manifest
        $plugin_manager = PluginManager::getInstance();
        $manifest = $plugin_manager->getPluginManifest($tmpplugindir);

        if ($manifest === NULL) {
            rmdirr($packagedir);
            throw new PluginInstallationException(_('Das Manifest des Plugins fehlt.'));
        }

        // get plugin meta data
        $pluginclass = $manifest['pluginclassname'];
        $origin      = $manifest['origin'];
        $min_version = $manifest['studipMinVersion'];
        $max_version = $manifest['studipMaxVersion'];

        // check for compatible version
        if ((isset($min_version) && StudipVersion::olderThan($min_version)) ||
            (isset($max_version) && StudipVersion::newerThan($max_version))) {
            rmdirr($packagedir);
            throw new PluginInstallationException(_('Das Plugin ist mit dieser Stud.IP-Version nicht kompatibel.'));
        }

        // determine the plugin path
        $basepath = get_config('PLUGINS_PATH');
        $pluginpath = $origin . '/' . $pluginclass;
        $plugindir = $basepath . '/' . $pluginpath;

        $pluginregistered = $plugin_manager->getPluginInfo($pluginclass);

        // is the plugin already installed?
        if (file_exists($plugindir)) {
            if ($pluginregistered) {
                $this->updateDBSchema($plugindir, $tmpplugindir, $manifest);
            }

            rmdirr($plugindir);

            // on NFS file system, removing the plugin may fail (see ticket #1892)
            if (file_exists($plugindir)) {
                $plugindir_old = $plugindir . '.old';
                rmdirr($plugindir_old);
                rename($plugindir, $plugindir_old);
            }
        }

        // move directory to final destination
        if (!file_exists($basepath . '/' . $origin) && mkdir($basepath . '/' . $origin) === false) {
            throw new PluginInstallationException(sprintf(
                _('Der Ordner "%s" konnte nicht erstellt werden.'),
                studip_relative_path($basepath . '/' . $origin)
            ));
        }

        if (!is_writable($base_path . '/' . $origin)) {
            throw new PluginInstallationException(sprintf(
                _('Der Ordner "%s" ist nicht schreibbar.'),
                studip_relative_path($basepath . '/' . $origin)
            ));
        }

        rename($tmpplugindir, $plugindir);

        // create database schema if needed
        $this->createDBSchema($plugindir, $manifest, $pluginregistered);

        // now register the plugin in the database
        $pluginid = $plugin_manager->registerPlugin($manifest['pluginname'], $pluginclass, $pluginpath);

        if ($pluginid === NULL) {
            rmdirr($plugindir);
            throw new PluginInstallationException(_('Das Plugin enthält keine gültige Plugin-Klasse.'));
        }

        // register additional plugin classes in this package
        $additionalclasses = $manifest['additionalclasses'];

        if (is_array($additionalclasses)) {
            foreach ($additionalclasses as $class) {
                $plugin_manager->registerPlugin($class, $class, $pluginpath, $pluginid);
            }
        }
        rmdirr($packagedir);
        return $pluginid;
    }

    /**
     * Download and install a new plugin from the given URL.
     *
     * @param string $plugin_url the URL of the plugin package
     */
    public function installPluginFromURL($plugin_url)
    {
        $temp_name = tempnam(get_config('TMP_PATH'), 'plugin');

        if (!@copy($plugin_url, $temp_name)) {
            throw new PluginInstallationException(_('Das Herunterladen des Plugins ist fehlgeschlagen.'));
        }

        $pluginid = $this->installPlugin($temp_name);
        unlink($temp_name);
        return $pluginid;
    }

    /**
     * Download and install a plugin with the given name from the
     * plugin repository.
     *
     * @param string $pluginname name of the plugin to install
     */
    public function installPluginByName($pluginname)
    {
        $repository = new PluginRepository();
        $plugin = $repository->getPlugin($pluginname);

        if (!isset($plugin)) {
            throw new PluginInstallationException(_('Das Plugin konnte nicht gefunden werden.'));
        }

        $this->installPluginFromURL($plugin['url']);
    }

    /**
     * Uninstall the given plugin from the system. It will remove
     * the database schema and all the plugin's files.
     *
     * @param array $plugin meta data of plugin
     */
    public function uninstallPlugin($plugin)
    {
        $plugin_manager = PluginManager::getInstance();

        // check if there are dependent plugins
        foreach ($plugin_manager->getPluginInfos() as $dep_plugin) {
            if ($dep_plugin['depends'] === $plugin['id']) {
                $plugin_manager->unregisterPlugin($dep_plugin['id']);
            }
        }

        $plugin_manager->unregisterPlugin($plugin['id']);
        $plugindir = get_config('PLUGINS_PATH') . '/' . $plugin['path'];
        $manifest = $plugin_manager->getPluginManifest($plugindir);

        // delete database if needed
        $this->deleteDBSchema($plugindir, $manifest);
        PluginAsset::deleteBySQL('plugin_id = ?', [$plugin['id']]);

        rmdirr($plugindir);
    }

    /**
     * Create the initial database schema for the plugin.
     *
     * @param string  $plugindir  absolute path to the plugin
     * @param array   $manifest   plugin manifest information
     * @param boolean $update     update installed plugin
     */
    private function createDBSchema($plugindir, $manifest, $update)
    {
        $pluginname = $manifest['pluginname'];

        if (isset($manifest['dbscheme']) && !$update) {
            $schemafile = $plugindir . '/' . $manifest['dbscheme'];
            $contents   = file_get_contents($schemafile);
            $statements = preg_split("/;[[:space:]]*\n/", $contents, -1, PREG_SPLIT_NO_EMPTY);
            $db = DBManager::get();
            foreach ($statements as $statement) {
                $db->exec($statement);
            }
        }

        if (is_dir($plugindir . '/migrations')) {
            $schema_version = new DBSchemaVersion($pluginname);
            $migrator = new Migrator($plugindir . '/migrations', $schema_version);
            $migrator->migrateTo(null);
        }
    }

    /**
     * Update the database schema maintained by the plugin.
     *
     * @param string $plugindir      absolute path to the plugin
     * @param string $new_pluginpath absolute path to updated plugin
     * @param array  $manifest       plugin manifest information
     */
    private function updateDBSchema($plugindir, $new_pluginpath, $manifest)
    {
        $pluginname = $manifest['pluginname'];

        if (is_dir($plugindir . '/migrations')) {
            $schema_version = new DBSchemaVersion($pluginname);
            $new_version = 0;

            if (is_dir($new_pluginpath . '/migrations')) {
                $migrator = new Migrator($new_pluginpath . '/migrations', $schema_version);
                $new_version = $migrator->topVersion();
            }

            $migrator = new Migrator($plugindir . '/migrations', $schema_version);
            $migrator->migrateTo($new_version);
        }
    }

    /**
     * Delete the database schema maintained by the plugin.
     *
     * @param string $plugindir  absolute path to the plugin
     * @param array  $manifest   plugin manifest information
     */
    private function deleteDBSchema($plugindir, $manifest)
    {
        $pluginname = $manifest['pluginname'];

        if (is_dir($plugindir . '/migrations')) {
            $schema_version = new DBSchemaVersion($pluginname);
            $migrator = new Migrator($plugindir . '/migrations', $schema_version);
            $migrator->migrateTo(0);
        }

        if (isset($manifest['uninstalldbscheme'])) {
            $schemafile = $plugindir . '/' . $manifest['uninstalldbscheme'];
            $contents   = file_get_contents($schemafile);
            $statements = preg_split("/;[[:space:]]*\n/", $contents, -1, PREG_SPLIT_NO_EMPTY);
            $db = DBManager::get();
            foreach ($statements as $statement) {
                $db->exec($statement);
            }
        }
    }

    /**
     * Get a list of all available institutes.
     */
    public function getInstitutes()
    {
        $db = DBManager::get();
        $institutes = [];

        $sql = 'SELECT Institut_id, Name, fakultaets_id, Institut_id = fakultaets_id AS is_fak
                FROM Institute ORDER BY is_fak DESC, Name';
        $result = $db->query($sql)->fetchAll();

        foreach ($result as $row) {
            $inst_id = $row['Institut_id'];
            $fak_id = $row['fakultaets_id'];

            if ($inst_id == $fak_id) {
                $institutes[$inst_id] = ['name' => $row['Name']];
            } else {
                $institutes[$fak_id]['children'][$inst_id] = ['name' => $row['Name']];
            }
        }

        return $institutes;
    }

    /**
     * Get a list of the types of all installed plugins.
     *
     * @return array    list of plugin types
     */
    public function getPluginTypes()
    {
        $plugin_manager = PluginManager::getInstance();
        $plugin_infos   = $plugin_manager->getPluginInfos();
        $plugin_types   = [];

        foreach ($plugin_infos as $plugin) {
            $plugin_types = array_merge($plugin_types, $plugin['type']);
        }

        sort($plugin_types);
        return array_unique($plugin_types);
    }

    /**
     * Fetch update information for a list of plugins. This method
     * returns for each plugin: the plugin name, current version and
     * meta data of the plugin update, if available.
     *
     * @param array  $plugins    array of plugin meta data
     */
    public function getUpdateInfo($plugins)
    {
        $default_repository = new PluginRepository();
        $plugin_manager = PluginManager::getInstance();
        $update_info = [];

        foreach ($plugins as $plugin) {
            $repository = $default_repository;
            $plugindir = get_config('PLUGINS_PATH') . '/' . $plugin['path'];
            $manifest = $plugin_manager->getPluginManifest($plugindir);

            if (isset($manifest['updateURL'])) {
                $repository = new PluginRepository($manifest['updateURL']);
            }

            $meta_data = $repository->getPlugin($manifest['pluginname']);

            if (isset($meta_data) &&
                version_compare($meta_data['version'], $manifest['version']) > 0) {
                $manifest['update'] = $meta_data;
            }

            $update_info[$plugin['id']] = $manifest;
        }

        return $update_info;
    }

    /**
     * Fetch migration information plugins. This method
     * returns for each plugin:
     * current schema version and top migration version, if available.
     *
     * @return array
     */
    public function getMigrationInfo()
    {
        $info = [];
        $plugin_manager = PluginManager::getInstance();
        $plugins = $plugin_manager->getPluginInfos();
        $basepath = get_config('PLUGINS_PATH');
        foreach ($plugins as $id => $plugin) {
            $plugindir = $basepath . '/' . $plugin['path'] . '/';
            if (is_dir($plugindir . '/migrations')) {
                $schema_version = new DBSchemaVersion($plugin['name']);
                $migrator = new Migrator($plugindir . '/migrations', $schema_version);
                $info[$id]['migration_top_version'] = $migrator->topVersion();
                $info[$id]['schema_version'] = $schema_version->get();
            }
        }
        return $info;
    }

    /**
     * migrate plugin to top migration
     *
     * @param integer $plugin_id
     * @return string output from migrator
     */
    public function migratePlugin($plugin_id)
    {
        $plugin_manager = PluginManager::getInstance();
        $plugin = $plugin_manager->getPluginInfoById($plugin_id);
        $basepath = get_config('PLUGINS_PATH');
        $plugindir = $basepath . '/' . $plugin['path'] . '/';
        if (is_dir($plugindir . '/migrations')) {
            $schema_version = new DBSchemaVersion($plugin['name']);
            $migrator = new Migrator($plugindir .'/migrations', $schema_version, true);
            ob_start();
            $migrator->migrateTo(null);
            $log = ob_get_clean();
        }
        return $log;
    }

    /**
     * scans PLUGINS_PATH for plugin.manifest files
     * belonging to not registered plugins
     *
     * @return array with manifest meta data
     */
    public function scanPluginDirectory()
    {
        $found = [];
        $basepath = get_config('PLUGINS_PATH');
        $plugin_manager = PluginManager::getInstance();
        $iterator = new RegexIterator(
                        new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($basepath, FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::UNIX_PATHS)),
                        '/plugin\.manifest$/', RecursiveRegexIterator::MATCH);
        foreach ($iterator as $manifest_file) {
            $manifest = $plugin_manager->getPluginManifest($manifest_file->getPath());
            $pluginpath = $basepath . '/' . $manifest['origin'] . '/' . $manifest['pluginclassname'];
            if (!$plugin_manager->getPluginInfo($manifest['pluginclassname'])
                && $pluginpath === $manifest_file->getPath()) {
                $manifest['path'] = $manifest_file->getPath();
                $found[] = $manifest;
            }
        }
        return $found;
    }

    /**
     * registers plugin at given path in database
     *
     * @param string $plugindir path to plugin
     * @throws PluginInstallationException
     */
    public function registerPlugin($plugindir)
    {
        $plugin_manager = PluginManager::getInstance();
        $manifest = $plugin_manager->getPluginManifest($plugindir);
        if (!$manifest) {
            throw new PluginInstallationException(_('Das Manifest des Plugins fehlt.'));
        }

        // get plugin meta data
        $pluginclass = $manifest['pluginclassname'];
        $origin      = $manifest['origin'];
        $min_version = $manifest['studipMinVersion'];
        $max_version = $manifest['studipMaxVersion'];

        // check for compatible version
        if ((isset($min_version) && StudipVersion::olderThan($min_version)) ||
            (isset($max_version) && StudipVersion::newerThan($max_version))) {
            throw new PluginInstallationException(_('Das Plugin ist mit dieser Stud.IP-Version nicht kompatibel.'));
        }

        // determine the plugin path
        $basepath = get_config('PLUGINS_PATH');
        $pluginpath = $origin . '/' . $pluginclass;

        $pluginregistered = $plugin_manager->getPluginInfo($pluginclass);

        if ($pluginregistered) {
            new PluginInstallationException(_('Das Plugin ist bereits registriert.'));
        }

        // create database schema if needed
        $this->createDBSchema($plugindir, $manifest, $pluginregistered);

        // now register the plugin in the database
        $pluginid = $plugin_manager->registerPlugin($manifest['pluginname'], $pluginclass, $pluginpath);

        // register additional plugin classes in this package
        $additionalclasses = $manifest['additionalclasses'];

        if (is_array($additionalclasses)) {
            foreach ($additionalclasses as $class) {
                $plugin_manager->registerPlugin($class, $class, $pluginpath, $pluginid);
            }
        }
    }
}
