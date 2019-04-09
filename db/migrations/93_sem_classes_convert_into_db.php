<?php

class SemClassesConvertIntoDb extends Migration
{
    /**
     * short description of this migration
     */
    function description()
    {
        return 'Converts variables $SEM_CLASS AND $SEM_TYPE from config.inc.php into database. You can unset it in config.inc.php after the update.';
    }

    /**
     * perform this migration
     */
    function up()
    {
        $db = DBManager::get();

        $db->exec("
            CREATE TABLE IF NOT EXISTS `sem_classes` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(64) NOT NULL,
                `compact_mode` tinyint(4) NOT NULL,
                `workgroup_mode` tinyint(4) NOT NULL,
                `only_inst_user` tinyint(4) NOT NULL,
                `turnus_default` int(11) NOT NULL,
                `default_read_level` int(11) NOT NULL,
                `default_write_level` int(11) NOT NULL,
                `bereiche` tinyint(4) NOT NULL,
                `show_browse` tinyint(4) NOT NULL,
                `write_access_nobody` tinyint(4) NOT NULL,
                `topic_create_autor` tinyint(4) NOT NULL,
                `visible` tinyint(4) NOT NULL,
                `course_creation_forbidden` tinyint(4) NOT NULL,
                `overview` varchar(64) DEFAULT NULL,
                `forum` varchar(64) DEFAULT NULL,
                `admin` varchar(64) DEFAULT NULL,
                `documents` varchar(64) DEFAULT NULL,
                `schedule` varchar(64) DEFAULT NULL,
                `participants` varchar(64) DEFAULT NULL,
                `literature` varchar(64) DEFAULT NULL,
                `scm` varchar(64) DEFAULT NULL,
                `wiki` varchar(64) DEFAULT NULL,
                `resources` varchar(64) DEFAULT NULL,
                `calendar` varchar(64) DEFAULT NULL,
                `elearning_interface` varchar(64) DEFAULT NULL,
                `modules` text NOT NULL,
                `description` text NOT NULL,
                `create_description` text NOT NULL,
                `studygroup_mode` tinyint(4) NOT NULL,
                `admission_prelim_default` tinyint(4) NOT NULL DEFAULT 0,
                `admission_type_default` tinyint(4) NOT NULL DEFAULT 0,
                `title_dozent` VARCHAR(64) NULL,
                `title_dozent_plural` VARCHAR(64) NULL,
                `title_tutor` VARCHAR(64) NULL,
                `title_tutor_plural` VARCHAR(64) NULL,
                `title_autor` VARCHAR(64) NULL,
                `title_autor_plural` VARCHAR(64) NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `name` (`name`)
            ) ENGINE=MyISAM
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS `sem_types` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(64) NOT NULL,
                `class` int(11) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM
        ");

        $statement = $db->prepare(
            "INSERT IGNORE INTO sem_classes " .
            "SET id = :id, " .
                "name = :name, " .
                "compact_mode = :compact_mode, " .
                "workgroup_mode = :workgroup_mode, " .
                "only_inst_user = :only_inst_user, " .
                "turnus_default = :turnus_default, " .
                "default_read_level = :default_read_level, " .
                "default_write_level = :default_write_level, " .
                "bereiche = :bereiche, " .
                "show_browse = :show_browse, " .
                "write_access_nobody = :write_access_nobody, " .
                "topic_create_autor = :topic_create_autor, " .
                "visible = :visible, " .
                "course_creation_forbidden = :course_creation_forbidden, " .
                "studygroup_mode = :studygroup_mode, " .
                "admission_prelim_default = :admission_prelim_default, " .
                "admission_type_default = :admission_type_default, " .
                "overview = :overview, " .
                "admin = :admin, " .
                "forum = :forum, " .
                "documents = :documents, " .
                "schedule = :schedule, " .
                "participants = :participants, " .
                "literature = :literature, " .
                "scm = :scm, " .
                "calendar = :calendar, " .
                "wiki = :wiki, " .
                "resources = :resources, " .
                "elearning_interface = :elearning_interface, " .
                "modules = :modules, " .
                "description = :description, " .
                "create_description = :create_description, " .
                "title_dozent = :title_dozent, " .
                "title_dozent_plural = :title_dozent_plural, " .
                "title_tutor = :title_tutor, " .
                "title_tutor_plural = :title_tutor_plural, " .
                "title_autor = :title_autor, " .
                "title_autor_plural = :title_autor_plural, " .
                "mkdate = UNIX_TIMESTAMP(), " .
                "chdate = UNIX_TIMESTAMP() " .
        "");

        $SEM_CLASS = $GLOBALS['SEM_CLASS_OLD_VAR'];
        $SEM_TYPE  = $GLOBALS['SEM_TYPE_OLD_VAR'];

        if (!(is_array($SEM_CLASS) && count($SEM_CLASS))) {
            throw new Exception('Migration 93 kann nicht durchgeführt werden,
                da die vorhandenen Einstellungen für $SEM_CLASS und $SEM_TYPE
                nicht aus der Datei config.inc.php ausgelesen werden können. Entfernen
                Sie diese Einstellungen erst, nachdem diese Migration durchgeführt wurde!');
        }

        $slots = [
            'forum'               => ['module' => 'CoreForum'],
            'documents'           => ['module' => 'CoreDocuments'],
            'schedule'            => ['module' => 'CoreSchedule'],
            'participants'        => ['module' => 'CoreParticipants'],
            'scm'                 => ['module' => 'CoreScm', 'config' => 'SCM_ENABLE'],
            'literature'          => ['module' => 'CoreLiterature', 'config' => 'LITERATURE_ENABLE'],
            'wiki'                => ['module' => 'CoreWiki', 'config' => 'WIKI_ENABLE'],
            'resources'           => ['module' => 'CoreResources', 'config' => 'RESOURCES_ENABLE'],
            'calendar'            => ['module' => 'CoreCalendar', 'config' => 'COURSE_CALENDAR_ENABLE'],
            'elearning_interface' => ['module' => 'CoreElearningInterface', 'config' => 'ELEARNING_INTERFACE_ENABLE']
        ];

        $studygroup_settings = $this->getStudygroupSettings();

        foreach ($SEM_CLASS as $id => $sem_class) {
            $modules = $settings = [];
            $modules['overview'] = 'CoreOverview';
            $settings['CoreOverview'] = ['activated' => 1, 'sticky' => 1];

            if ($sem_class['studygroup_mode']) {
                $modules['admin'] = 'CoreStudygroupAdmin';
                $settings['CoreStudygroupAdmin'] = ['activated' => 1, 'sticky' => 1];
                $modules['participants'] = 'CoreStudygroupParticipants';
                $settings['CoreStudygroupParticipants'] = ['activated' => 1, 'sticky' => 1];

                foreach ($studygroup_settings as $slot => $activated) {
                    if (isset($slots[$slot])) {
                        $core_module_name = $slots[$slot]['module'];
                        $core_module_config = $slots[$slot]['config'];

                        if ($activated && (!isset($core_module_config) || $GLOBALS[$core_module_config])) {
                            $modules[$slot] = $core_module_name;
                            $settings[$core_module_name] = [
                                'activated' => 0,
                                'sticky'    => 0
                            ];
                        }
                    } else if ($slot !== 'chat') {
                        $settings[$slot] = [
                            'activated' => 0,
                            'sticky'    => $activated ? 0 : 1
                        ];
                    }
                }
            } else {
                $modules['admin'] = 'CoreAdmin';
                $settings['CoreAdmin'] = ['activated' => 1, 'sticky' => 1];

                foreach ($slots as $slot => $core_module) {
                    $core_module_name = $core_module['module'];
                    $core_module_config = $core_module['config'];

                    if (!isset($core_module_config) || $GLOBALS[$core_module_config]) {
                        $modules[$slot] = $core_module_name;
                        $settings[$core_module_name] = [
                            'activated' => $sem_class[$slot] ? 1 : 0,
                            'sticky'    => 0
                        ];
                    }
                }
            }

            $title_dozent = $title_tutor = $title_autor = null;
            $title_dozent_plural = $title_tutor_plural = $title_autor_plural = null;
            foreach ($SEM_TYPE as $sem_type_id => $sem_type) {
                if ($sem_type['class'] == $id) {
                    $title_dozent || list($title_dozent, $title_dozent_plural) = $sem_type['title_dozent'];
                    $title_tutor || list($title_tutor, $title_tutor_plural) = $sem_type['title_tutor'];
                    $title_autor || list($title_autor, $title_autor_plural) = $sem_type['title_autor'];
                }
            }

            $success = $statement->execute([
                'id' => $id,
                'name' => $sem_class['name'],
                'compact_mode' => $sem_class['compact_mode'],
                'workgroup_mode' => $sem_class['workgroup_mode'],
                'only_inst_user' => $sem_class['only_inst_user'],
                'turnus_default' => $sem_class['turnus_default'],
                'default_read_level' => $sem_class['default_read_level'],
                'default_write_level' => $sem_class['default_write_level'],
                'bereiche' => $sem_class['bereiche'],
                'show_browse' => $sem_class['show_browse'],
                'write_access_nobody' => $sem_class['write_access_nobody'],
                'topic_create_autor' => $sem_class['topic_create_autor'],
                'visible' => $sem_class['visible'],
                'course_creation_forbidden' => $sem_class['course_creation_forbidden'],
                'overview' => $modules['overview'],
                'admin' => $modules['admin'],
                'forum' => $modules['forum'],
                'documents' => $modules['documents'],
                'schedule' => $modules['schedule'],
                'participants' => $modules['participants'],
                'literature' => $modules['literature'],
                'scm' => $modules['scm'],
                'wiki' => $modules['wiki'],
                'resources' => $modules['resources'],
                'calendar' => $modules['calendar'],
                'elearning_interface' => $modules['elearning_interface'],
                'modules' => json_encode($settings),
                'description' => $sem_class['description'],
                'create_description' => $sem_class['create_description'],
                'studygroup_mode' => $sem_class['studygroup_mode'],
                'admission_prelim_default' => (int)$sem_class['admission_prelim_default'],
                'admission_type_default' => (int)$sem_class['admission_type_default'],
                'title_dozent' => $title_dozent ? $title_dozent : null,
                'title_dozent_plural' => $title_dozent_plural ? $title_dozent_plural : null,
                'title_tutor' => $title_tutor ? $title_tutor : null,
                'title_tutor_plural' => $title_tutor_plural ? $title_tutor_plural : null,
                'title_autor' => $title_autor ? $title_autor : null,
                'title_autor_plural' => $title_autor_plural ? $title_autor_plural : null
            ]);
        }

        $statement = $db->prepare(
            "INSERT IGNORE INTO `sem_types` " .
            "SET id = :id, " .
                "name = :name, " .
                "class = :class, " .
                "chdate = UNIX_TIMESTAMP(), " .
                "mkdate = UNIX_TIMESTAMP()"
        );
        foreach ($SEM_TYPE as $id => $sem_type) {
            $success = $statement->execute([
                'id' => $id,
                'name' => $sem_type['name'],
                'class' => $sem_type['class']
            ]);
        }

        $statement = $db->prepare("DELETE FROM config WHERE field = 'STUDYGROUP_SETTINGS'");
        $statement->execute();
    }

    protected function getStudygroupSettings() {
        $studygroup_settings = [];
        foreach (words($GLOBALS['STUDYGROUP_SETTINGS']) as $key => $value) {
            $value = explode(':', $value);
            if ($value[0] != 'participants') {
                $studygroup_settings[$value[0]] = $value[1];
            }
        }
        return $studygroup_settings;
    }

    /**
     * revert this migration
     */
    function down()
    {
        DBManager::get()->exec("DROP TABLE `sem_classes` ");
        DBManager::get()->exec("DROP TABLE `sem_types` ");
    }

}