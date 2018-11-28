<?php
/**
 * Creates the neccessary tables for the files search index.
 *
 * @author  <mlunzena@uos.de>
 * @license GPL2 or any later version
 */
class AddFilesSearchIndex extends Migration
{
    public function description()
    {
        return 'Sets up the database tables for the files search index';
    }

    /**
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function up()
    {
        $this->createTables();
        $this->registerWidgets();
        $this->setupDefaultWidgets();
        $this->installCronjob();
    }

    public function down()
    {
        $dbm = \DBManager::get();
        $dbm->execute('DROP TABLE `files_search_index`, `files_search_attributes`');
    }

    // Get version of database system (MySQL/MariaDB/Percona)
    private static function isMySQL55()
    {
        $data = \DBManager::get()->fetchFirst("SELECT VERSION() AS version");
        $version = $data[0];
        return version_compare($version, '5.6', '<');
    }

    // The primary key is named FTS_DOC_ID according to
    // https://dev.mysql.com/doc/refman/5.6/en/innodb-fulltext-index.html
    private function createTables()
    {
        $engine = self::isMySQL55() ? 'MyISAM' : 'InnoDB';
        $dbm = \DBManager::get();
        $dbm->execute(
            'CREATE TABLE IF NOT EXISTS `files_search_index` (
             `file_ref_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
             `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
             `relevance` float NOT NULL,
             KEY `file_ref_id` (`file_ref_id`),
             FULLTEXT KEY `text` (`text`)
            ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC'
        );
        $dbm->execute('
            CREATE TABLE IF NOT EXISTS `files_search_attributes` (
             `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
             `file_ref_user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
             `file_ref_mkdate` int(10) unsigned NOT NULL,
             `file_ref_chdate` int(10) unsigned NOT NULL,
             `folder_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
             `folder_range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
             `folder_range_type` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
             `folder_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
             `course_status` tinyint(4) unsigned DEFAULT NULL,
             `semester_start` int(20) unsigned DEFAULT NULL,
             `semester_end` int(20) unsigned DEFAULT NULL,
             PRIMARY KEY (`id`),
             KEY `folder_range_id` (`folder_range_id`),
             KEY `folder_range_type` (`folder_range_type`),
             KEY `semester_start` (`semester_start`),
             KEY `semester_end` (`semester_end`)
            ) ENGINE=' . $engine . ' DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC
        ');
    }

    private function registerWidgets()
    {
        $widgets = [
            'app/widgets/files_dashboard/MyPublicFilesWidget.php' => 'Widgets\\FilesDashboard\\MyPublicFilesWidget',

            'app/widgets/files_dashboard/LatestFilesWidget.php' => 'Widgets\\FilesDashboard\\LatestFilesWidget'
        ];

        foreach ($widgets as $path => $class) {
            require $path;
            \Widgets\Widget::registerWidget(new $class());
        }
    }

    private function setupDefaultWidgets()
    {
        $widgets = [
            'Widgets\\FilesDashboard\\LatestFilesWidget' => ['width' => 3, 'height' => 3],
            'Widgets\\FilesDashboard\\MyPublicFilesWidget' => ['width' => 3, 'height' => 3],
        ];

        foreach (['user', 'autor', 'tutor', 'dozent', 'admin', 'root'] as $perm) {
            $this->createDefaultContainer('user', $perm, 'dashboard', $widgets);
        }
    }

    private function createDefaultContainer($rangeType, $rangeId, $scope, array $widgets)
    {
        $containerId = $this->createNewContainer($rangeType, $rangeId, $scope);

        $xCoord = 0;
        $yCoord = 0;
        $yMax   = 0;
        foreach ($widgets as $class => $additional) {
            $this->addWidgetToContainerByClass($containerId, $class, array_merge([
                'x' => $xCoord,
                'y' => $yCoord,
            ], $additional));

            $yMax = max($yMax, $additional['height'] ?: 1);

            $xCoord += $additional['width'];
            if ($xCoord >= 6) {
                $xCoord = 0;

                $yCoord += $yMax;
                $yMax = 0;
            }
        }
    }

    private function createNewContainer($rangeType, $rangeId, $scope)
    {
        $query = 'INSERT INTO `widget_containers` (
                    `range_id`, `range_type`, `scope`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    :id, :type, :scope,
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )';
        $statement = \DBManager::get()->prepare($query);
        $statement->bindValue(':id', $rangeId);
        $statement->bindValue(':type', $rangeType);
        $statement->bindValue(':scope', $scope);
        $statement->execute();

        return \DBManager::get()->lastInsertId();
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function addWidgetToContainerByClass($containerId, $widgetClass, array $data = [])
    {
        $query = "INSERT INTO `widget_elements` (
                    `container_id`, `widget_id`,
                    `x`, `y`, `width`, `height`,
                    `locked`, `removable`, `options`,
                    `mkdate`, `chdate`
                  )
                  SELECT :container_id, `widget_id`,
                         :x, :y, :width, :height,
                         1, 0, '[]',
                         UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  FROM `widgets`
                  WHERE `class` = :class";
        $statement = \DBManager::get()->prepare($query);
        $statement->bindValue(':container_id', $containerId);
        $statement->bindValue(':x', $data['x'] ?: 0);
        $statement->bindValue(':y', $data['y'] ?: 0);
        $statement->bindValue(':width', $data['width'] ?: 1);
        $statement->bindValue(':height', $data['height'] ?: 1);
        $statement->bindValue(':class', $widgetClass);
        $statement->execute();
    }

    private function installCronjob()
    {
        require_once 'lib/classes/FilesSearch/Cronjob.php';
        $task = new  \FilesSearch\Cronjob();
        $taskId = CronjobScheduler::getInstance()->registerTask($task);
        CronjobScheduler::scheduleOnce($taskId, strtotime('+1 minute'))->activate();
        CronjobScheduler::schedulePeriodic($taskId, 55, 0)->activate();
    }
}
