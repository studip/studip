<?php

class Step00349 extends Migration
{
    private $registered_modules = [
        'overview'            => ['id' => 20, 'const' => '', 'sem' => true, 'inst' => false],
        'admin'               => ['id' => 17, 'const' => '', 'sem' => true, 'inst' => false],
        'forum'               => ['id' => 0, 'const' => '', 'sem' => true, 'inst' => true],
        'documents'           => ['id' => 1, 'const' => '', 'sem' => true, 'inst' => true],
        'schedule'            => ['id' => 2, 'const' => '', 'sem' => true, 'inst' => false],
        'participants'        => ['id' => 3, 'const' => '', 'sem' => true, 'inst' => false],
        'personal'            => ['id' => 4, 'const' => '', 'sem' => false, 'inst' => true],
        'wiki'                => ['id' => 8, 'const' => 'WIKI_ENABLE', 'sem' => true, 'inst' => true],
        'scm'                 => ['id' => 12, 'const' => 'SCM_ENABLE', 'sem' => true, 'inst' => true],
        'elearning_interface' => ['id' => 13, 'const' => 'ELEARNING_INTERFACE_ENABLE', 'sem' => true, 'inst' => true],
        'calendar'            => ['id' => 16, 'const' => 'COURSE_CALENDAR_ENABLE', 'sem' => true, 'inst' => true],
    ];
    private $notification_modules = [
        'basicdata'           => 27,
        'votes'               => 26,
        'news'                => 25,
        'forum'               => 0,
        'documents'           => 1,
        'schedule'            => 2,
        'participants'        => 3,
        'wiki'                => 8,
        'scm'                 => 12,
        'elearning_interface' => 13
    ];

    public function description()
    {
        return 'add table `tools_activated`; migrate data from plugins_activated and seminare.modules; add CorePlugins';
    }

    public function up()
    {
        $db = DBManager::get();
        $db->execute("CREATE TABLE IF NOT EXISTS `tools_activated` (
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_type` enum('course','institute') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `plugin_id` int(10) UNSIGNED NOT NULL,
  `position` tinyint(3) UNSIGNED NOT NULL,
  `metadata` json DEFAULT NULL,
  `mkdate` int(10) UNSIGNED NOT NULL,
  `chdate` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`range_id`,`plugin_id`),
  KEY (`plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $db->execute("CREATE TABLE IF NOT EXISTS `seminar_user_notifications` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `notification_data` json DEFAULT NULL,
  `chdate` int(10) UNSIGNED NOT NULL,
  `mkdate` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`,`seminar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $core_plugins = [
            'IliasInterfaceModule',
            'LtiToolModule',
            'GradebookModule',
            'FeedbackModule',
            'ConsultationModule',
            'CoreForum',
            'Blubber',
            'CoursewareModule'
        ];
        $studip_modules = [
            'CoreOverview',
            'CoreAdmin',
            'CoreStudygroupAdmin',
        //???    'CoreStudygroupOverview',
            'CoreDocuments',
            'CoreParticipants',
            'CoreStudygroupParticipants',
            'CoreSchedule',
            'CoreScm',
            'CoreWiki',
            'CoreCalendar',
            'CoreElearningInterface',
            'CorePersonal'
        ];
        $ouv_mapping = [
            'sem' => 0,
            'inst'=> 0,
            'basicdata' => 0,
            'vote' => -1,
            'eval' => -2,
            'news' => 'CoreOverview',
            'documents' => 'CoreDocuments',
            'schedule' => 'CoreSchedule',
            'scm' =>  'CoreScm',
            'wiki' => 'CoreWiki',
            'elearning_interface' => 'CoreElearningInterface',
            'ilias_interface' => 'IliasInterfaceModule',
            'participants' => 'CoreParticipants',
            'courseware' => 'CoursewareModule'
        ];
        PluginManager::getInstance()->getPlugin('CoreForum');
        PluginManager::getInstance()->getPlugin('Blubber');

        foreach ($core_plugins as $plugin) {
            try {
                $info = new ReflectionClass($plugin);
            } catch (ReflectionException $e) {
                continue;
            }
            $ifaces = array_merge(['CorePlugin'], $info->getInterfaceNames());
            $db->execute("UPDATE plugins SET plugintype=? WHERE pluginclassname=?", [join(',', $ifaces), $plugin]);
        }
        foreach ($studip_modules as $module) {
            $info = new ReflectionClass($module);
            $ifaces = array_merge(['CorePlugin'], $info->getInterfaceNames());
            $db->execute("INSERT INTO plugins (pluginclassname, pluginname, plugintype, enabled, navigationpos)
                VALUES (?, ?, ?, 'yes', 1)", [$module, $module, join(',', $ifaces)]);
            $db->execute("INSERT INTO roles_plugins (roleid, pluginid) SELECT roleid, ? FROM roles WHERE `system` = 'y'", [$db->lastInsertId()]);
        }

        $all_plugins = $db->fetchPairs("SELECT pluginclassname, pluginid FROM plugins");

        foreach ($db->query("SELECT seminar_id, status, modules FROM seminare") as $row) {
            $activated_plugins = $db->fetchPairs("SELECT plugins_activated.pluginid, state FROM `plugins_activated` INNER JOIN `plugins` USING(pluginid) WHERE range_id=? AND range_type='sem' ORDER BY navigationpos", [$row['seminar_id']]);
            $modules = $this->getLocalModules('sem', $row['modules'], $row['status']);
            $pos = 0;
            foreach ($modules as $pos => $module) {
                if (isset($all_plugins[$module]) && !(isset($activated_plugins[$all_plugins[$module]]) && $activated_plugins[$all_plugins[$module]] === '0')) {
                    $db->execute("INSERT IGNORE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES (?, 'course', ?, ?, NULL, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())",
                        [
                            $row['seminar_id'],
                            $all_plugins[$module],
                            $pos
                        ]);
                }
            }
            foreach ($activated_plugins as $plugin_id => $state) {
                if (!$state) {
                    continue;
                }
                $db->execute("INSERT IGNORE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES (?, 'course', ?, ?, NULL, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())",
                    [
                        $row['seminar_id'],
                        $plugin_id,
                        ++$pos
                    ]);
            }
        }

        foreach ($db->query("SELECT institut_id, type, modules FROM Institute") as $row) {
            $activated_plugins = $db->fetchPairs("SELECT plugins_activated.pluginid, state FROM `plugins_activated` INNER JOIN `plugins` USING(pluginid) WHERE range_id=? AND range_type='inst' ORDER BY navigationpos", [$row['institut_id']]);
            $modules = $this->getLocalModules('inst', $row['modules'], $row['type']);
            $pos = 0;
            foreach ($modules as $pos => $module) {
                if (isset($all_plugins[$module]) && !(isset($activated_plugins[$all_plugins[$module]]) && $activated_plugins[$all_plugins[$module]] === '0')) {
                    $db->execute("INSERT IGNORE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES (?, 'institute', ?, ?, NULL, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())",
                        [
                            $row['institut_id'],
                            $all_plugins[$module],
                            $pos
                        ]);
                }
            }
            foreach ($activated_plugins as $plugin_id => $state) {
                if (!$state) {
                    continue;
                }
                $db->execute("INSERT IGNORE INTO `tools_activated` (`range_id`, `range_type`, `plugin_id`, `position`, `metadata`, `mkdate`, `chdate`) VALUES (?, 'institute', ?, ?, NULL, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())",
                    [
                        $row['institut_id'],
                        $plugin_id,
                        ++$pos
                    ]);
            }
        }

        foreach ($db->query("SELECT seminar_id, user_id, notification FROM seminar_user WHERE notification > 0") as $row) {
            $notifications = [];
            foreach ($this->notification_modules as $module => $id) {
                if ($row['notification'] & pow(2, $id)) {
                    $plugin_id = $ouv_mapping[$module];
                    if (is_string($plugin_id)) {
                        $plugin_id = $all_plugins[$plugin_id];
                    }
                    if ($plugin_id !== null) {
                        $notifications[] = $plugin_id;
                    }
                }
            }
            if (count($notifications)) {
                $db->execute("INSERT INTO seminar_user_notifications (user_id, seminar_id, notification_data, chdate, mkdate) VALUES (?,?,?,UNIX_TIMESTAMP(), UNIX_TIMESTAMP())", [$row['user_id'], $row['seminar_id'], json_encode($notifications)]);
            }
        }

        foreach ($db->query("SELECT range_id, user_id, notification FROM deputies WHERE notification > 0") as $row) {
            $notifications = [];
            foreach ($this->notification_modules as $module => $id) {
                if ($row['notification'] & pow(2, $id)) {
                    $plugin_id = $ouv_mapping[$module];
                    if (!is_int($plugin_id)) {
                        $plugin_id = $all_plugins[$module];
                    }
                    if ($plugin_id !== null) {
                        $notifications[] = $plugin_id;
                    }
                }
            }
            if (count($notifications)) {
                $db->execute("INSERT IGNORE INTO seminar_user_notifications (user_id, seminar_id, notification_data, chdate, mkdate) VALUES (?,?,?,UNIX_TIMESTAMP(), UNIX_TIMESTAMP())", [$row['user_id'], $row['range_id'], json_encode($notifications)]);
            }
        }

        $db->exec("DELETE FROM `object_user_visits` WHERE `type` IN ('literature', 'forum')");
        $db->exec("ALTER TABLE `object_user_visits` ADD `plugin_id` INT NOT NULL AFTER `type`");
        foreach ($ouv_mapping as $type => $plugin_id) {
            if (is_string($plugin_id)) {
                $plugin_id = $all_plugins[$plugin_id];
            }
            $db->execute("UPDATE `object_user_visits` SET `plugin_id` = ? WHERE `type` = ?", [$plugin_id, $type]);
        }

        $db->exec("ALTER TABLE `object_user_visits` DROP PRIMARY KEY, ADD PRIMARY KEY (`object_id`, `user_id`, `plugin_id`)");
        $db->exec("ALTER TABLE `object_user_visits` DROP `type`");
        $db->exec("ALTER TABLE `seminar_user` DROP `notification`");
        $db->exec("ALTER TABLE `seminare` DROP `modules`");
        $db->exec("ALTER TABLE `Institute` DROP `modules`");
        $db->exec("ALTER TABLE `sem_classes`
                      DROP `overview`,
                      DROP `forum`,
                      DROP `admin`,
                      DROP `documents`,
                      DROP `schedule`,
                      DROP `participants`,
                      DROP `literature`,
                      DROP `scm`,
                      DROP `wiki`,
                      DROP `resources`,
                      DROP `calendar`,
                      DROP `elearning_interface`");

    }

    private function getLocalModules($range_type, $modules, $type)
    {
        $old_sem_class = OldSemClass::getClasses();
        if ($range_type === 'sem') {
            $class = DBManager::get()->fetchColumn("SELECT class FROM sem_types WHERE id=?", [$type]);
            $sem_class = $old_sem_class[$class] ?: OldSemClass::getDefaultSemClass();
        } else {
            $sem_class = OldSemClass::getDefaultInstituteClass($type);
        }
        if (!$modules) {
            $modules = 0;
            foreach ($this->registered_modules as $slot => $val) {
                if ($val[$range_type === 'sem' ? 'sem' : 'inst']) {
                    $const = $val['const'];
                    if ($sem_class->isModuleActivated($sem_class->getSlotModule($slot)) && (!$const || Config::get()->$const)) {
                        $modules += pow(2, $val['id']);
                    }
                }
            }
        }
        $modules_list = [];
        $pos = 0;
        foreach ($this->registered_modules as $key => $val) {
            $module = $sem_class->getSlotModule($key);
            if ($sem_class->isModuleAllowed($module)) {
                if (($modules & pow(2, $val['id'])) || $sem_class->isSlotMandatory($key)) {
                    $modules_list[$pos] = $module;
                    $pos++;
                }
            }
        }
        return $modules_list;
    }

    public function down()
    {

    }
}


class OldSemClass implements ArrayAccess
{
    protected $data = [];
    protected static $slots = [
        "overview",
        "forum",
        "admin",
        "documents",
        "schedule",
        "participants",
        "scm",
        "wiki",
        "calendar",
        "elearning_interface"
    ];
    protected static $core_modules = [
        "CoreOverview",
        "CoreAdmin",
        "CoreStudygroupAdmin",
        "CoreStudygroupOverview",
        "CoreDocuments",
        "CoreParticipants",
        "CoreStudygroupParticipants",
        "CoreSchedule",
        "CoreScm",
        "CoreWiki",
        "CoreCalendar",
        "CoreElearningInterface"
    ];
    protected static $sem_classes = null;

    public static function getDefaultSemClass()
    {
        $data = [
            'name'                => "Fehlerhafte Seminarklasse!",
            'overview'            => "CoreOverview",
            'forum'               => "Blubber",
            'admin'               => "CoreAdmin",
            'documents'           => "CoreDocuments",
            'schedule'            => "CoreSchedule",
            'participants'        => "CoreParticipants",
            'scm'                 => "CoreScm",
            'wiki'                => "CoreWiki",
            'calendar'            => "CoreCalendar",
            'elearning_interface' => "CoreElearningInterface",
            'modules'             => '{"CoreOverview":{"activated":1,"sticky":1},"CoreAdmin":{"activated":1,"sticky":1}, "CoreResources":{"activated":1,"sticky":0}}',
            'visible'             => 1,
            'is_group'            => false
        ];
        return new self($data);
    }

    /**
     * Generates a dummy SemClass for institutes of this type (as defined in config.inc.php).
     * @param integer $type institute type
     * @return SemClass
     */
    public static function getDefaultInstituteClass($type)
    {
        global $INST_MODULES;

        // fall back to 'default' if modules are not defined
        $type = isset($INST_MODULES[$type]) ? $type : 'default';

        $data = [
            'name'     => 'Generierte Standardinstitutsklasse',
            'visible'  => 1,
            'overview' => 'CoreOverview', // always available
            'admin'    => 'CoreAdmin'     // always available
        ];
        $slots = [
            'forum'               => 'Blubber',
            'documents'           => 'CoreDocuments',
            'scm'                 => 'CoreScm',
            'wiki'                => 'CoreWiki',
            'calendar'            => 'CoreCalendar',
            'elearning_interface' => 'CoreElearningInterface',
            'personal'            => 'CorePersonal'
        ];
        $modules = [
            'CoreOverview' => ['activated' => 1, 'sticky' => 1],
            'CoreAdmin'    => ['activated' => 1, 'sticky' => 1]
        ];

        foreach ($slots as $slot => $module) {
            $data[$slot] = $module;
            $modules[$module] = ['activated' => (int)$INST_MODULES[$type][$slot], 'sticky' => 0];
        }
        $data['modules'] = json_encode($modules);

        return new self($data);
    }

    /**
     * Constructor can be set with integer of sem_class_id or an array of
     * the old $SEM_CLASS style.
     * @param integer | array $data
     */
    public function __construct($data)
    {
        $db = DBManager::get();
        if (is_int($data)) {
            $statement = $db->prepare("SELECT * FROM sem_classes WHERE id = :id ");
            $statement->execute(['id' => $data]);
            $this->data = $statement->fetch(PDO::FETCH_ASSOC);
        } else {
            $this->data = $data;
        }
        if ($this->data['modules']) {
            $this->data['modules'] = self::object2array(json_decode($this->data['modules']));
        } else {
            $this->data['modules'] = [];
        }
    }

    /**
     * Returns the name of the module of the slot or the module itself, if it
     * is a plugin.
     * @param string $slot
     * @return string
     */
    public function getSlotModule($slot)
    {
        if (in_array($slot, self::$slots)) {
            return $this->data[$slot];
        } else {
            return $slot;
        }
    }


    /**
     * Returns all metadata of the modules at once.
     * @return array: array($module_name => array('sticky' => (bool), 'activated' => (bool)), ...)
     */
    public function getModules()
    {
        return $this->data['modules'];
    }

    /**
     * Returns true if a module is activated on default for this sem_class.
     * @param string $modulename
     * @return boolean
     */
    public function isModuleActivated($modulename)
    {
        return !$this->data['modules'][$modulename]
            || $this->data['modules'][$modulename]['activated'];
    }

    /**
     * Returns if a module is allowed to be displayed for this sem_class.
     * @param string $modulename
     * @return boolean
     */
    public function isModuleAllowed($modulename)
    {
        return !$this->data['modules'][$modulename]
            || !$this->data['modules'][$modulename]['sticky']
            || $this->data['modules'][$modulename]['activated'];
    }

    /**
     * Returns if a module is mandatory for this sem_class.
     * @param string $module
     * @return boolean
     */
    public function isModuleMandatory($module)
    {
        return $this->data['modules'][$module]['sticky']
            && $this->data['modules'][$module]['activated'];
    }

    /**
     * Returns if the slot is mandatory, which it is if the module in this
     * slot is mandatory.
     * @param string $slot
     * @return boolean
     */
    public function isSlotMandatory($slot)
    {
        $module = $this->getSlotModule($slot);
        return $module && $this->isModuleMandatory($module);
    }

    /**
     * Returns if a module is a slot module. Good for plugins that should be
     * displayed on a specific place only if they are no slot modules.
     * @param string $module
     * @return boolean
     */
    public function isSlotModule($module)
    {
        foreach (self::$slots as $slot) {
            if ($module === $this->getSlotModule($slot)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the slot name of a module.
     * @param string $module
     * @return string|null
     */
    public function getModuleSlot($module)
    {
        foreach (self::$slots as $slot) {
            if ($module === $this->getSlotModule($slot)) {
                return $slot;
            }
        }
        return null;
    }

    /**
     * returns an instance of the module of a given slotname or pluginclassname
     * @param string $slot_or_plugin
     * @return StudipModule | null
     */
    public function getModule($slot_or_plugin)
    {
        $module = $this->getSlotModule($slot_or_plugin);
        if ($module && $this->isModuleAllowed($module)) {
            if (in_array($module, self::$core_modules)) {
                return new $module();
            }
            if ($module) {
                return PluginEngine::getPlugin($module);
            }
        }
    }


    /**
     * Sets an attribute of sem_class->data
     * @param string $offset
     * @param mixed $value
     */
    public function set($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /***************************************************************************
     *                          ArrayAccess methods                            *
     ***************************************************************************/

    /**
     * deprecated, does nothing, should not be used
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Compatibility function with old $SEM_CLASS variable for plugins. Maps the
     * new array-structure to the old boolean values.
     * @param integer $offset : name of attribute
     * @return boolean|(localized)string
     */
    public function offsetGet($offset)
    {
        switch ($offset) {
            case "name":
                return gettext($this->data['name']);
            case "only_inst_user":
                return (bool)$this->data['only_inst_user'];
            case "bereiche":
                return (bool)$this->data['bereiche'];
            case "show_browse":
                return (bool)$this->data['show_browse'];
            case "write_access_nobody":
                return (bool)$this->data['write_access_nobody'];
            case "topic_create_autor":
                return (bool)$this->data['topic_create_autor'];
            case "visible":
                return (bool)$this->data['visible'];
            case "forum":
                return $this->data['forum'] !== null;
            case "documents":
                return $this->data['documents'] !== null;
            case "schedule":
                return $this->data['schedule'] !== null;
            case "participants":
                return $this->data['participants'] !== null;
            case "scm":
                return $this->data['scm'] !== null;
            case "studygroup_mode":
                return (bool)$this->data['studygroup_mode'];
            case "admission_prelim_default":
                return (int)$this->data['admission_prelim_default'];
            case "admission_type_default":
                return (int)$this->data['admission_type_default'];
            case "is_group":
                return (bool)$this->data['is_group'];
        }
        //ansonsten
        return $this->data[$offset];
    }

    /**
     * ArrayAccess method to check if an attribute exists.
     * @param type $offset
     * @return type
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * deprecated, does nothing, should not be used
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
    }

    /***************************************************************************
     *                            static methods                               *
     ***************************************************************************/

    /**
     * Returns an array of all SemClasses in Stud.IP. Equivalent to global
     * $SEM_CLASS variable. This variable is statically stored in this class.
     * @return array of SemClass
     */
    public static function getClasses()
    {
        if (!is_array(self::$sem_classes)) {
            $db = DBManager::get();
            self::$sem_classes = [];

            $statement = $db->prepare(
                "SELECT * FROM sem_classes ORDER BY id ASC "
            );
            $statement->execute();
            $class_array = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($class_array as $sem_class) {
                self::$sem_classes[$sem_class['id']] = new self($sem_class);
            }
        }
        return self::$sem_classes;
    }


    /**
     * Static method to recursively transform an object into an associative array.
     * @param mixed $obj : should be of class StdClass
     * @return array
     */
    public static function object2array($obj)
    {
        $arr_raw = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($arr_raw as $key => $val) {
            $val = (is_array($val) || is_object($val)) ? self::object2array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }
}

