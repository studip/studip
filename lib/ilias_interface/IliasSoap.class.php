<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

require_once 'vendor/ilias/class.ilSaxParser.php';
require_once 'vendor/ilias/class.ilObjectXMLParser.php';

/**
* class for ILIAS-SOAP-Webservice
*
* This class contains methods to connect to the ILIAS SOAP Server.
*
* @author       Arne Schröder <schroeder@data-quest.de>
* @access       public
* @modulegroup  ilias_interface_modules
* @module       IliasSoap
* @package      ILIAS-Interface
*/
class IliasSoap extends StudipSoapClient
{
    private $index;
    private $ilias_client;
    private $ilias_version;
    private $admin_login;
    private $admin_password;
    private $admin_sid;
    private $user_sid;
    private $user_type;
    private $soap_cache;
    private $separator_string;


    /**
     * constructor
     *
     * init class.
     * @access public
     * @param string $index ILIAS installation index
     * @param string $soap_path SOAP url
     * @param string $ilias_client ILIAS client
     * @param string $ilias_version ILIAS int client
     * @param string $admin_login ILIAS admin account login
     * @param string $admin_password ILIAS admin account password
     */
    public function __construct($index, $soap_path, $ilias_client = '', $ilias_version = '', $admin_login = '', $admin_password = '')
    {
        $this->index = $index;
        $this->ilias_client = $ilias_client;
        $this->ilias_version= $ilias_version;
        $this->admin_login = $admin_login;
        $this->admin_password = $admin_password;
        $this->separator_string = " / ";

        parent::__construct($soap_path);

        $this->user_type = "admin";

        $this->loadCacheData();
        $this->caching_active = false;
    }

    /**
    * set usertype
    *
    * sets usertype for soap-calls
    * @access public
    * @param string user_type usertype (admin or user)
    */
    function setUserType($user_type)
    {
        $this->user_type = $user_type;
    }

    /**
    * get sid
    *
    * returns soap-session-id
    * @access public
    * @return string session-id
    */
    function getSID()
    {
        if ($this->user_type == "admin") {
            if ($this->admin_sid == false)
                $this->loginAdmin();
            return $this->admin_sid;
        }
        if ($this->user_type == "user") {
            if ($this->user_sid == false)
                $this->loginUser();
            return $this->user_sid;
        }
        return false;
    }

    /**
    * call soap-function
    *
    * calls soap-function with given parameters
    * @access public
    * @param string method method-name
    * @param string params parameters
    * @return mixed result
    */
    function call($method, $params)
    {
        // return false if no session_id is given
        if ($method !== 'login' && $method !== 'getInstallationInfoXML' && $method !== 'getClientInfoXML' && $params['sid'] == '') {
            return false;
        }

        $cache_index = md5($method . ':' . implode($params, '-'));
        if ($this->caching_active && isset($this->soap_cache[$cache_index]) && $method !== 'login') {
            $result = $this->soap_cache[$cache_index];
        } else {
            $result = $this->_call($method, $params);
            // if Session is expired, re-login and try again
            if ($method !== 'login' && $this->soap_client->fault && in_array(mb_strtolower($this->faultstring), ['session not valid', 'session invalid', 'session idled'])) {
                $caching_status = $this->caching_active;
                $this->caching_active = false;
                $params["sid"] = $this->getSID();
                $result = $this->_call($method, $params);
                $this->caching_active = $caching_status;
            } elseif (! $this->soap_client->fault) {
                $this->soap_cache[$cache_index] = $result;
                if ($this->caching_active == true) {
                    $this->saveCacheData();
                }
            }
        }
        return $result;
    }

    /**
    * load cache
    *
    * load soap-cache
    * @access public
    * @param string cms cms-type
    */
    function loadCacheData()
    {
        $this->soap_cache = (array)$_SESSION["cache_data"][$this->index];
    }

    /**
    * get caching status
    *
    * gets caching-status
    * @access public
    * @return boolean status
    */
    function getCachingStatus()
    {
        return $this->caching_active;
    }

    /**
    * set caching status
    *
    * sets caching-status
    * @access public
    * @param boolean bool_value status
    */
    function setCachingStatus($bool_value)
    {
        $this->caching_active = $bool_value;
    }

    /**
    * clear cache
    *
    * clears cache
    * @access public
    */
    function clearCache()
    {
        $this->soap_cache = [];
        $_SESSION["cache_data"][$this->index] = [];

    }

    /**
    * save cache
    *
    * saves soap-cache in session-variable
    * @access public
    */
    function saveCacheData()
    {
       $_SESSION["cache_data"][$this->index] = $this->soap_cache;
    }

    /**
    * parse xml
    *
    * use xml-parser
    * @access public
    * @param string data xml-data
    * @return array object
    */
    function ParseXML($data)
    {
        //$xml_parser = new Ilias3ObjectXMLParser($data);
        $xml_parser = new IlObjectXMLParser($data);
        $xml_parser->startParsing();
        return $xml_parser->getObjectData();
    }

    /**
    * login with admin account
    *
    * login to ILIAS soap webservice with admin account
    * @access public
    * @return string result
    */
    function loginAdmin()
    {
        $param = [
                'client' => $this->ilias_client,
                'username' => $this->admin_login,
                'password' => $this->admin_password
                ];
        $result = $this->call('login', $param);
        $this->admin_sid = $result;
        return $result;
    }

    /**
     * login with admin account
     *
     * login to ILIAS soap webservice with current user
     * @access public
     * @return string result
     */
    function loginUser($username, $password)
    {
        if ($this->ilias_version < 50305) {
            // ILIAS-Versions below 5.3.5 (use LoginStudipUser)
            $param = [
                            'client' => $this->ilias_client,
                            'username' => $username,
                            'password' => $password
            ];
            $result = $this->call('loginStudipUser', $param);
            $this->user_sid = $result;
            return $result;
        } else {
            // ILIAS-Versions 5.3.5 and above (use StudipAuthPlugin)
            $param = [
                            'client' => $this->ilias_client,
                            'username' => $username,
                            'password' => $password
            ];
            $result = $this->call('login', $param);
            $this->user_sid = $result;
            return $result;
        }
    }

    /**
    * logout
    *
    * logout from soap-webservice
    * @access public
    * @return boolean result
    */
    function logout()
    {
        $param = [
            'sid' => $this->getSID()
            ];
        return $this->call('logout', $param);
    }

    /**
     * Check Auth
     *
     * login to soap-webservice
     * @access public
     * @return string result
     */
    function checkPassword($username, $password)
    {
        $param = [
                        'client' => $this->ilias_client,
                        'username' => $username,
                        'password' => $password
        ];
        $result = $this->call('login', $param);
        return $result;
    }

///////////////////////////
// OBJECT-FUNCTIONS //
//////////////////////////

    /**
     * parse ILIAS object
     *
     * parse XML and return ilias object(s)
     * @access public
     * @param string xml xml data
     * @param string parent_id get data for child references of parent_id
     * @return array objects
     */
    function parseIliasObject($xml, $condition_field = '', $condition_value = '')
    {
        $s = simplexml_load_string($xml);

        $objects = [];
        if (is_object($s->Object)) {
            foreach ($s->Object as $object) {
                $single_object = [];
                $single_object['type'] = (string)$object[0]['type'];
                $single_object['offline'] = (string)$object[0]['offline'];
                $single_object['obj_id'] = (string)$object[0]['obj_id'];
                $single_object['title'] = (string)$object->Title;
                $single_object['description'] = (string)$object->Description;
                $single_object['owner'] = (string)$object->Owner;
                $single_object['create_date'] = (string)$object->CreateDate;
                $single_object['last_update'] = (string)$object->LastUpdate;
                $single_object['ref_count'] = count($object->References);
                foreach ($object->References as $reference) {
                    //$single_object['references'][(string)$reference[0]['ref_id']]['ref_id'] = (string)$reference[0]['ref_id'];
                    if ($condition_field && ($reference[0][$condition_field] == $condition_value)) {
                        $single_object['ref_id'] = (string)$reference[0]['ref_id'];
                        $single_object['parent_id'] = (string)$reference[0]['parent_id'];
                        $single_object['accessInfo'] = (string)$reference[0]['accessInfo'];
                        foreach ($reference->Operation as $operation) {
                            $single_object['operations'][] = (string)$operation;
                        }
                    }
                    $single_object['references'][(string)$reference[0]['ref_id']]['parent_id'] = (string)$reference[0]['parent_id'];
                    $single_object['references'][(string)$reference[0]['ref_id']]['accessInfo'] = (string)$reference[0]['accessInfo'];
                    foreach ($reference->Operation as $operation) {
                        $single_object['references'][(string)$reference[0]['ref_id']]['operations'][] = (string)$operation;
                    }
                    foreach ($reference->Path->Element as $element) {
                        $single_object['references'][(string)$reference[0]['ref_id']]['path_names'][] = (string)$element;
                        $single_object['references'][(string)$reference[0]['ref_id']]['path_ids'][] = (string)$element[0]['ref_id'];
                        $single_object['references'][(string)$reference[0]['ref_id']]['path_types'][] = (string)$element[0]['type'];
                    }
                }
                if ($single_object['ref_id']) {
                    $objects[$single_object['ref_id']] = $single_object;
                } elseif (!$condition_field) {
                    $objects[] = $single_object;
                }
            }
        }
        return $objects;
    }


    /**
    * search objects
    *
    * search for ilias-objects
    * @access public
    * @param array types types
    * @param string key keyword
    * @param string combination search-combination
    * @param string user_id ilias-user-id
    * @return array objects
    */
    function searchObjects($types, $key, $combination, $user_id = "")
    {
        $param = [
            'sid' => $this->getSID(),
            'types' => $types,
            'key' => $key,
            'combination' => $combination
            ];
         if ($user_id != "")
            $param["user_id"] = $user_id;
        $result = $this->call('searchObjects', $param);
        if ($result != false)
        {
            //$objects = $this->parseXML($result);
            $objects = $this->parseIliasObject($result);
//            var_dump($objects);
            return $objects;
            if (count(objects)){
                foreach($all_objects as $one_object){
                    $ret[$one_object['ref_id']] = $one_object;
                }
                return $ret;
            }
        }
        return false;

    }

    /**
    * get object by reference
    *
    * gets object by reference-id
    * @access public
    * @param ref reference_id
    * @param string user_id ilias-user-id
    * @return array object
    */
    function getObjectByReference($ref, $user_id = "")
    {
        $param = [
            'sid' => $this->getSID(),
            'reference_id' => $ref
            ];
         if ($user_id != "")
            $param["user_id"] = $user_id;
        $result = $this->call('getObjectByReference', $param);
        if ($result != false)
        {

            $objects = $this->parseIliasObject($result, 'ref_id', $ref);
            return $objects[$ref];
        }
        return false;
    }

    /**
    * get object by title
    *
    * gets object by title
    * @access public
    * @param string key keyword
    * @param string type object-type
    * @return array object
    */
    function getObjectByTitle($key, $type = "")
    {
        $param = [
            'sid'   => $this->getSID(),
            'title' => $key
        ];
        $result = $this->call('getObjectsByTitle', $param);
        if ($result != false)
        {
            $objects = $this->parseIliasObject($result);
            //$objects = $this->parseXML($result);
            foreach($objects as $index => $object_data)
            {
                if (($type != "") AND ($object_data["type"] != $type))
                    unset($objects[$index]);
                elseif (! (mb_strpos(mb_strtolower($object_data["title"]), mb_strtolower(trim($key)) ) === 0))
                    unset($objects[$index]);
            }
            reset($objects);
            if (sizeof($objects) > 0)
                return current($objects);
        }
        return false;
    }

    /**
    * get reference by title
    *
    * gets reference-id by object-title
    * @access public
    * @param string key keyword
    * @param string type object-type
    * @return string reference-id
    */
    function getReferenceByTitle($key, $type = "")
    {
        $param = [
            'sid'   => $this->getSID(),
            'title' => $key
        ];
        $result = $this->call('getObjectsByTitle', $param);
        if ($result != false)
        {
            $objects = $this->parseIliasObject($result);
            foreach($objects as $index => $object_data)
            {
                if (($type != "") AND ($object_data["type"] != $type))
                    unset($objects[$index]);
                elseif (mb_strpos(mb_strtolower($object_data["title"]), mb_strtolower(trim($key)) ) === false)
                    unset($objects[$index]);
            }
            if (sizeof($objects) > 0)
                foreach($objects as $object_data)
                    if (sizeof($object_data["references"]) > 0)
                    {
                        return key($object_data["references"]);
                        //return $object_data["references"][0]["ref_id"];
                    }
        }
        return false;
    }

    /**
    * add object
    *
    * adds new ilias-object
    * @access public
    * @param array object_data object-data
    * @param string ref_id reference-id
    * @return string result
    */
    function addObject($object_data, $ref_id)
    {
        $this->clearCache();
        $type = $object_data["type"];
        $title = htmlReady($object_data["title"]);
        $description = htmlReady($object_data["description"]);

        $xml = "<!DOCTYPE Objects SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_object_0_1.dtd\">
<Objects>
  <Object type=\"$type\">
    <Title>
    $title
    </Title>
    <Description>
    $description
    </Description>
  </Object>
</Objects>";

        $param = [
            'sid' => $this->getSID(),
            'target_id' => $ref_id,
            'object_xml' => $xml
            ];
        return $this->call('addObject', $param);
    }

    /**
    * delete object
    *
    * deletes ilias-object
    * @access public
    * @param string ref_id reference-id
    * @return boolean result
    */
    function deleteObject($reference_id)
    {
        $this->clearCache();
        $param = [
            'sid' => $this->getSID(),
            'reference_id' => $reference_id
            ];
        return $this->call('deleteObject', $param);
    }

    /**
    * add reference
    *
    * add a new reference to an existing ilias-object
    * @access public
    * @param string object_id source-object-id
    * @param string ref_id target-id
    * @return string created reference-id
    */
    function addReference($object_id, $ref_id)
    {
        $this->clearCache();
        $param = [
            'sid' => $this->getSID(),
            'source_id' => $object_id,
            'target_id' => $ref_id
            ];
        return $this->call('addReference', $param);
    }

    /**
     * add references to desktop
     *
     * adds references to personal desktop
     * @access public
     * @param string object_id source-object-id
     * @param string ref_id target-id
     * @return string created reference-id
     */
    function addDesktopItems($user_id, $ref_ids)
    {
        $this->clearCache();
        $param = [
                        'sid' => $this->getSID(),
                        'user_id' => $user_id,
                        'reference_ids' => $ref_ids
        ];
        return $this->call('addDesktopItems', $param);
    }

    /**
    * get tree childs
    *
    * gets child-objects of the given tree node
    * @access public
    * @param string ref_id reference-id
    * @param array types show only childs with these types
    * @param string user_id user-id for permissions
    * @return array objects
    */
    function getTreeChilds($ref_id, $types = "", $user_id = "")
    {
        if ($types == "")
            $types = [];
        $param = [
            'sid' => $this->getSID(),
            'ref_id' => $ref_id,
            'types' => $types
            ];
        if ($user_id != "") {
            $param["user_id"] = $user_id;
        }
        $result = $this->call('getTreeChilds', $param);
        $tree_childs = [];
        if ($result != false) {
            $tree_childs = $this->parseIliasObject($result, 'parent_id', $ref_id);
        }
        return $tree_childs;
    }

/////////////////////////
// RBAC-FUNCTIONS //
///////////////////////
    /**
    * get operation
    *
    * gets all ilias operations
    * @access public
    * @return array operations
    */
    function getOperations()
    {
        $param = [
            'sid' => $this->getSID()
            ];
        $result = $this->call('getOperations', $param);
        if (is_array($result))
            foreach ($result as $operation_set)
                $operations[$operation_set["operation"]] = $operation_set["ops_id"];
        return $operations;
    }

    /**
    * get object tree operations
    *
    * gets permissions for object at given tree-node
    * @access public
    * @param string ref_id reference-id
    * @param string user_id user-id for permissions
    * @return array operation-ids
    */
    function getObjectTreeOperations($ref_id, $user_id)
    {
        $param = [
            'sid' => $this->getSID(),
            'ref_id' => $ref_id,
            'user_id' => $user_id
            ];
        $result = $this->call('getObjectTreeOperations', $param);
        if ($result != false)
        {
            $ops_ids = [];
            foreach ($result as $operation_set)
                $ops_ids[] = $operation_set["ops_id"];
            return $ops_ids;
        }
        return false;
    }

    /**
    * get user roles
    *
    * gets user roles
    * @access public
    * @param string user_id user-id
    * @return array role-ids
    */
    function getUserRoles($user_id)
    {
        $param = [
            'sid' => $this->getSID(),
            'user_id' => $user_id
           ];
        $result = $this->call('getUserRoles', $param);
        if ($result != false)
        {
            // TODO: change to simple xml
            $objects = $this->parseXML($result);
            $roles = [];
            foreach ($objects as $count => $role)
                $roles[$count] = $role["obj_id"];
            return $roles;
        }
        return false;
    }

    /**
    * get local roles
    *
    * gets local roles for given object
    * @access public
    * @param string course_id object-id
    * @return array role-objects
    */
    function getLocalRoles($course_id)
    {
        $param = [
            'sid' => $this->getSID(),
            'ref_id' => $course_id
           ];
        $result = $this->call('getLocalRoles', $param);
        if ($result != false)
        {
            // TODO: change to simple xml
            $objects = $this->parseXML($result);
            return $objects;
        }
        return false;
    }

    /**
    * add role
    *
    * adds a new role
    * @access public
    * @param array role_data data for role-object
    * @param string ref_id reference-id
    * @return string role-id
    */
    function addRole($role_data, $ref_id)
    {
        $this->clearCache();
        $type = "role";
        $title = htmlReady($role_data["title"]);
        $description = htmlReady($role_data["description"]);

        $xml = "<!DOCTYPE Objects SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_object_0_1.dtd\">
<Objects>
  <Object type=\"$type\">
    <Title>
    $title
    </Title>
    <Description>
    $description
    </Description>
  </Object>
</Objects>";

        $param = [
            'sid' => $this->getSID(),
            'target_id' => $ref_id,
            'obj_xml' => $xml
            ];
        $result = $this->call('addRole', $param);
        if (is_array($result))
            return current($result);
        else
            return false;
    }

    /**
    * add role from tremplate
    *
    * adds a new role and adopts properties of the given role template
    * @access public
    * @param array role_data data for role-object
    * @param string ref_id reference-id
    * @param string role_id role-template-id
    * @return string role-id
    */
    function addRoleFromTemplate($role_data, $ref_id, $role_id)
    {
        $this->clearCache();
        $type = "role";
        $title = htmlReady($role_data["title"]);
        $description = htmlReady($role_data["description"]);

        $xml = "<!DOCTYPE Objects SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_object_0_1.dtd\">
<Objects>
  <Object type=\"$type\">
    <Title>
    $title
    </Title>
    <Description>
    $description
    </Description>
  </Object>
</Objects>";

        $param = [
            'sid' => $this->getSID(),
            'target_id' => $ref_id,
            'obj_xml' => $xml,
            'role_template_id' => $role_id
            ];
        $result = $this->call('addRoleFromTemplate', $param);
        if (is_array($result))
            return current($result);
        else
            return false;
    }

    /**
    * delete user role entry
    *
    * deletes a role entry from the given user
    * @access public
    * @param string user_id user-id
    * @param string role_id role-id
    * @return boolean result
    */
    function deleteUserRoleEntry($user_id, $role_id)
    {
        $this->clearCache();
        $param = [
            'sid' => $this->getSID(),
            'user_id' => $user_id,
            'role_id' => $role_id
           ];
        return $this->call('deleteUserRoleEntry', $param);
    }

    /**
    * add user role entry
    *
    * adds a role entry for the given user
    * @access public
    * @param string user_id user-id
    * @param string role_id role-id
    * @return boolean result
    */
    function addUserRoleEntry($user_id, $role_id)
    {
        $this->clearCache();
        $param = [
            'sid' => $this->getSID(),
            'user_id' => $user_id,
            'role_id' => $role_id
           ];
        return $this->call('addUserRoleEntry', $param);
    }

    /**
    * grant permissions
    *
    * grants permissions for given operations at role-id and ref-id
    * @access public
    * @param array operations operation-array
    * @param string role_id role-id
    * @param string ref_id reference-id
    * @return boolean result
    */
    function grantPermissions($operations, $role_id, $ref_id)
    {
        $this->clearCache();
        $param = [
            'sid' => $this->getSID(),
            'ref_id' => $ref_id,
            'role_id' => $role_id,
            'operations' => $operations,
           ];
        return $this->call('grantPermissions', $param);
    }

    /**
    * revoke permissions
    *
    * revokes all permissions role-id and ref-id
    * @access public
    * @param string role_id role-id
    * @param string ref_id reference-id
    * @return boolean result
    */
    function revokePermissions($role_id, $ref_id)
    {
        $this->clearCache();
        $param = [
            'sid' => $this->getSID(),
            'ref_id' => $ref_id,
            'role_id' => $role_id,
           ];
        return $this->call('revokePermissions', $param);
    }

/////////////////////////
// USER-FUNCTIONS //
///////////////////////

    /**
    * lookup user
    *
    * gets user-id for given username
    * @access public
    * @param string username username
    * @return string user-id
    */
    function lookupUser($username)
    {
        $param = [
            'sid'       => $this->getSID(),
            'user_name' => $username,
        ];
        return $this->call('lookupUser', $param); // returns user_id
    }

    /**
    * get user
    *
    * gets user-data for given user-id
    * @access public
    * @param string user_id user-id
    * @return array user-data
    */
    function getUser($user_id)
    {
        $param = [
            'sid' => $this->getSID(),
            'user_id'         => $user_id,
            ];
        $result = $this->call('getUser', $param); // returns user data array
        return $result;
    }

    /**
     * get user fullname
     *
     * gets user-data for given user-id
     * @access public
     * @param string user_id user-id
     * @return string full name
     */
    function getUserFullname($user_id)
    {
        $param = [
                        'sid' => $this->getSID(),
                        'user_id'         => $user_id,
        ];
        $result = $this->call('getUser', $param); // returns user data array
        $objects = $result;
        return trim(sprintf('%s %s %s', $result['title'], $result['firstname'], $result['lastname']));
    }

    /**
     * search users
     *
     * search for ilias users
     * @access public
     * @param array types types
     * @param string key keyword
     * @param string combination search-combination
     * @param string user_id ilias-user-id
     * @return array objects
     */
    function searchUser($user_id)
    {
        if ($user_id != "") {
            $param = [
                            'sid' => $this->getSID(),
                            'user_ids' => [$user_id],
                            'attach_roles' => 0
            ];
            $result = $this->call('getUserXML', $param);
            if ($result != false)
            {
                // TODO: change to simple xml
                $objects = $this->parseXML($result);
                $all_objects = [];
                foreach($objects as $count => $object_data){
                    if (is_array($object_data["references"]))
                    {
                        foreach($object_data["references"] as $ref_data)
                            if ($ref_data["accessInfo"] == "granted"
                                    && (count($all_objects[$object_data["obj_id"]]["operations"]) < count($ref_data["operations"])))
                            {
                                $all_objects[$object_data["obj_id"]] = $object_data;
                                unset($all_objects[$object_data["obj_id"]]["references"]);
                                $all_objects[$object_data["obj_id"]]["ref_id"] = $ref_data["ref_id"];
                                $all_objects[$object_data["obj_id"]]["accessInfo"] = $ref_data["accessInfo"];
                                $all_objects[$object_data["obj_id"]]["operations"] = $ref_data["operations"];
                            }
                    }
                }
                if (count($all_objects)){
                    foreach($all_objects as $one_object){
                        $ret[$one_object['ref_id']] = $one_object;
                    }
                    return $ret;
                }
            }
            return false;
        }
    }

    /**
     * add user by importUsers
     *
     * adds new user and sets role-id
     * @access public
     * @param array user_data user-data
     * @param string role_id global role-id for new user
     * @return string user-id
     */
    function addUser($user_data, $role_id)
    {
        $this->clearCache();
        foreach($user_data as $key => $value) {
            $user_data[$key] = htmlReady($user_data[$key]);
        }
        $update = $user_data["id"];

        $usr_xml = "<Users>
<User".($update ? ' Id="'.$user_data["id"].'"' : '')." Action=".($update ? '"Update"' : '"Insert"').">
<UDFDefinitions></UDFDefinitions>
<Login>".$user_data["login"]."</Login>
<Password Type=\"PLAIN\">".$user_data["passwd"]."</Password>
<Firstname>".$user_data["firstname"]."</Firstname>
<Lastname>".$user_data["lastname"]."</Lastname>
<Title>".$user_data["title"]."</Title>
<Gender>".$user_data["gender"]."</Gender>
<Email>".$user_data["email"]."</Email>
<Street>".$user_data["street"]."</Street>
<PhoneHome>".$user_data["phone_home"]."</PhoneHome>
<Role Id=\"".$role_id."\" Type=\"Global\"/>
<Active>true</Active>
<TimeLimitUnlimited>".$user_data["time_limit_unlimited"]."</TimeLimitUnlimited>
<TimeLimitMessage>0</TimeLimitMessage>
<ApproveDate>".$user_data["approve_date"]."</ApproveDate>
<AgreeDate>".$user_data["agree_date"]."</AgreeDate>";
        if (($user_data["user_skin"] != "") OR ($user_data["user_style"] != "")) {
            $usr_xml .= "<Look Skin=\"".$user_data["user_skin"]."\" Style=\"".$user_data["user_style"]."\"/>";
        }
        $usr_xml .= "<AuthMode type=\"".$user_data["auth_mode"]."\"/>
<ExternalAccount>".$user_data["external_account"]."</ExternalAccount>
</User>
</Users>";

        $param = [
                        'sid' => $this->getSID(),
                        'folder_id' => -1,
                        'usr_xml' => $usr_xml,
                        'conflict_role' => 1,
                        'send_account_mail' => 0
        ];
        $result = $this->call('importUsers', $param);

        $s = simplexml_load_string($result);

        if ((string)$s->rows->row->column[3] == "successful")
            return (string)$s->rows->row->column[0];
            else
                return false;
    }

 ///////////////////////////////////////////////////
    /**
     * copy object
     *
     * copy ilias-object
     * @access public
     * @param string source_id reference-id
     * @param string target_id reference-id
     * @return string result
     */
    function copyObject($source_id, $target_id)
    {
        $this->clearCache();
        $type = $object_data["type"];
        $title = $object_data["title"];
        $description = $object_data["description"];

        $xml = "<Settings source_id=\"$source_id\" target_id=\"$target_id\" default_action=\"COPY\"/>";

        $param = [
                        'sid' => $this->getSID(),
                        'xml' => $xml
        ];
        return $this->call('copyObject', $param);
    }

    /**
     * get structure
     *
     * returns structure for ilias content object
     * @access public
     * @param string ref_id reference id
     * @return array result
     */
    function getStructure($ref_id)
    {
        $param = [
                        'sid' => $this->getSID(),
                        'ref_id' => $ref_id
        ];
        $result = $this->call('getStructureObjects', $param);

        $structure = [];
        if ($result) {
            $s = simplexml_load_string($result);

            foreach ($s->StructureObjects->StructureObject as $object) {
                $structure[] = (string)$object->Title;
            }
        }
        return $structure;
    }

    /**
     * get path
     *
     * returns repository-path to ilias-object
     * @access public
     * @param string source_id reference-id
     * @param string target_id reference-id
     * @return string result
     */
    function getPath($ref_id)
    {
        $param = [
                        'sid' => $this->getSID(),
                        'ref_id' => $ref_id
        ];
        $result = $this->call('getPathForRefId', $param);

        if ($result) {
            $s = simplexml_load_string($result);

            foreach ($s->rows->row as $row) {
                $path[] = (string)$row->column[2];
            }
        }

        if (is_array($path)) {
            return implode($path, $this->separator_string);
        } else {
            return false;
        }
    }

    /**
     *
     * returns repository-path to ilias-object
     *
     * @access public
     * @param string source_id reference-id
     * @param string target_id reference-id
     * @return string result
     */
    function getRawPath($ref_id)
    {
        $param = [
                        'sid' => $this->getSID(),
                        'ref_id' => $ref_id
        ];
        $result = $this->call('getPathForRefId', $param);

        if ($result) {
            $s = simplexml_load_string($result);

            foreach ($s->rows->row as $row) {
                $path[] = (string)$row->column[0];
            }
        }

        if (is_array($path)) {
            return implode($path, '_');
        } else {
            return false;
        }
    }

    /**
     *
     * returns ILIAS-Server-Info
     *
     * @access public
     * @return string result
     */
    function getInstallationInfoXML()
    {
        $this->clearCache();
        $param = [
        ];
        $result = $this->call('getInstallationInfoXML', $param);
        if ($result) {
            $s = simplexml_load_string($result);
            $version_info = (string)$s[0]['version'];
            $version_parts = explode(' ', $version_info);
            $data['version'] = $version_parts[0];
            $data['version_date'] = $version_parts[1];
            foreach($s->Clients->Client as $client) {
                $data['clients'][] = (string)$client[0]['id'];
            }
        }
        return $data;
    }
    //////////////////////////////////////////

    /**
    * update user
    *
    * update user-data
    * @access public
    * @param array user_data user-data
    * @return string result
    */
/*    function updateUser($user_data)
    {
        $this->clearCache();
        $param = array(
            'sid' => $this->getSID(),
            'user_data' => $user_data
        );
        return $this->call('updateUser', $param); // returns boolean
    }
/**/
    /**
    * update password
    *
    * update password with given string and write it uncrypted to the ilias-database
    * @access public
    * @param string user_id user-id
    * @param string password password
    * @return string result
    */
/*    function updatePassword($user_id, $password)
    {
        $this->clearCache();
        $param = array(
            'sid'          => $this->getSID(),
            'user_id'      => $user_id,
            'new_password' => $password
        );
        return $this->call('updatePassword', $param); // returns boolean
    }
/**/
    /**
    * delete user
    *
    * deletes user-account
    * @access public
    * @param string user_id user-id
    * @return string result
    */
    function deleteUser($user_id)
    {
        $this->clearCache();
        $param = [
            'sid' => $this->getSID(),
            'user_id'         => $user_id
            ];
        return $this->call('deleteUser', $param);   // returns boolean
    }

////////////////////////////
// COURSE-FUNCTIONS //
//////////////////////////

    /**
    * is course member
    *
    * checks if user is course-member
    * @access public
    * @param string user_id user-id
    * @param string course_id course-id
    * @return boolean result
    */
    function isMember($user_id, $course_id)
    {
        $param = [
            'sid' => $this->getSID(),
            'course_id'         => $course_id,
            'user_id'         => $user_id
            ];
        $status = $this->call('isAssignedToCourse', $param);    // returns 0 if not assigned, 1 => course admin, 2 => course member or 3 => course tutor
        if ($status == 0)
            return false;
        else
            return true;
    }

    /**
    * add course member
    *
    * adds user to course
    * @access public
    * @param string user_id user-id
    * @param string type member-type (Admin, Tutor or Member)
    * @param string course_id course-id
    * @return boolean result
    */
    function addMember($user_id, $type, $course_id)
    {
        $this->clearCache();
        $param = [
            'sid' => $this->getSID(),
            'course_id'         => $course_id,
            'user_id'         => $user_id,
            'type'         => $type
            ];
        return $this->call('assignCourseMember', $param);
    }

    /**
     * add course
     *
     * adds course
     * @access public
     * @param array course_data course-data
     * @param string ref_id target-id
     * @return string course-id
     */
    function addCourse($course_data, $ref_id)
    {
        $this->clearCache();
        foreach($course_data as $key => $value) {
            $course_data[$key] = htmlReady($course_data[$key]);
        }

        $xml = $this->getCourseXML($course_data);
        $param = [
                        'sid' => $this->getSID(),
                        'target_id'         => $ref_id,
                        'crs_xml' => $xml
        ];
        $crs_id = $this->call('addCourse', $param);
        return $crs_id;
    }

    /**
     * add group
     *
     * adds group
     * @access public
     * @param array group_data group data
     * @param string ref_id target id
     * @return string group id
     */
    function addGroup($group_data, $ref_id)
    {
        $this->clearCache();
        foreach($group_data as $key => $value) {
            $group_data[$key] = htmlReady($group_data[$key]);
        }

        $xml = $this->getGroupXML($group_data);
        $param = [
                        'sid' => $this->getSID(),
                        'target_id' => $ref_id,
                        'group_xml' => $xml
        ];
        $group_id = $this->call('addGroup', $param);
        return $group_id;
    }

    /**
     * update group
     *
     * updates group
     * @access public
     * @param array group_data group data
     * @param string ref_id group id
     * @return string result
     */
    function updateGroup($group_data, $ref_id)
    {
        $this->clearCache();
        foreach($group_data as $key => $value) {
            $group_data[$key] = htmlReady($group_data[$key]);
        }

        $xml = $this->getGroupXML($group_data);
        $param = [
                        'sid' => $this->getSID(),
                        'ref_id' => $ref_id,
                        'xml' => $xml
        ];
        $result = $this->call('updateGroup', $param);
        return $result;
    }

    /**
     * assign group member
     *
     * assigns user to group
     * @access public
     * @param string group_id group id
     * @param string user_id user id
     * @param string type type
     */
    function assignGroupMember($group_id, $user_id, $type = "Member")
    {
        $this->clearCache();
        $param = [
                        'sid' => $this->getSID(),
                        'group_id' => $group_id,
                        'user_id' => $user_id,
                        'type' => $type
        ];
        return $this->call('assignGroupMember', $param);
    }

    /**
     * exclude group member
     *
     * removes user from group
     * @access public
     * @param string group_id group id
     * @param string user_id user id
     */
    function excludeGroupMember($group_id, $user_id)
    {
        $this->clearCache();
        $param = [
                        'sid' => $this->getSID(),
                        'group_id' => $group_id,
                        'user_id' => $user_id
        ];
        return $this->call('excludeGroupMember', $param);
    }

    /**
     * get group
     *
     * returns group xml
     * @access public
     * @param string group_id group id
     * @return string group xml
     */
    function getGroup($group_id)
    {
        $param = [
                        'sid' => $this->getSID(),
                        'ref_id' => $group_id
        ];
        $result = $this->call('getGroup', $param);
        if ($result) {
            $s = simplexml_load_string($result);
            $data['title'] = (string)$s->title;
            $data['members'] = [];
            foreach($s->member as $member) {
                $member_parts = explode('_usr_', (string)$member[0]['id']);
                $data['members'][] = $member_parts[1];
            }
        }
        return $data;
    }

    /**
    * get course-xml
    *
    * gets course xml-object for given course-data
    * @access public
    * @param array course_data course-data
    * @return string course-xml
    */
    function getCourseXML($course_data)
    {
    $crs_language = $course_data["language"];
    $crs_admin_id = $course_data["admin_id"];
    $crs_title = $course_data["title"];
    $crs_desc = $course_data["description"];

    $xml = "<!DOCTYPE Course SYSTEM \"http://www.ilias.uni-koeln.de/download/dtd/ilias_course_0_1.dtd\">
<Course>
  <MetaData>
    <General Structure=\"Hierarchical\">
      <Identifier Catalog=\"ILIAS\"/>
      <Title Language=\"$crs_language\">
      $crs_title
      </Title>
      <Language Language=\"$crs_language\"/>
      <Description Language=\"$crs_language\">
      $crs_desc
      </Description>
      <Keyword Language=\"$crs_language\">
      </Keyword>
    </General>
  </MetaData>
  <Admin id=\"$crs_admin_id\" notification=\"Yes\" passed=\"No\">
  </Admin>
  <Settings>
    <Availability>
      <Unlimited/>
    </Availability>
    <Syllabus>
    </Syllabus>
    <Contact>
      <Name>
      </Name>
      <Responsibility>
      </Responsibility>
      <Phone>
      </Phone>
      <Email>
      </Email>
      <Consultation>
      </Consultation>
    </Contact>
    <Registration registrationType=\"Password\" maxMembers=\"0\" notification=\"No\">
      <Disabled/>
    </Registration>
    <Sort type=\"Manual\"/>
    <Archive Access=\"Disabled\">
    </Archive>
  </Settings>
</Course>";
    return $xml;
    }


    /**
    * get group xml
    *
    * gets group xml object for given group data
    * @access public
    * @param array group_data group data
    * @return string group xml
    */
    function getGroupXML($group_data)
    {
        $xml = '<group '.($group_data['id'] ? 'id="'.$group_data['id'].'" ': '').'type="open">
    <title>'.$group_data['title'].'</title>
    <owner id="'.$group_data['owner'].'"/>
    <information/>
    <registration type="disabled" waitingList="No">
    <maxMembers enabled="No">0</maxMembers>
    <minMembers>0</minMembers>
    <WaitingListAutoFill>0</WaitingListAutoFill>
    <CancellationEnd/><mailMembersType>1</mailMembersType>
    </registration><Sort type="Inherit"/>
    <ContainerSettings>
    <ContainerSetting id="cont_auto_rate_new_obj">0</ContainerSetting>
    <ContainerSetting id="cont_badges">0</ContainerSetting>
    <ContainerSetting id="cont_show_calendar">1</ContainerSetting>
    <ContainerSetting id="cont_show_news">0</ContainerSetting>
    <ContainerSetting id="cont_skills">0</ContainerSetting>
    <ContainerSetting id="cont_tag_cloud">0</ContainerSetting>
    <ContainerSetting id="cont_use_news">0</ContainerSetting>
    <ContainerSetting id="news_timeline">0</ContainerSetting>
    <ContainerSetting id="news_timeline_incl_auto">0</ContainerSetting>
    <ContainerSetting id="news_timeline_landing_page">0</ContainerSetting>
    </ContainerSettings>
    </group>';
        return $xml;
    }
 /**/

    /**
    * check reference by title
    *
    * gets reference id by object id
    * @access public
    * @param string key keyword
    * @param string type object-type
    * @return string reference-id
    */
    function checkReferenceById($id)
    {
        $param = [
            'sid'          => $this->getSID(),
            'reference_id' => $id
        ];

        $result = $this->call('getObjectByReference', $param);
        if ($result != false)
        {
            // TODO: change to simple xml
            $objects = $this->parseXML($result);
            if(is_array($objects)){
                foreach($objects as $index => $object_data){
                    if(is_array($object_data['references'])){
                        foreach($object_data['references'] as $reference){
                            if($reference['ref_id'] == $id && $reference['accessInfo'] != 'object_deleted') return $object_data['obj_id'];
                        }
                    }
                }
            }
        }
        return false;
    }
}
