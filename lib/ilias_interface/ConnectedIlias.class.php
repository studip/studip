<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
* main-class for connected systems
*
* This class contains the main methods of the ilias-interface to connect ILIAS installations.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  ilias_interface_modules
* @module       ConnectedIlias
* @package  ILIAS-Interface
*/
class ConnectedIlias
{
    const CRS_NOTIFICATION= '1';
    const CRS_NO_NOTIFICATION= '2';
    const CRS_ADMIN_ROLE= '1';
    const CRS_MEMBER_ROLE= '2';
    const CRS_TUTOR_ROLE= '3';
    const CRS_PASSED_VALUE= '0';

    const OPERATION_VISIBLE= 'visible';
    const OPERATION_READ= 'read';
    const OPERATION_WRITE= 'write';
    const OPERATION_COPY= 'copy';
    const OPERATION_DELETE= 'delete';

    public $index;
    public $ilias_config;
    public $ilias_interface_config;
    public $ilias_int_version;
    public $global_roles;
    public $crs_roles;
    public $error;

    public $soap_client;
    public $course_modules;
    public $user;
    public $user_modules;

    /**
    * constructor
    *
    * ILIAS connection main class
    * @access
    * @param string $index ilias installation index
    */
    public function __construct($index)
    {
        // load settings
        $this->index = $index;
        $this->error = [];
        $this->global_roles = [4,5,14];
        $this->loadSettings();
        $this->crs_roles = [
                        "autor" => "member",
                        "tutor" => "tutor",
                        "dozent" => "admin",
                        "admin" => "admin",
                        "root" => "admin"
        ];
        $this->user_operations = [self::OPERATION_VISIBLE, self::OPERATION_READ];
        $this->operations = [];
        $this->course_modules = [];
        $this->user_modules = [];

        // set ILIAS version as integer value
        $this->ilias_int_version = $this->getIntVersion($this->ilias_config['version']);

        // init soap client
        $this->soap_client = new IliasSoap($this->index, $this->ilias_config['url'].'/webservice/soap/server.php?wsdl', $this->ilias_config['client'], $this->ilias_int_version, $this->ilias_config['admin'], $this->ilias_config['admin_pw']);
        $this->soap_client->setCachingStatus($this->ilias_interface_config['cache']);

        // init current user (only if ILIAS installation is active)
        if ($this->ilias_config['is_active']) {
            $this->user = new IliasUser($this->index, $this->ilias_config['version']);
            // create account automatically if it doesn't exist
            if (! $this->user->isConnected()) {
                $this->soap_client->setCachingStatus(false);
                $this->soap_client->clearCache();
                $this->newUser();
            } else {
                NotificationCenter::addObserver($this, "updateUser", "UserDidUpdate");
            }
            // create user category if user has ILIAS author permission
            if ($GLOBALS['perm']->have_perm($this->ilias_config['author_perm']) && ! $this->ilias_config['category_create_on_add_module'] && ! $this->user->getCategory()) {
                $this->soap_client->setCachingStatus(false);
                $this->soap_client->clearCache();
                $this->newUserCategory();
            }
        }
    }

    /**
     * get ILIAS version as int
     *
     * converts ILIAS version to int value
     * @access public
     * @return string messages
     */
    public static function getIntVersion($version)
    {
        $version_array = explode('.', $version);
        return ((int)$version_array[0]*10000) + ((int)$version_array[1]*100) + ((int)$version_array[2]);
    }

    /**
     * load settings
     *
     * load ILIAS settings from config table
     * @access public
     * @return string messages
     */
    public function loadSettings()
    {
        $this->ilias_interface_config = Config::get()->ILIAS_INTERFACE_BASIC_SETTINGS;

        $ilias_configs = Config::get()->ILIAS_INTERFACE_SETTINGS;
        $this->ilias_config = $ilias_configs[$this->index];
    }

    /**
     * store settings
     *
     * stores current ILIAS settings to config table.
     * @access public
     */
    public function storeSettings()
    {
        $ilias_configs = Config::get()->ILIAS_INTERFACE_SETTINGS;
        $ilias_configs[$this->index] = $this->ilias_config;
        Config::get()->store('ILIAS_INTERFACE_SETTINGS', $ilias_configs);
    }


    /**
     * get ILIAS info
     *
     * checks ILIAS base settings
     * @access public
     * @param string $url
     * @return array info
     */
    public static function getIliasInfo($url)
    {
        $info = [];
        // check if url exists
        $check = @get_headers($url . 'login.php');
        if (strpos($check[0], '200') === false) {
            return $info;
        } else {
            $info['url'] = $url;
        }
        $soap_client = new IliasSoap('new', $url.'/webservice/soap/server.php?wsdl');
        $soap_client->setCachingStatus(false);
        if ($client_info = $soap_client->getInstallationInfoXML()) {
            $info = array_merge($info, $client_info);
        }
        return $info;
    }

    /**
     * get soap methods
     *
     * returns array of available soap methods
     * @access public
     * @return array soap method names
     */
    public function getSoapMethods()
    {
        // fetch all available SOAP methods
        $soap_methods = [];
        if (is_callable([$this->soap_client->soap_client, '__getfunctions'])) {
            $soap_methods_raw = $this->soap_client->soap_client->__getfunctions();
            foreach ($soap_methods_raw as $method) {
                $method_array = explode(' ', $method);
                preg_match_all('/\${1}[^, )]*/', $method, $param_array);
                preg_match('/[^(]*/', $method_array[1], $method_name);
                $soap_methods[$method_name[0]] = [];
                foreach ($param_array[0] as $par) {
                    $soap_methods[$method_name[0]][] = substr($par, 1);
                }
            }
        } else {
            $proxy = $this->soap_client->soap_client->getProxyClassCode();
            preg_match_all('/function{1}[^{]*/', $proxy, $soap_methods_raw);
            foreach ($soap_methods_raw[0] as $method) {
                $method_array = explode(' ', $method);
                preg_match_all('/\${1}[^, )]*/', $method, $param_array);
                preg_match('/[^(]*/', $method_array[1], $method_name);
                $soap_methods[$method_name[0]] = [];
                foreach ($param_array[0] as $par) {
                    $soap_methods[$method_name[0]][] = substr($par, 1);;
                }
            }
        }
        return $soap_methods;
    }

    /**
     * get connection status
     *
     * checks connection settings
     * @access public
     * @return string messages
     */
    public function getConnectionSettingsStatus()
    {
        // check ILIAS version
        if (($this->ilias_int_version < 30000) || ($this->ilias_int_version > 80000)) {
            $this->error[] = _('Die ILIAS-Version ist ungültig.');
            return false;
        }

        // check if url exists
        $check = @get_headers($this->ilias_config['url'] . 'login.php');
        if (strpos($check[0], '200') === false) {
            $this->error[] = sprintf(_('Die URL "%s" ist nicht erreichbar.'), $this->ilias_config['url']);
            return false;
        }

        // check soap connection
        $res = $this->soap_client->loginAdmin();
        if (!$res) {
            $this->error[] = sprintf(_('Anmelden mit dem Account "%s" in der %s-Installation ist fehlgeschlagen.'), $this->ilias_config['admin'], $this->ilias_config['name']);
            return false;
        }

        return true;
    }

    /**
     * get content status
     *
     * checks content settings
     * @access public
     * @return string messages
     */
    public function getContentSettingsStatus()
    {
        if (!$this->ilias_config['root_category']) {
            // check category
            if (!$this->ilias_config['root_category_name']) {
                $this->error[] = _("Die ILIAS-Kategorie für Stud.IP-Inhalte wurde noch nicht festgelegt.");
                return false;
            }
            $category = $this->soap_client->getReferenceByTitle($this->ilias_config['root_category_name'], 'cat');
            if (!$category) {
                $this->error[] = sprintf(_("Die Kategorie \"%s\" wurde nicht gefunden."), $this->ilias_config['root_category_name']);
                return false;
            }
            if ($category) {
                $this->ilias_config['root_category'] = $category;

                // check user data category
                if (! $this->ilias_config['user_data_category']) {
                    $object_data["title"] = sprintf(_("User-Daten"));
                    $object_data["description"] = _("Hier befinden sich die persönlichen Ordner der Stud.IP-User.");
                    $object_data["type"] = "cat";
                    $object_data["owner"] = $this->soap_client->lookupUser($this->ilias_config['admin']);
                    $user_cat = $this->soap_client->addObject($object_data, $this->ilias_config['root_category']);
                    if ($user_cat != false) {
                        $this->ilias_config['user_data_category'] = $user_cat;
                    } else {
                        $this->error[] = sprintf(_("Die Kategorie \"%s\" konnte nicht angelegt werden."), $object_data["title"]);
                        return false;
                    }
                }
                $this->storeSettings();
            }
        }

        return true;
    }

    /**
     * get permissions status
     *
     * checks permissions settings
     * @access public
     * @return string messages
     */
    public function getPermissionsSettingsStatus()
    {
        // check role template
        if (!$this->ilias_config['author_role_name']) {
            $this->error[] = _("Das Rollen-Template für die persönliche Kategorie wurde noch nicht festgelegt.");
            return false;
        }
        $role_template = $this->soap_client->getObjectByTitle( $this->ilias_config['author_role_name'], "rolt" );
        if ($role_template == false) {
            $this->error[] = sprintf(_("Das Rollen-Template mit dem Namen \"%s\" wurde im System %s nicht gefunden."), htmlReady($this->ilias_config['author_role_name']), htmlReady($this->getName()));
            return false;
        }
        if (is_array($role_template))
        {
            $this->ilias_config['author_role'] = $role_template["obj_id"];
            $this->ilias_config['author_role_name'] = $role_template["title"];
            $this->storeSettings();
        }
        return true;
    }

    /**
     * create new user-account
     *
     * creates new ILIAS user account
     * @access public
     * @return boolean returns false
     */
    public function newUser()
    {
        if (!$this->user->studip_login) {
            return false;
        }
        $user_data = $this->user->getUserArray();
        $user_data["login"] = $this->ilias_config['user_prefix'].$user_data["login"];

        $user_exists = $this->soap_client->lookupUser($user_data["login"]);
        //automatische Zuordnung von bestehenden Ilias Accounts
        //nur wenn ldap Modus benutzt wird und Stud.IP Nutzer passendes ldap plugin hat
        if ($user_exists &&
                ! $this->ilias_config['user_prefix'] &&
                $this->ilias_config['ldap_enable'] &&
                ($this->user->auth_plugin != 'standard') &&
                ($this->user->auth_plugin == $this->ilias_config['ldap_enable'])) {
            $this->user->setConnection($this->user->getUserType(), true);
            PageLayout::postSuccess(sprintf(_("Verbindung mit Nutzer ID %s wiederhergestellt."), $this->user->id));
            return true;
        } elseif ($user_exists) {
            $this->error[] = sprintf(_('Externer Account konnte nicht angelegt werden. Es existiert bereits ein User mit dem Login %s in %s'), $user_data["login"], $this->ilias_config['name']);
            return false;
        }

        // set role according to Stud.IP perm
        if ($GLOBALS['auth']->auth['perm'] === 'root') {
            $role_id = 2;
        } else {
            $role_id = 4;
        }

        $this->soap_client->setCachingStatus(false);
        $this->soap_client->clearCache();
        $user_id = $this->soap_client->addUser($user_data, $role_id);
        if ($user_id != false)
        {
            $this->user->id = $user_id;
            $this->user->login = $this->ilias_config['user_prefix'].$this->user->studip_login;

            $this->user->setConnection(IliasUser::USER_TYPE_CREATED);
            return true;
        }
        return false;
    }

    /**
     * update given user account
     *
     * updates ILIAS user data
     * @access public
     * @param $user Stud.IP user object
     * @return boolean returns false
     */
    public function updateUser($user)
    {
        if (! is_object($user)) {
            return false;
        }
        $update_user = new IliasUser($this->index, $this->ilias_config['version'], $user->id);
        // if user is manually connected don't update user data
        if ($update_user->getUserType() == IliasUser::USER_TYPE_ORIGINAL) {
            return true;
        }
        $this->soap_client->setCachingStatus(false);
        $this->soap_client->clearCache();
        if ($update_user->isConnected() && $update_user->id && $this->soap_client->lookupUser($update_user->login)) {
            $user_data = $update_user->getUserArray();
            $user_data["login"] = $this->ilias_config['user_prefix'].$user_data["login"];

            // set role according to Stud.IP perm
            if ($user->perms == "root") {
                $role_id = 2;
            } else {
                $role_id = 4;
            }

            $user_id = $this->soap_client->addUser($user_data, $role_id);
            if ($user_id != false) {
                $update_user->login = $user_data["login"];
                $update_user->setConnection(IliasUser::USER_TYPE_CREATED);
                return true;
            }
        }
        return false;
    }

    /**
     * create new user category
     *
     * creates new ILIAS user account
     * @access public
     * @return boolean returns false
     */
    public function newUserCategory()
    {
        if (!$this->user->studip_login) {
            return false;
        }
        $this->soap_client->setCachingStatus(false);
        $this->soap_client->clearCache();

        // data for user category in ILIAS
        $object_data["title"] = sprintf(_("Eigene Daten von %s (%s)."), $this->user->getName(), $this->user->getId());
        $object_data["description"] = sprintf(_("Hier befinden sich die persönlichen Lernmodule des Benutzers %s."), $this->user->getName());
        $object_data["type"] = "cat";
        $object_data["owner"] = $this->user->getId();

        // check if category already exists
        $cat = $this->soap_client->getReferenceByTitle($object_data["title"]);
        if (($cat != false) && $this->soap_client->checkReferenceById($cat) ) {
            $this->user->category = $cat;
        } else {
            // add new user category at main user data category in ILIAS
            $this->user->category = $this->soap_client->addObject($object_data, $this->ilias_config['user_data_category']);
        }
        if ($this->ilias_config['category_to_desktop'] && $this->user->category) {
            $this->soap_client->addDesktopItems($this->user->getId(), [$this->user->category]);
        }

        // store data
        if ($this->user->category != false) {
            $this->user->setConnection($this->user->getUserType());
        } else {
            $this->error[] = _('ILIAS-User-Kategorie konnte nicht angelegt werden.');
            return false;
        }

        // personal user role in ILIAS
        $role_data["title"] = "studip_usr" . $this->user->getId() . "_cat" . $this->user->category;
        $role_data["description"] = sprintf(_("User-Rolle von %s. Diese Rolle wurde von Stud.IP generiert."), $this->user->getName());
        $role_id = $this->soap_client->getObjectByTitle($role_data["title"], "role");
        if ($role_id == false) {
            $role_id = $this->soap_client->addRoleFromTemplate($role_data, $this->user->getCategory(), $this->ilias_config['author_role']);
        }
        $this->soap_client->addUserRoleEntry($this->user->getId(), $role_id);

        // delete permissions for all global roles (User, Guest, Anonymous) for this category
        foreach ($this->global_roles as $key => $role) {
            $this->soap_client->revokePermissions($role, $this->user->category);
        }
        return true;
    }

    /**
     * get ILIAS user full name
     *
     * returns full name of given ILIAS user ID
     * @access public
     * @param $user_id ILIAS user id
     * @return string full name
     */
    public function getUserFullname($user_id)
    {
        return $this->soap_client->getUserFullname($user_id);
    }

    /**
     * get ILIAS path
     *
     * returns full path for given ILIAS ref ID
     * @access public
     * @param $ref_id ILIAS reference id
     * @return string path
     */
    public function getPath($ref_id)
    {
        return $this->soap_client->getPath($ref_id);
    }

    /**
     * get structure
     *
     * returns structure for given ILIAS lm ID
     * @access public
     * @param $ref_id ILIAS reference id
     * @return string path
     */
    public function getStructure($ref_id)
    {
        return $this->soap_client->getStructure($ref_id);
    }

    /**
     * get supported module types
     *
     * returns all active module types for current ILIAS installation
     * @access public
     */
    public static function getsupportedModuleTypes()
    {
        return [
//                        'cat'  => _('Kategorie'),
//                        'crs'  => _('Kurs'),
                        'webr' => _('Weblink'),
                        'htlm' => _('HTML-Lernmodul'),
                        'sahs' => _('SCORM/AICC-Lernmodul'),
                        'lm'   => _('ILIAS-Lernmodul'),
                        'glo'  => _('Glossar'),
                        'tst'  => _('Test'),
                        'svy'  => _('Umfrage'),
                        'exc'  => _('Übung')
        ];
    }

    /**
     * get active module types
     *
     * returns all active module types for current ILIAS installation
     * @access public
     */
    public function getAllowedModuleTypes()
    {
        return $this->ilias_config['modules'];
    }

    /**
     * check is module type is allowed
     *
     * returns true if module type is allowed for current ILIAS installation
     * @access public
     */
    public function isAllowedModuleType($module_type)
    {
        return (boolean)$this->ilias_config['modules'][$module_type];
    }

    /**
     * get existing ilias indices
     *
     * loads existing indices of all ilias installations from database
     * @access public
     */
    public static function getExistingIndices()
    {
        $query = "SELECT DISTINCT external_user_system_type FROM auth_extern ORDER BY external_user_system_type ASC";
        return DBManager::get()->fetchGrouped($query);
    }

    /**
     * get user modules
     *
     * returns content modules from current users private category
     * @access public
     * @return array list of content modules
     */
    public function getUserModules()
    {
        if (count($this->user_modules)) {
            return $this->user_modules;
        }
        $types = [];
        foreach ($this->getAllowedModuleTypes() as $type => $name) {
            $types[] = $type;
        }
        if ($this->user->getCategory() == false) {
            return [];
        }
        $result = $this->soap_client->getTreeChilds($this->user->getCategory(), $types, $this->user->getId());
        $obj_ids = [];
        if (is_array($result)) {
            foreach($result as $key => $object_data) {
                $this->user_modules[$key] = new IliasModule($key, $object_data, $this->index, $this->ilias_int_version);
            }
        }
        return $this->user_modules;
    }


    /**
     * get module
     *
     * returns module instance by ID
     * @access public
     * @param string $module_id ILIAS ref id
     * @return instance of IliasModule
     */
    public function getModule($module_id)
    {
        $object_data = $this->soap_client->getObjectByReference($module_id, $this->user->getId());
        $module = new IliasModule($module_id, $object_data, $this->index, $this->ilias_int_version);
        return $module;
    }

    /**
     * Helper function to fetch childs including objects in folders
     *
     * @access public
     * @param string $parent_id
     * @return array result
     */
    public function getChilds($parent_id) {
        $types[] = 'fold';
        foreach ($this->ilias_config['modules'] as $type => $name) {
            $types[] = $type;
        }
        $result = $this->soap_client->getTreeChilds($parent_id, $types);
        $user_result = $this->soap_client->getTreeChilds($parent_id, $types, $this->user->getId());

        if ($result) {
            foreach($result as $ref_id => $data) {
                if ($data['type'] == 'fold') {
                    unset($result[$ref_id]);
                    $result = $result + $this->getChilds($ref_id);
                } else {
                    $result[$ref_id]['accessInfo'] = $user_result[$ref_id]['accessInfo'];
                    $result[$ref_id]['references'][$ref_id] = $user_result[$ref_id]['references'][$ref_id];
                }
            }
        }

        if (is_array($result))
            return $result;
            else
                return [];
    }

    /**
     * check connected modules and update connections
     *
     * checks if there are modules in the course that are not connected to the seminar
     * @access public
     * @param string $course_id course-id
     * @return boolean successful
     */
    public function updateCourseConnections($course_id)
    {
        $this->soap_client->setCachingStatus(false);
        // fetch childs
        $result = $this->getChilds($course_id);

        if (is_array($result)) {
            $check = DBManager::get()->prepare("SELECT 1 FROM object_contentmodules WHERE object_id = ? AND module_id = ? AND system_type = ? AND module_type = ?");
            $found = [];
            $added = 0;
            $deleted = 0;
            $messages["info"] .= "<b>".sprintf(_("Aktualisierung der Zuordnungen zum System \"%s\":"), $this->getName()) . "</b><br>";
            foreach($result as $ref_id => $data) {
                if (($data['accessInfo'] == 'granted') || ($this->ilias_interface_config['show_offline'] && $data['offline'])) {
                    $this->course_modules[$ref_id] = new IliasModule($ref_id, $data, $this->index, $this->ilias_int_version);
                }
                $check->execute([Context::getId(), $ref_id, $this->index, $data["type"]]);
                if (!$check->fetch()) {
                    $messages["info"] .= sprintf(_("Zuordnung zur Lerneinheit \"%s\" wurde hinzugefügt."), ($data["title"])) . "<br>";
                    IliasObjectConnections::setConnection(Context::getId(), $ref_id, $data["type"], $this->index);
                    $added++;
                }
                $found[] = $ref_id . '_' . $data["type"];
            }
            $to_delete = DBManager::get()->prepare("SELECT module_id,module_type FROM object_contentmodules WHERE module_type <> 'crs' AND object_id = ? AND system_type = ? AND CONCAT_WS('_', module_id,module_type) NOT IN (?)");
            $to_delete->execute([Context::getId(), $this->index, count($found) ? $found : ['']]);
            while ($row = $to_delete->fetch(PDO::FETCH_ASSOC)) {
                IliasObjectConnections::unsetConnection(Context::getId(), $row["module_id"], $row["module_type"], $this->index);
                $deleted++;
                $messages["info"] .= sprintf(_("Zuordnung zu \"%s\" wurde entfernt."), $row["module_id"]  . '_' . $row["module_type"]) . "<br>";
            }
            if (($added + $deleted) < 1) {
                $messages["info"] .= _("Die Zuordnungen sind bereits auf dem aktuellen Stand.") . "<br>";
            }
            return true;
        }
        return false;
    }

    /**
     * set module connection
     *
     * sets module connection to course
     * @access public
     * @param string $studip_course_id studip range id
     * @param string $module_id ILIAS ref id
     * @param string $module_type type of ILIAS module
     * @param string $connection_mode copy or reference
     * @param string $write_permission_level write permission for new module requires this perm (autor, tutor, dozent, never)
     * @return boolean successful
     */
    public function setCourseModuleConnection($studip_course_id, $module_id, $module_type, $connection_mode, $write_permission_level)
    {
        $object_data = $this->soap_client->getObjectByReference($module_id, $this->user->getId());
        $module = new IliasModule($module_id, $object_data, $this->index, $this->ilias_int_version);
        if (!$module->isAllowed('start')) {
            return false;
        }
        if (!$module->isAllowed('copy') && !$module->isAllowed('edit')) {
            $this->error[] = _("Keine Berechtigung zum Kopieren des Lernobjekts!");
            return false;
        }

        $crs_id = IliasObjectConnections::getConnectionModuleId($studip_course_id, "crs", $this->index);
        $this->soap_client->setCachingStatus(false);
        $this->soap_client->clearCache();

        if (! $crs_id) {
            // if no course entry create new course
            $crs_id = $this->addCourse($studip_course_id);
        } elseif ($crs_id AND ($this->soap_client->getObjectByReference($crs_id) == false)) {
            // if course entry is invalid create new course
            IliasObjectConnections::unsetConnection($studip_course_id, $crs_id, "crs", $this->index);
            $this->error[] = sprintf(_('Der zugeordnete ILIAS-Kurs (ID %s) existiert nicht mehr. Ein neuer Kurs wurde angelegt.'), $crs_id);
            $crs_id = $this->addCourse($studip_course_id);
        }

        if ($crs_id == false) {
            return false;
        }

        if ($connection_mode == 'copy') {
            $ref_id = $this->soap_client->copyObject($module_id, $crs_id);
        } elseif ($connection_mode == 'reference') {
            $ref_id = $this->soap_client->addReference($module_id, $crs_id);
        }
        if (! $ref_id) {
            $this->error[] = _("Zuordnungs-Fehler: Lernobjekt konnte nicht angelegt werden.");
            return false;
        }
        // set permissions for course roles
        $local_roles = $this->soap_client->getLocalRoles($crs_id);
        $member_operations = $this->getOperationArray([self::OPERATION_VISIBLE, self::OPERATION_READ]);
        $admin_operations = $this->getOperationArray([self::OPERATION_VISIBLE, self::OPERATION_READ, self::OPERATION_WRITE, self::OPERATION_COPY, self::OPERATION_DELETE]);
        $admin_operations_no_delete = $this->getOperationArray([self::OPERATION_VISIBLE, self::OPERATION_READ, self::OPERATION_WRITE, self::OPERATION_COPY]);
        $admin_operations_readonly = $this->getOperationArray([self::OPERATION_VISIBLE, self::OPERATION_READ, self::OPERATION_DELETE]);
        foreach ($local_roles as $key => $role_data) {
            // check only if local role is il_crs_member, -tutor or -admin
            if (mb_strpos($role_data["title"], "il_crs_") === 0) {
                if(mb_strpos($role_data["title"], 'il_crs_member') === 0){
                    $operations = ($write_permission_level == "autor") ? $admin_operations_no_delete : $member_operations;
                } elseif(mb_strpos($role_data["title"], 'il_crs_tutor') === 0){
                    $operations = (($write_permission_level == "tutor") || ($write_permission_level == "autor")) ? $admin_operations : $admin_operations_readonly;
                } elseif(mb_strpos($role_data["title"], 'il_crs_admin') === 0){
                    $operations = (($write_permission_level == "dozent") || ($write_permission_level == "tutor") || ($write_permission_level == "autor")) ? $admin_operations : $admin_operations_readonly;
                } else {
                    continue;
                }
                $this->soap_client->revokePermissions($role_data["obj_id"], $ref_id);
                $this->soap_client->grantPermissions($operations, $role_data["obj_id"], $ref_id);
            }
        }
        // store object connection
        if ($ref_id) {
            IliasObjectConnections::setConnection($studip_course_id, $ref_id, $module_type, $this->index);
            return true;
        }
        return false;
    }


    /**
     * unset module connection
     *
     * unsets ILIAS module connection with course
     * @access public
     * @param string $studip_course_id studip range id
     * @param string $module_id ILIAS ref id
     * @param string $module_type type of ILIAS module
     */
    public function unsetCourseModuleConnection($studip_course_id, $module_id, $module_type)
    {
        $this->soap_client->setCachingStatus(false);
        $this->soap_client->deleteObject($module_id);
        IliasObjectConnections::unsetConnection($studip_course_id, $module_id, $module_type, $this->index);
    }

    /**
     * add course module
     *
     * adds module instance to list of course modules
     * @access public
     */
    public function addCourseModule($module_id, $module_data)
    {
        $object_data = $this->soap_client->getObjectByReference($module_id, $this->user->getId());
        $this->course_modules[$module_id] = new IliasModule($module_id, $object_data, $this->index, $this->ilias_int_version);
        $this->course_modules[$module_id]->setConnectionType(true);
    }

    /**
     * get course modules
     *
     * returns all added course module instances
     * @access public
     */
    public function getCourseModules()
    {
        return $this->course_modules;
    }

    /**
     * create course
     *
     * creates new ilias course
     * @access public
     * @param string $studip_course_id seminar-id
     * @return boolean successful
     */
    public function addCourse($studip_course_id)
    {
        $crs_id = IliasObjectConnections::getConnectionModuleId($studip_course_id, "crs", $this->index);
        $this->soap_client->setCachingStatus(false);
        $this->soap_client->clearCache();

        if (!$crs_id) {
            $seminar = Seminar::getInstance($studip_course_id);
            // on error use root category
            $ref_id = $this->ilias_config['root_category'];
            if ($this->ilias_config['cat_semester'] == 'outer') {
                // category for semester above institute
                $semester_ref_id = IliasObjectConnections::getConnectionModuleId($seminar->start_semester->getId(), 'cat', $this->index);
                if (!$semester_ref_id) {
                    $object_data['title'] = $seminar->getStartSemesterName();
                    $object_data['description'] = sprintf(_('Hier befinden sich die Veranstaltungsdaten zum Semester "%s".'), $seminar->getStartSemesterName());
                    $object_data['type'] = 'cat';
                    $object_data['owner'] =  $this->soap_client->LookupUser($this->ilias_config['admin']);
                    $semester_ref_id = $this->soap_client->addObject($object_data, $ref_id);
                    if ($semester_ref_id) {
                        // store institute category
                        IliasObjectConnections::setConnection($seminar->start_semester->getId(), $semester_ref_id, 'cat', $this->index);
                    } else {
                        $this->error[] = sprintf(_('ILIAS-Kategorie %s konnte nicht angelegt werden.'), $object_data['title']);
                    }
                }
                if ($semester_ref_id) {
                    $ref_id = $semester_ref_id;
                    // category for home institute below semester
                    $home_institute = Institute::find($seminar->getInstitutId());
                    if ($home_institute) {
                        $institute_ref_id = IliasObjectConnections::getConnectionModuleId(md5($seminar->start_semester->getId().$home_institute->getId()), "cat", $this->index);
                    }
                    if (!$institute_ref_id) {
                        $object_data['title'] = $home_institute->name;
                        $object_data['description'] = sprintf(_('Hier befinden sich die Veranstaltungsdaten zur Stud.IP-Einrichtung "%s".'), $home_institute->name);
                        $object_data['type'] = 'cat';
                        $object_data['owner'] =  $this->soap_client->LookupUser($this->ilias_config['admin']);
                        $institute_ref_id = $this->soap_client->addObject($object_data, $ref_id);
                        if ($institute_ref_id) {
                            // store institute category
                            IliasObjectConnections::setConnection(md5($seminar->start_semester->getId().$home_institute->getId()), $institute_ref_id, "cat", $this->index);
                        }
                    }
                    if ($institute_ref_id) {
                        $ref_id = $institute_ref_id;
                    } else {
                        $this->error[] = sprintf(_('ILIAS-Kategorie %s konnte nicht angelegt werden.'), $object_data['title']);
                    }
                }
            } elseif ($this->ilias_config['cat_semester'] === 'inner' || $this->ilias_config['cat_semester'] === 'none') {
                // category for home institute
                $home_institute = Institute::find($seminar->getInstitutId());
                if ($home_institute) {
                    $institute_ref_id = IliasObjectConnections::getConnectionModuleId($home_institute->getId(), "cat", $this->index);
                }
                if (!$institute_ref_id) {
                    $object_data['title'] = $home_institute->name;
                    $object_data['description'] = sprintf(_('Hier befinden sich die Veranstaltungsdaten zur Stud.IP-Einrichtung "%s".'), $home_institute->name);
                    $object_data['type'] = 'cat';
                    $object_data['owner'] =  $this->soap_client->LookupUser($this->ilias_config['admin']);
                    $institute_ref_id = $this->soap_client->addObject($object_data, $ref_id);
                    if ($institute_ref_id) {
                        // store institute category
                        IliasObjectConnections::setConnection($home_institute->getId(), $institute_ref_id, "cat", $this->index);
                    } else {
                        $this->error[] = sprintf(_('ILIAS-Kategorie %s konnte nicht angelegt werden.'), $object_data["title"]);
                    }
                }
                if ($institute_ref_id) {
                    $ref_id = $institute_ref_id;
                    if ($this->ilias_config['cat_semester'] === 'inner') {
                        // category for semester below institute
                        $institute_semester_ref_id = IliasObjectConnections::getConnectionModuleId(md5($home_institute->getId().$seminar->start_semester->getId()), 'cat', $this->index);
                        if (!$institute_semester_ref_id) {
                            $object_data['title'] = $seminar->getStartSemesterName();
                            $object_data['description'] = sprintf(_('Hier befinden sich die Veranstaltungsdaten zum Semester "%s".'), $seminar->getStartSemesterName());
                            $object_data['type'] = 'cat';
                            $object_data['owner'] =  $this->soap_client->LookupUser($this->ilias_config['admin']);
                            $institute_semester_ref_id= $this->soap_client->addObject($object_data, $ref_id);
                            if ($institute_semester_ref_id) {
                                // store institute category
                                IliasObjectConnections::setConnection(md5($home_institute->getId().$seminar->start_semester->getId()), $institute_semester_ref_id, 'cat', $this->index);
                            }
                        }
                        if ($institute_semester_ref_id) {
                            $ref_id = $institute_semester_ref_id;
                        } else {
                            $this->error[] = sprintf(_('ILIAS-Kategorie %s konnte nicht angelegt werden.'), $object_data["title"]);
                        }
                    }
                }
            }

            // create course
            $lang_array = explode('_',$DEFAULT_LANGUAGE);
            $course_data['language'] = $lang_array[0];
            if ($this->ilias_config['course_semester'] === 'old' || $this->ilias_config['course_semester'] === 'old_bracket') {
                $course_data['title'] = sprintf(_('Stud.IP-Veranstaltung "%s"'), $seminar->getName());
            } else {
                $course_data['title'] = sprintf(_('%s'), $seminar->getName());
            }
            if ($this->ilias_config['course_semester'] === 'old_bracket' || $this->ilias_config['course_semester'] === 'bracket') {
                $course_data['title'] .= ' ('.$seminar->getStartSemesterName().')';
            }
            if ($this->ilias_config['course_veranstaltungsnummer']) {
                $course_data['title'] .= ' '.$seminar->VeranstaltungsNummer;
            }
            $course_data['description'] = sprintf(_('Dieser Kurs enthält die Lernobjekte der Stud.IP-Veranstaltung "%s".'), $seminar->getName());
            $crs_id = $this->soap_client->addCourse($course_data, $ref_id);
            if (!$crs_id) {
                $this->error[] = _('ILIAS-Kurs konnte nicht angelegt werden.');
                return false;
            }
            IliasObjectConnections::setConnection($studip_course_id, $crs_id, 'crs', $this->index);

            // Rollen zuordnen
            $this->CheckUserCoursePermissions($crs_id);
            return $crs_id;
        }
    }

    /**
     * check user permissions
     *
     * checks user permissions for connected course and changes setting if necessary
     * @access public
     * @param string $ilias_course_id course-id
     * @return boolean returns false on error
     */
    public function checkUserCoursePermissions($ilias_course_id = "")
    {
        if (($ilias_course_id == "") || ($this->user->getId() == "")) {
            return false;
        }

        if ($GLOBALS['user']->perms == 'root') {
            return true;
        }

        // get course role folder and local roles
        $user_roles = $this->soap_client->getUserRoles($this->user->getId());
        $local_roles = $this->soap_client->getLocalRoles($ilias_course_id);
        $active_role = "";
        $proper_role = "";
        $user_crs_role = $this->crs_roles[$GLOBALS["perm"]->get_studip_perm(Context::getId())];
        if (is_array($local_roles)) {
            foreach ($local_roles as $key => $role_data) {
                // check only if local role is il_crs_member, -tutor or -admin
                if (! (mb_strpos($role_data["title"], "_crs_") === false)) {
                    if ( in_array( $role_data["obj_id"], $user_roles ) ) {
                        $active_role = $role_data["obj_id"];
                    }
                    if ( mb_strpos( $role_data["title"], $user_crs_role) > 0 ) {
                        $proper_role = $role_data["obj_id"];
                    }
                }
            }
        }

        // is user already course-member? otherwise add member with proper role
        $is_member = $this->soap_client->isMember( $this->user->getId(), $ilias_course_id);
        if (!$is_member) {
            $member_data["usr_id"] = $this->user->getId();
            $member_data["ref_id"] = $ilias_course_id;
            $member_data["status"] = CRS_NO_NOTIFICATION;
            $type = "";
            switch ($user_crs_role)
            {
                case "admin":
                    $member_data["role"] = self::CRS_ADMIN_ROLE;
                    $type = "Admin";
                    break;
                case "tutor":
                    $member_data["role"] = self::CRS_TUTOR_ROLE;
                    $type = "Tutor";
                    break;
                case "member":
                    $member_data["role"] = self::CRS_MEMBER_ROLE;
                    $type = "Member";
                    break;
                default:
            }
            $member_data["passed"] = self::CRS_PASSED_VALUE;
            if ($type != "") {
                $this->soap_client->addMember( $this->user->getId(), $type, $ilias_course_id);
                $this->permissions_changed = true;
            }
        }

        // check if user has proper local role
        // if not, change it
        if ($active_role != $proper_role) {
            if ($active_role) {
                $this->soap_client->deleteUserRoleEntry( $this->user->getId(), $active_role);
            }

            if ($proper_role) {
                $this->soap_client->addUserRoleEntry( $this->user->getId(), $proper_role);
            }
            $this->permissions_changed = true;
        }

        if (! $this->getUserModuleViewPermission($ilias_course_id)) {
            return false;
        }

        return true;
    }

    /**
     * get user permissions for ILIAS module
     *
     * returns allowed operations for current user and given module
     * @access public
     * @param string $module_id module-id
     * @return boolean returns false on error
     */
    public function getUserModuleViewPermission($module_id)
    {
        $this->allowed_operations = [];
        $this->tree_allowed_operations = $this->soap_client->getObjectTreeOperations(
                    $module_id,
                    $this->user->getId()
                    );
        if (! is_array($this->tree_allowed_operations)) {
            return false;
        }

        $view_permission = false;
        if ((in_array($this->operations[self::OPERATION_READ], $this->tree_allowed_operations)) && (in_array($this->operations[self::OPERATION_VISIBLE], $this->tree_allowed_operations))) {
            $view_permission = true;
        }
        return $view_permission;
    }

    /**
     * get operation
     *
     * returns id for given operation-string
     * @access public
     * @param string $operation operation
     * @return integer operation-id
     */
    public function getOperation($operation)
    {
        // get operation IDs
        if (!count($this->operations)) {
            $this->operations = $this->soap_client->getOperations();
        }

        return $this->operations[$operation];
    }

    /**
     * get operation-ids
     *
     * returns an array of operation-ids
     * @access public
     * @param string $operation operation
     * @return array operation-ids
     */
    public function getOperationArray($operation)
    {
        // get operation IDs
        if (!count($this->operations)) {
            $this->operations = $this->soap_client->getOperations();
        }

        $ops_array = [];
        if (is_array($operation)) {
            foreach ($operation as $key => $operation_name) {
                $ops_array[] = $this->operations[$operation_name];
            }
        }
        return $ops_array;
    }

    /**
    * get name of ILIAS installation
    *
    * returns name of cms
    * @access public
    * @return string name
    */
    public function getName()
    {
        return $this->ilias_config['name'];
    }

    /**
    * get index of ILIAS installation
    *
    * returns index of ILIAS installation
    * @access public
    * @return string type
    */
    public function getIndex()
    {
        return $this->index;
    }

    /**
    * get url of ILIAS installation
    *
    * returns url of ILIAS installation
    * @access public
    * @return string path
    */
    public function getAbsolutePath()
    {
        return $this->ilias_config['url'];
    }

    /**
    * get target file of ILIAS installation
    *
    * returns target file of ILIAS installation
    * @access public
    * @return string target file
    */
    public function getTargetFile()
    {
        return $this->ilias_config['url'].'studip_referrer.php';
    }

    /**
    * get active-setting
    *
    * returns true, if ILIAS installation is active
    * @access public
    * @return boolean active-setting
    */
    public function isActive()
    {
        return $this->ilias_config['is_active'];
    }

    /**
     * get client-id
     *
     * returns client-id
     * @access public
     * @return string client-id
     */
    public function getClientId()
    {
        return $this->ilias_config['client'];
    }

    /**
    * get user prefix
    *
    * returns user prefix
    * @access public
    * @return string user prefix
    */
    public function getUserPrefix()
    {
        return $this->ilias_config['user_prefix'];
    }

    /**
     * get errors
     *
     * returns array of error strings.
     * @access public
     * @return array of error strings
     */
    public function getError()
    {
        return $this->error;
    }

    /**
    * search ILIAS modules
    *
    * performs search for ILIAS modules
    * @access public
    * @return boolean returns false
    */
    public function searchModules($search_key)
    {
        $types = [];
        foreach ($this->getAllowedModuleTypes() as $type => $name) {
            $types[] = $type;
        }
        $search_modules = [];

        $result = $this->soap_client->searchObjects($types, $search_key, "and", $this->user->getId());
        if ($result) {
            foreach($result as $key => $object_data) {
                // set every single reference as part of the result
                foreach ($object_data['references'] as $ref_id => $reference) {
                    $search_modules[$ref_id] = new IliasModule($ref_id, $object_data, $this->index, $this->ilias_int_version);
                }
            }
        }
        return $search_modules;
    }

    public function deleteConnectedModules($object_id){
        return IliasObjectConnections::DeleteAllConnections($object_id, $this->index);
    }
}
