<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

/**
 * Class to define and manage attributes of seminar classes (or seminar categories).
 * Usually all sem-classes are stored in a global variable $SEM_CLASS which is
 * an array of SemClass objects.
 *
 * SemClass::getClasses() gets you all seminar classes in an array.
 *
 * You can access the attributes of a sem-class like an associative
 * array with $sem_class['default_read_level']. The uinderlying data is stored
 * in the database in the table sem_classes.
 *
 * If you want to have a name of a sem-class like "Lehre", please use
 * $sem_class['name'] and you will get a fully localized name and not the pure
 * database entry.
 *
 * This class manages also which modules are contained in which course-slots,
 * like "what module is used as a forum in my seminars". In the database stored
 * is the name of the module like "CoreForum" or a classname of a plugin or null
 * if the forum is completely disabled by root for this sem-class. Core-modules
 * can only be used within a standard slot. Plugins may also be used as optional
 * modules not contained in a slot.
 *
 * In the field 'modules' in the database is for each modules stored in a json-string
 * if the module is activatable by the teacher or not and if it is activated as
 * a default. Please use the methods SemClass::isSlotModule, SemClass::getSlotModule,
 * SemClass::isModuleAllowed, SemClass::isModuleMandatory, SemClass::isSlotMandatory
 * or even more simple SemClass::getNavigationForSlot (see documentation there).
 */
class SemClass implements ArrayAccess
{
    protected $data = [];
    static protected $slots = [
        "overview",
        "forum",
        "admin",
        "documents",
        "schedule",
        "participants",
        "literature",
        "scm",
        "wiki",
        "resources",
        "calendar",
        "elearning_interface"
    ];
    static protected $core_modules = [
        "CoreOverview",
        "CoreAdmin",
        "CoreStudygroupAdmin",
        "CoreStudygroupOverview",
        "CoreDocuments",
        "CoreParticipants",
        "CoreStudygroupParticipants",
        "CoreSchedule",
        "CoreLiterature",
        "CoreScm",
        "CoreWiki",
        "CoreResources",
        "CoreCalendar",
        "CoreElearningInterface"
    ];
    static protected $sem_classes = null;

    static public function getDefaultSemClass() {
        $data = [
            'name' => "Fehlerhafte Seminarklasse!",
            'overview' => "CoreOverview",
            'forum' => "CoreForum",
            'admin' => "CoreAdmin",
            'documents' => "CoreDocuments",
            'schedule' => "CoreSchedule",
            'participants' => "CoreParticipants",
            'literature' => "CoreLiterature",
            'scm' => "CoreScm",
            'wiki' => "CoreWiki",
            'resources' => "CoreResources",
            'calendar' => "CoreCalendar",
            'elearning_interface' => "CoreElearningInterface",
            'modules' => '{"CoreOverview":{"activated":1,"sticky":1},"CoreAdmin":{"activated":1,"sticky":1}, "CoreResources":{"activated":1,"sticky":0}}',
            'visible' => 1,
            'is_group' => false
        ];
        return new SemClass($data);
    }

    /**
     * Generates a dummy SemClass for institutes of this type (as defined in config.inc.php).
     * @param integer $type   institute type
     * @return SemClass
     */
    static public function getDefaultInstituteClass($type)
    {
        global $INST_MODULES;

        // fall back to 'default' if modules are not defined
        $type = isset($INST_MODULES[$type]) ? $type : 'default';

        $data = [
            'name'                => 'Generierte Standardinstitutsklasse',
            'visible'             => 1,
            'overview'            => 'CoreOverview', // always available
            'admin'               => 'CoreAdmin'     // always available
        ];
        $slots = [
            'forum'               => 'CoreForum',
            'documents'           => 'CoreDocuments',
            'literature'          => 'CoreLiterature',
            'scm'                 => 'CoreScm',
            'wiki'                => 'CoreWiki',
            'resources'           => 'CoreResources',
            'calendar'            => 'CoreCalendar',
            'elearning_interface' => 'CoreElearningInterface',
            'personal'            => 'personal'
        ];
        $modules = [
            'CoreOverview'        => ['activated' => 1, 'sticky' => 1],
            'CoreAdmin'           => ['activated' => 1, 'sticky' => 1]
        ];

        foreach ($slots as $slot => $module) {
            $data[$slot] = $module;
            $modules[$module] = ['activated' => (int) $INST_MODULES[$type][$slot], 'sticky' => 0];
        }
        $data['modules'] = json_encode($modules);

        return new SemClass($data);
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
     * Returns the number of seminars of this sem_class in Stud.IP
     * @return integer
     */
    public function countSeminars()
    {
        $db = DBManager::get();
        $sum = 0;
        foreach ($GLOBALS['SEM_TYPE'] as $sem_type) {
            if ($sem_type['class'] == $this->data['id']) {
                $sum += $sem_type->countSeminars();
            }
        }
        return $sum;
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
     * Defines a module for a slot and overwrites previous module.
     * @param string $slot
     * @param string $module (coremodule or classname of plugin)
     */
    public function setSlotModule($slot, $module)
    {
        if (in_array($slot, self::$slots)) {
            $this->data[$slot] = $module ? $module : null;
        }
    }

    /**
     * Returns the metadata of a module regarding this sem_class object.
     * @param string $modulename
     * @return array('sticky' => (bool), 'activated' => (bool))
     */
    public function getModuleMetadata($modulename)
    {
        return $this->data['modules'][$modulename];
    }

    /**
     * Sets the metadata for each module at once.
     * @param array $module_array: array($module_name => array('sticky' => (bool), 'activated' => (bool)), ...)
     */
    public function setModules($module_array)
    {
        $this->data['modules'] = $module_array;
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
            ||  $this->data['modules'][$modulename]['activated'];
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
            ||  $this->data['modules'][$modulename]['activated'];
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
     * @param string  $slot
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
     * Returns an array of navigation-objects. Those are for the tabs.
     * And yes, a slot can contain more than one tab, but usually contains
     * only one. The keys of the array are the names within the navigation-tree.
     * @param string $slot
     * @return array('navigation_name' => Navigation $nav, ...)
     */
    public function getNavigationForSlot($slot, $course_id = null)
    {
        $module = $this->getModule($slot);
        if ($module) {
            return (array) $module->getTabNavigation($course_id ? $course_id : Context::getId());
        } else {
            return [];
        }
    }

    public function getSemTypes()
    {
        $types = [];
        foreach (SemType::getTypes() as $id => $type) {
            if ($type['class'] == $this->data['id']) {
                $types[$id] = $type;
            }
        }
        return $types;
    }

    /**
     * Checks if the current sem class is usable for course grouping.
     */
    public function isGroup()
    {
        return $this->data['is_group'];
    }

    /**
     * Checks if any SemClasses exist that provide grouping functionality.
     * @return SimpleCollection
     */
    public static function getGroupClasses()
    {
        return SimpleCollection::createFromArray(self::getClasses())->findBy('is_group', true);
    }

    /**
     * stores all data in the database
     * @return boolean success
     */
    public function store()
    {
        $db = DBManager::get();
        $statement = $db->prepare(
            "UPDATE sem_classes " .
                "SET name = :name, " .
                "description = :description, " .
                "create_description = :create_description, " .
                "studygroup_mode = :studygroup_mode, " .
                "only_inst_user = :only_inst_user, " .
                "default_read_level = :default_read_level, " .
                "default_write_level = :default_write_level, " .
                "bereiche = :bereiche, " .
                "module = :module, " .
                "show_browse = :show_browse, " .
                "write_access_nobody = :write_access_nobody, " .
                "topic_create_autor = :topic_create_autor, " .
                "visible = :visible, " .
                "course_creation_forbidden = :course_creation_forbidden, " .
                "overview = :overview, " .
                "forum = :forum, " .
                "admin = :admin, " .
                "documents = :documents, " .
                "schedule = :schedule, " .
                "participants = :participants, " .
                "literature = :literature, " .
                "scm = :scm, " .
                "wiki = :wiki, " .
                "resources = :resources, " .
                "calendar = :calendar, " .
                "elearning_interface = :elearning_interface, " .
                "modules = :modules, " .
                "title_dozent = :title_dozent, " .
                "title_dozent_plural = :title_dozent_plural, " .
                "title_tutor = :title_tutor, " .
                "title_tutor_plural = :title_tutor_plural, " .
                "title_autor = :title_autor, " .
                "title_autor_plural = :title_autor_plural, " .
                "admission_prelim_default = :admission_prelim_default, " .
                "admission_type_default = :admission_type_default, " .
                "show_raumzeit = :show_raumzeit, " .
                "is_group = :is_group, " .
                "chdate = UNIX_TIMESTAMP() " .
            "WHERE id = :id ".
        "");
        StudipCacheFactory::getCache()->expire('DB_SEM_CLASSES_ARRAY');
        return $statement->execute([
            'id' => $this->data['id'],
            'name' => $this->data['name'],
            'description' => $this->data['description'],
            'create_description' => $this->data['create_description'],
            'studygroup_mode' => (int) $this->data['studygroup_mode'],
            'only_inst_user' => (int) $this->data['only_inst_user'],
            'default_read_level' => (int) $this->data['default_read_level'],
            'default_write_level' => (int) $this->data['default_write_level'],
            'bereiche' => (int) $this->data['bereiche'],
            'module' => (int) $this->data['module'],
            'show_browse' => (int) $this->data['show_browse'],
            'write_access_nobody' => (int) $this->data['write_access_nobody'],
            'topic_create_autor' => (int) $this->data['topic_create_autor'],
            'visible' => (int) $this->data['visible'],
            'course_creation_forbidden' => (int) $this->data['course_creation_forbidden'],
            'overview' => $this->data['overview'],
            'forum' => $this->data['forum'],
            'admin' => $this->data['admin'],
            'documents' => $this->data['documents'],
            'schedule' => $this->data['schedule'],
            'participants' => $this->data['participants'],
            'literature' => $this->data['literature'],
            'scm' => $this->data['scm'],
            'wiki' => $this->data['wiki'],
            'resources' => $this->data['resources'],
            'calendar' => $this->data['calendar'],
            'elearning_interface' => $this->data['elearning_interface'],
            'modules' => json_encode((object) $this->data['modules']),
            'title_dozent' => $this->data['title_dozent']
                ? $this->data['title_dozent']
                : null,
            'title_dozent_plural' => $this->data['title_dozent_plural']
                ? $this->data['title_dozent_plural']
                : null,
            'title_tutor' => $this->data['title_tutor']
                ? $this->data['title_tutor']
                : null,
            'title_tutor_plural' => $this->data['title_tutor_plural']
                ? $this->data['title_tutor_plural']
                : null,
            'title_autor' => $this->data['title_autor']
                ? $this->data['title_autor']
                : null,
            'title_autor_plural' => $this->data['title_autor_plural']
                ? $this->data['title_autor_plural']
                : null,
            'admission_prelim_default' => (int)$this->data['admission_prelim_default'],
            'admission_type_default' => (int)$this->data['admission_type_default'],
            'show_raumzeit' => (int) $this->data['show_raumzeit'],
            'is_group' => (int) $this->data['is_group']
        ]);
    }

    /**
     * Deletes the sem_class-object and all its sem_types. Will only delete,
     * if there are no seminars in this sem_class.
     * Remember to refresh the global $SEM_CLASS and $SEM_TYPE array.
     * @return boolean : success of deletion
     */
    public function delete()
    {
        if ($this->countSeminars() === 0) {
            foreach ($GLOBALS['SEM_TYPE'] as $sem_type) {
                if ($sem_type['class'] == $this->data['id']) {
                    $sem_type->delete();
                }
            }
            $GLOBALS['SEM_TYPE'] = SemType::getTypes();
            $db = DBManager::get();
            $statement = $db->prepare("
                DELETE FROM sem_classes 
                WHERE id = :id 
            ");
            StudipCacheFactory::getCache()->expire('DB_SEM_CLASSES_ARRAY');
            return $statement->execute([
                'id' => $this->data['id']
            ]);
        } else {
            return false;
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
     * @param integer $offset: name of attribute
     * @return boolean|(localized)string
     */
    public function offsetGet($offset)
    {
        switch ($offset) {
            case "name":
                return gettext($this->data['name']);
            case "only_inst_user":
                return (bool) $this->data['only_inst_user'];
            case "bereiche":
                return (bool) $this->data['bereiche'];
            case "show_browse":
                return (bool) $this->data['show_browse'];
            case "write_access_nobody":
                return (bool) $this->data['write_access_nobody'];
            case "topic_create_autor":
                return (bool) $this->data['topic_create_autor'];
            case "visible":
                return (bool) $this->data['visible'];
            case "forum":
                return $this->data['forum'] !== null;
            case "documents":
                return $this->data['documents'] !== null;
            case "schedule":
                return $this->data['schedule'] !== null;
            case "participants":
                return $this->data['participants'] !== null;
            case "literature":
                return $this->data['literature'] !== null;
            case "scm":
                return $this->data['scm'] !== null;
            case "studygroup_mode":
                return (bool) $this->data['studygroup_mode'];
            case "admission_prelim_default":
               return (int) $this->data['admission_prelim_default'];
            case "admission_type_default":
               return (int) $this->data['admission_type_default'];
            case "is_group":
               return (bool) $this->data['is_group'];
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
    static public function getClasses()
    {
        if (!is_array(self::$sem_classes)) {
            $db = DBManager::get();
            self::$sem_classes = [];

            $cache = StudipCacheFactory::getCache();
            $class_array = unserialize($cache->read('DB_SEM_CLASSES_ARRAY'));
            if (!$class_array) {

                try {
                    $statement = $db->prepare(
                        "SELECT * FROM sem_classes ORDER BY id ASC "
                    );
                    $statement->execute();
                    $class_array = $statement->fetchAll(PDO::FETCH_ASSOC);

                    if ($class_array) {
                        $cache = StudipCacheFactory::getCache();
                        $cache->write('DB_SEM_CLASSES_ARRAY', serialize($class_array));
                    }
                } catch (PDOException $e) {
                    //for use without or before migration 92
                    $class_array = $GLOBALS['SEM_CLASS_OLD_VAR'];
                    if (is_array($class_array)) {
                        ksort($class_array);
                        foreach ($class_array as $id => $class) {
                            self::$sem_classes[$id] = new SemClass($class);
                        }
                    } else {
                        self::$sem_classes[1] = self::getDefaultSemClass();
                    }
                }
            }
            foreach ($class_array as $sem_class) {
                self::$sem_classes[$sem_class['id']] = new SemClass($sem_class);
            }
        }
        return self::$sem_classes;
    }

    /**
     * Refreshes the internal $sem_classes cache-variable.
     * @return array of SemClass
     */
    static public function refreshClasses()
    {
        self::$sem_classes = null;
        return self::getClasses();
    }

    /**
     * Static method to recursively transform an object into an associative array.
     * @param mixed $obj: should be of class StdClass
     * @return array
     */
    static public function object2array($obj)
    {
        $arr_raw = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($arr_raw as $key => $val) {
            $val = (is_array($val) || is_object($val)) ? self::object2array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }

    /**
     * Returns an array of all slot-names.
     * @return array of strings
     */
    static public function getSlots() {
        return self::$slots;
    }

    /**
     * Static method only to keep the translationstrings of the values. It is
     * never used within the system.
     */
    static private function localization()
    {
        _("Lehre");
        _("Forschung");
        _("Organisation");
        _("Community");
        _("Arbeitsgruppen");
        _("importierte Kurse");
        _("Hauptveranstaltungen");

        _("Hier finden Sie alle in Stud.IP registrierten Lehrveranstaltungen");
        _("Verwenden Sie diese Kategorie, um normale Lehrveranstaltungen anzulegen");
        _("Hier finden Sie virtuelle Veranstaltungen zum Thema Forschung an der Universität");
        _("In dieser Kategorie können Sie virtuelle Veranstaltungen für Forschungsprojekte anlegen.");
        _("Hier finden Sie virtuelle Veranstaltungen zu verschiedenen Gremien an der Universität");
        _("Um virtuelle Veranstaltungen für Uni-Gremien anzulegen, verwenden Sie diese Kategorie");
        _("Hier finden Sie virtuelle Veranstaltungen zu unterschiedlichen Themen");
        _("Wenn Sie Veranstaltungen als Diskussiongruppen zu unterschiedlichen Themen anlegen möchten, verwenden Sie diese Kategorie.");
        _("Hier finden Sie verschiedene Arbeitsgruppen an der %s");
        _("Verwenden Sie diese Kategorie, um unterschiedliche Arbeitsgruppen anzulegen.");
        _("Veranstaltungen dieser Kategorie dienen als Gruppierungselement, um die Zusammengehörigkeit von Veranstaltungen anderer Kategorien abzubilden.");
    }

}
