<?php
/**
 * Creates the neccessary tables for the stud.ip widget system.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 *
 * @todo Migrate the old widget system
 */
class Widgets extends Migration
{
    public function description()
    {
        return 'Sets up the database tables for the new widget system';
    }

    public function up()
    {
        $this->createTables();
        $this->registerDefaultWidgets();

        // $plugin_ids = $this->getValidPortalPluginIds();
        // $mapping    = $this->registerLegacyWidgets($plugin_ids);
        //
        // $this->migrateOldDefaults($plugin_ids, $mapping);
        // $this->migrateOldWidgets($plugin_ids, $mapping);
        // $this->migrateOldSettings();
        //
        // $this->setDefaultsForCourseDetails();
        // $this->setDefaultsForCourseOverview();
        // $this->setDefaultsForInstituteDetails();
        // $this->setDefaultsForUserProfile();
        //
        // $this->unregisterOldPlugins($plugin_ids);
        //
        // $this->dropOldTables();

        $this->activateRoutes();

        // $this->updateHelpContent();
    }

    private function createTables()
    {
        $query = "CREATE TABLE IF NOT EXISTS `widget_containers` (
                    `container_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `range_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL,
                    `range_type` ENUM('course','institute','user','plugin','other') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'course',
                    `scope` VARCHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'default',
                    `parent_id` INT(11) UNSIGNED NULL DEFAULT NULL,
                    `mkdate` INT(11) UNSIGNED NOT NULL,
                    `chdate` INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (`container_id`),
                    UNIQUE KEY `range` (`range_id`, `range_type`, `scope`),
                    KEY `parent_id` (`parent_id`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        $query = "CREATE TABLE IF NOT EXISTS `widget_elements` (
                    `element_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `container_id` INT(11) UNSIGNED NOT NULL,
                    `widget_id` INT(11) UNSIGNED NOT NULL,
                    `x` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                    `y` TINYINT(3) UNSIGNED NOT NULL,
                    `width` TINYINT(1) UNSIGNED NOT NULL,
                    `height` TINYINT(1) UNSIGNED NOT NULL,
                    `locked` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                    `removable` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                    `options` VARCHAR(8192) NOT NULL DEFAULT '[]',
                    `mkdate` INT(11) UNSIGNED NOT NULL,
                    `chdate` INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (`element_id`),
                    KEY `container_id` (`container_id`),
                    KEY `widget_id` (`widget_id`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);

        $query = "CREATE TABLE IF NOT EXISTS `widgets` (
                    `widget_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `class` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                    `filename` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL,
                    `enabled` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
                    `mkdate` INT(11) UNSIGNED NOT NULL,
                    `chdate` INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (`widget_id`),
                    UNIQUE KEY `class` (`class`)
                  ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
        DBManager::get()->exec($query);
    }

    private function registerDefaultWidgets()
    {
        $files = array_merge(
            glob(__DIR__ . '/../../app/widgets/*.php'),
            glob(__DIR__ . '/../../app/widgets/course/*.php')
        );

        // Register default widgets
        foreach ($files as $file) {
            require_once $file;

            $class = basename($file, '.php');
            Widgets\Widget::registerWidget(new $class);
        }
    }

    private function dropOldTables()
    {
        // Drop old tables
        $query = "DROP TABLE IF EXISTS `widget_default`, `widget_user`";
        DBManager::get()->exec($query);
    }

    private function getValidPortalPluginIds()
    {
        // Load and validate plugins
        $query = "SELECT `pluginid`, CONCAT(`pluginpath`, '/', `pluginclassname`)
                  FROM `plugins`
                  WHERE FIND_IN_SET('PortalPlugin', `plugintype`) > 0";
        $statement = DBManager::get()->query($query);
        $plugin_paths = $statement->fetchGrouped(PDO::FETCH_COLUMN);

        $base_path = Config::get()->PLUGINS_PATH;
        foreach ($plugin_paths as $plugin_id => $path) {
            if (strpos($path, 'core/') === 0) {
                continue;
            }

            $files = glob($base_path . '/' . $path . '{,.class}.php', GLOB_BRACE);
            if (count($files) === 0) {
                unset($plugin_paths[$plugin_id]);
            }
        }
        return array_keys($plugin_paths);
    }

    private function registerLegacyWidgets(array $plugin_ids)
    {
        // Register legacy widgets
        $query = "INSERT IGNORE INTO `widgets` (`class`, `filename`, `enabled`, `mkdate`, `chdate`)
                  SELECT `pluginid`, NULL, `enabled` = 'yes', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  FROM `plugins`
                  WHERE `pluginid` IN (:ids)
                    AND `pluginpath` NOT LIKE 'core/%'";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':ids', $plugin_ids, StudipPDO::PARAM_ARRAY);
        $statement->execute();

        // Get plugin -> widget mapping
        $query = "SELECT `class`, `widget_id`
                  FROM `widgets`
                  WHERE `filename` IS NULL";
        $statement = DBManager::get()->query($query);
        $mapping0 = $statement->fetchGrouped(PDO::FETCH_COLUMN);

        // Add already converted core plugins
        $plugins_to_widgets = [
            'QuickSelection|QuickSelectionWidget',
            'ScheduleWidget|ScheduleWidget',
            'TerminWidget|CalendarWidget',
            'ActivityFeed|ActivityFeedWidget',
            'NewsWidget|NewsWidget',
            'EvaluationsWidget|EvaluationsWidget',
        ];
        $query = "SELECT `pluginid`, `widget_id`
                  FROM `plugins`, `widgets`
                  WHERE CONCAT(`pluginclassname`, '|', `class`)
                     IN (:mapping)";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':mapping', $plugins_to_widgets);
        $statement->execute();
        $mapping1 = $statement->fetchGrouped(PDO::FETCH_COLUMN);

        return $mapping0 + $mapping1;
    }

    private function migrateOldDefaults(array $plugin_ids, array $mapping)
    {
        $query = "SELECT `perm`, `pluginid`, `col`, `position`
                  FROM `widget_default`
                  JOIN `plugins` USING (`pluginid`)
                  WHERE `enabled` = 'yes'
                    AND `pluginid` IN (:ids)
                  ORDER BY `perm`, `position` ASC, `col` ASC";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':ids', $plugin_ids, StudipPDO::PARAM_ARRAY);
        $statement->execute();
        $defaults = $statement->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        $this->migrateWidgetsAndContainers($defaults, $mapping);
    }

    private function migrateOldWidgets(array $plugin_ids, array $mapping)
    {
        $query = "SELECT `range_id`, `pluginid`, `col`, `position`
                  FROM `widget_user`
                  JOIN `plugins` USING (`pluginid`)
                  WHERE `enabled` = 'yes'
                    AND `pluginid` IN (:ids)
                  ORDER BY `range_id`, `position` ASC, `col` ASC";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':ids', $plugin_ids, StudipPDO::PARAM_ARRAY);
        $statement->execute();
        $widgets = $statement->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

        $this->migrateWidgetsAndContainers($widgets, $mapping);
    }

    private function migrateWidgetsAndContainers(array $data, array $mapping)
    {
         foreach ($data as $id => $rows) {
            $container_id = $this->createNewContainer('user', $id, 'start');

            foreach ($rows as $row) {
                $this->addWidgetToContainerById($container_id, $mapping[$row['pluginid']], [
                    'x'     => $row['col'] * 2,
                    'y'     => $row['position'],
                    'width' => $row['col'] == 0 ? 4 : 2,
                    'height' => 1,
                ]);
            }
        }
    }

    private function migrateOldSettings()
    {
        // Quick selection widget
        $query = "UPDATE `widget_elements` AS `we`
                  -- Get correct widget
                  JOIN `widgets` AS `w` USING (`widget_id`)
                  -- Get correct configuration
                  JOIN `widget_containers` AS `wc` USING (`container_id`)
                  JOIN `user_config` AS `uc`
                  ON (`uc`.`user_id` = `wc`.`range_id`
                      AND `wc`.`range_type` = 'user'
                      AND `uc`.`field` = 'QUICK_SELECTION')
                  SET `we`.`options` = `uc`.`value`
                  WHERE `w`.`class` = 'QuickSelectionWidget'";
        DBManager::get()->exec($query);

        $query = "DELETE FROM `user_config` WHERE `field` = 'QUICK_SELECTION'";
        DBManager::get()->exec($query);
    }

    private function unregisterOldPlugins(array $plugin_ids)
    {
        // Remove old core widgets
        $query = "DELETE FROM `plugins`
                  WHERE `pluginid` IN (:ids)
                    AND `pluginpath` LIKE 'core/%'";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':ids', $plugin_ids);
        $statement->execute();

        // Deactivate other plugins
        $query = "UPDATE `plugins`
                  SET `enabled` = 'no'
                  WHERE `pluginid` IN (:ids)";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':ids', $plugin_ids);
        $statement->execute();
    }

    private function setDefaultsForCourseDetails()
    {
        // Define widgets
        $widgets = [
            'CourseDetailsWidget'    => ['height' => 3],
            'CourseTeachersWidget'   => ['height' => 2],
            'CourseEventsWidget'     => ['height' => 2],
            'CourseTopicsWidget'     => ['height' => 2],
            'CourseRoomsWidget'      => ['height' => 2],
            'CourseModulesWidget'    => ['height' => 3],
            'CourseStudyareasWidget' => ['height' => 3],
            'CourseAdmissionWidget'  => ['height' => 3],
            'CourseDomainsWidget'    => [],
        ];

        // Check whether mvv is activated, remove widget if not
        $query = "SELECT `enabled` = 'yes'
                  FROM `plugins`
                  WHERE `pluginclassname` = 'MVVPlugin'";
        $mvv_enabled = DBManager::get()->fetchColumn($query);
        if (!$mvv_enabled) {
            unset($widgets['CourseModulesWidget']);
        }

        $this->createDefaultContainer('course', null, 'details', $widgets);
    }

    private function setDefaultsForCourseOverview()
    {
        $this->createDefaultContainer('course', null, 'overview', [
            'CourseDetailsWidget'    => ['height' => 3],
            'NewsWidget'             => ['height' => 2],
            'CalendarWidget'         => ['height' => 2],
            'EvaluationsWidget'      => [],
            'QuestionnaireWidget'    => [],
        ]);
    }

    private function setDefaultsForInstituteDetails()
    {
        $this->createDefaultContainer('institute', null, 'default', [
            'InstituteDetailsWidget' => ['height' => 3],
            'NewsWidget'             => ['height' => 2],
            'EvaluationsWidget'      => [],
            'QuestionnaireWidget'    => [],
        ]);
    }

    private function setDefaultsForUserProfile()
    {
        $widgets = [
            'ProfileDetailsWidget'    => ['height' => 5],
            'NewsWidget'              => ['height' => 2],
            'CalendarWidget'          => ['height' => 2, 'perms' => ['user', 'autor', 'tutor', 'dozent']],
            'EvaluationsWidget'       => [],
            'QuestionnaireWidget'     => [],
            'ProfileFilesWidget'      => ['height' => 2],
            'ProfileCoursesWidget'    => ['height' => 2, 'perms' => ['dozent']],
            'LiteratureWidget'        => [],
            'ProfileCategoriesWidget' => ['height' => 2],

        ];

        foreach (['user', 'autor', 'tutor', 'dozent', 'admin', 'root'] as $perm) {
            $perm_widgets = array_filter($widgets, function ($row) use ($perm) {
                return !isset($row['perms'])
                    || in_array($perm, $row['perms']);
            });

            $this->createDefaultContainer('user', $perm, 'profile', $perm_widgets);
        }
    }

    private function createDefaultContainer($type, $id, $scope, array $widgets)
    {
        // Create default container and add widgets
        $container_id = $this->createNewContainer($type, $id, $scope);

        $y = 0;
        foreach ($widgets as $class => $additional) {
            $this->addWidgetToContainerByClass($container_id, $class, array_merge([
                'y' => $y,
            ], $additional));

            $y += $additional['height'] ?: 1;
        }
    }

    private function createNewContainer($type, $id, $scope)
    {
        $query = "INSERT INTO `widget_containers` (
                    `range_id`, `range_type`, `scope`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    :id, :type, :scope,
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $id);
        $statement->bindValue(':type', $type);
        $statement->bindValue(':scope', $scope);
        $statement->execute();

        return DBManager::get()->lastInsertId();
    }

    private function addWidgetToContainerByClass($container_id, $widget_class, array $data = [])
    {
        $query = "INSERT INTO `widget_elements` (
                    `container_id`, `widget_id`,
                    `x`, `y`, `width`, `height`,
                    `locked`, `removable`, `options`,
                    `mkdate`, `chdate`
                  )
                  SELECT :container_id, `widget_id`,
                         :x, :y, :width, :height,
                         0, 1, '[]',
                         UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  FROM `widgets`
                  WHERE `class` = :class";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':container_id', $container_id);
        $statement->bindValue(':x', $data['x'] ?: 0);
        $statement->bindValue(':y', $data['y'] ?: 0);
        $statement->bindValue(':width', $data['width'] ?: 1);
        $statement->bindValue(':height', $data['height'] ?: 1);
        $statement->bindValue(':class', $widget_class);
        $statement->execute();
    }

    private function addWidgetToContainerById($container_id, $widget_id, array $data = [])
    {
        $query = "INSERT INTO `widget_elements` (
                    `container_id`, `widget_id`,
                    `x`, `y`, `width`, `height`,
                    `locked`, `removable`, `options`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    :container_id, :widget_id,
                    :x, :y, :width, :height,
                    0, 1, '[]',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':container_id', $container_id);
        $statement->bindValue(':widget_id', $widget_id);
        $statement->bindValue(':x', $data['x'] ?: 0);
        $statement->bindValue(':y', $data['y'] ?: 0);
        $statement->bindValue(':width', $data['width'] ?: 1);
        $statement->bindValue(':height', $data['height'] ?: 1);
        $statement->execute();
    }

    private function activateRoutes()
    {
        $permissions = RESTAPI\ConsumerPermissions::get('global');
        $permissions->set('/widgets/:container_id', 'put', true, true);
        $permissions->set('/widgets/:container_id/:widget_id', 'post', true, true);
        $permissions->set('/widgets/:container_id/:action/:element_id', 'get', true, true);
        $permissions->set('/widgets/:container_id/:action/:element_id', 'post', true, true);
        $permissions->set('/widgets/:container_id/:action/:element_id/:admin', 'get', true, true);
        $permissions->set('/widgets/:container_id/:action/:element_id/:admin', 'post', true, true);
        $permissions->store();
    }

    private function updateHelpContent()
    {
        $query = "UPDATE `help_content`
                  SET `route` = 'dispatch.php/course/info'
                  WHERE `route` = 'dispatch.php/course/details'";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        // Naaaah, don't do that...
    }
}
