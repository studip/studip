<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
* class to use ILIAS-3-Webservices
*
* This class contains methods to connect to the ILIAS-3-Soap-Server.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       Ilias3Soap
* @package  ELearning-Interface
*/
class Ilias3Soap extends StudipSoapClient
{
    var $cms_type;
    var $admin_sid;
    var $user_sid;
    var $user_type;
    var $soap_cache;

    /**
    * constructor
    *
    * init class.
    * @access
    * @param string $cms system-type
    */
    function __construct($cms)
    {
        global $ELEARNING_INTERFACE_MODULES, $connected_cms;
        $this->cms_type = $cms;

        parent::__construct($ELEARNING_INTERFACE_MODULES[$cms]["ABSOLUTE_PATH_SOAP"]);
        $this->user_type = "admin";

        $this->loadCacheData($cms);
        $this->caching_active = false;
    }



    /**
    * set usertype
    *
    * sets usertype fpr soap-calls
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
        if ($this->user_type == "admin")
        {
            if ($this->admin_sid == false)
                $this->login();
//          echo "a";
            return $this->admin_sid;
        }
        if ($this->user_type == "user")
        {
            if ($this->user_sid == false)
                $this->login();
//            echo "u";
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
        $index = md5($method . ":" . implode($params, "-"));
        // return false if no session_id is given
        if (($method != "login") AND ($params["sid"] == ""))
            return false;
//      echo $this->caching_active;
        if (($this->caching_active == true) AND (isset($this->soap_cache[$index])))
        {
//          echo $index;
//          echo " from Cache<br>";
            $result = $this->soap_cache[$index];
        }
        else
        {
            $result = $this->_call($method, $params);
            // if Session is expired, re-login and try again
            if (($method != "login") AND $this->soap_client->fault AND in_array(mb_strtolower($this->faultstring), ["session not valid","session invalid", "session idled"]) )
            {
//              echo "LOGIN AGAIN.";
                $caching_status = $this->caching_active;
                $this->caching_active = false;
                $params["sid"] = $this->login();
                $result = $this->_call($method, $params);
                $this->caching_active = $caching_status;
            }
            elseif (! $this->soap_client->fault)
                $this->soap_cache[$index] = $result;
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
    function loadCacheData($cms)
    {
        $this->soap_cache = (array)$_SESSION["cache_data"][$cms];
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
//      echo "SET:".$this->caching_active."<br>";
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
        $_SESSION["cache_data"][$this->cms_type] = [];

    }

    /**
    * save cache
    *
    * saves soap-cache in session-variable
    * @access public
    */
    function saveCacheData()
    {
       $_SESSION["cache_data"][$this->cms_type] = $this->soap_cache;

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
        $xml_parser = new Ilias3ObjectXMLParser($data);
        $xml_parser->startParsing();
        return $xml_parser->getObjectData();
    }

    /**
    * login
    *
    * login to soap-webservice
    * @access public
    * @return string result
    */
    function login()
    {
        global $ELEARNING_INTERFACE_MODULES, $connected_cms;
        if ($this->user_type == "admin")
            $param = [
                'client' => $ELEARNING_INTERFACE_MODULES[$this->cms_type]["soap_data"]["client"],
                'username' => $ELEARNING_INTERFACE_MODULES[$this->cms_type]["soap_data"]["username"],
                'password' => $ELEARNING_INTERFACE_MODULES[$this->cms_type]["soap_data"]["password"]
                ];
        elseif ($this->user_type == "user")
            $param = [
                'client' => $ELEARNING_INTERFACE_MODULES[$this->cms_type]["soap_data"]["client"],
                'username' => $connected_cms[$this->cms_type]->user->getUsername(),
                'password' => $connected_cms[$this->cms_type]->user->getPassword()
                ];
        $result = $this->call('login', $param);
        if ($this->user_type == "admin")
            $this->admin_sid = $result;
        if ($this->user_type == "user")
            $this->user_sid = $result;
//      if ($this->user_type == "user") echo "SID".$this->call('login', $param).$param["username"];
        return $result;
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


///////////////////////////
// OBJECT-FUNCTIONS //
//////////////////////////

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

            $objects = $this->parseXML($result);
            foreach($objects as $count => $object_data)
                if (is_array($object_data["references"]))
                {
                    foreach($object_data["references"] as $ref_data)
                        if ($ref_data["accessInfo"] != "object_deleted" && $ref == $ref_data["ref_id"])
                        {
                            $all_objects[$ref_data["ref_id"]] = $object_data;
                            unset($all_objects[$ref_data["ref_id"]]["references"]);
                            $all_objects[$ref_data["ref_id"]]["ref_id"] = $ref_data["ref_id"];
                            $all_objects[$ref_data["ref_id"]]["accessInfo"] = $ref_data["accessInfo"];
                            $all_objects[$ref_data["ref_id"]]["operations"] = $ref_data["operations"];
                        }
                }
            return $all_objects[$ref];
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
            $objects = $this->parseXML($result);
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
            $objects = $this->parseXML($result);
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
                        return $object_data["references"][0]["ref_id"];
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
        $param = [
            'sid' => $this->getSID(),
            'source_id' => $object_id,
            'target_id' => $ref_id
            ];
        return $this->call('addReference', $param);
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
         if ($user_id != "")
            $param["user_id"] = $user_id;
        $result = $this->call('getTreeChilds', $param);
        if ($result != false)
        {

            $objects = $this->parseXML($result);
            foreach($objects as $count => $object_data)
                if (is_array($object_data["references"]))
                    foreach($object_data["references"] as $ref_data)
                        if ($ref_data["accessInfo"] != "object_deleted")
                        {
                            $all_objects[$ref_data["ref_id"]] = $object_data;
//                          unset($all_objects[$ref_id]["references"]);
                            $all_objects[$ref_data["ref_id"]]["ref_id"] = $ref_data["ref_id"];
                            $all_objects[$ref_data["ref_id"]]["accessInfo"] = $ref_data["accessInfo"];
                            $all_objects[$ref_data["ref_id"]]["operations"] = $ref_data["operations"];
                        }
            if (sizeof($all_objects) > 0) {
                return $all_objects;
            } else {
                return [];
            }
        }
        return false;
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
            $objects = $this->parseXML($result);
            $roles = [];
            foreach ($objects as $count => $role)
                $roles[$count] = $role["obj_id"];
//          echo implode($roles, ".");
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
        $result = $this->call('getUser', $param); // returns user-data-array
        return $result;
    }

    /**
    * add user
    *
    * adds new user and sets role-id
    * @access public
    * @param array user_data user-data
    * @param string role_id global role-id for new user
    * @return string user-id
    */
    function addUser($user_data, $role_id)
    {
        $param = [
            'sid' => $this->getSID(),
            'user_data' => $user_data,
            'global_role_id' => $role_id
            ];
        return $this->call('addUser', $param); // returns user_id
    }

    /**
    * update user
    *
    * update user-data
    * @access public
    * @param array user_data user-data
    * @return string result
    */
    function updateUser($user_data)
    {
        $param = [
            'sid' => $this->getSID(),
            'user_data' => $user_data
        ];
        return $this->call('updateUser', $param); // returns boolean
    }

    /**
    * update password
    *
    * update password with given string and write it uncrypted to the ilias-database
    * @access public
    * @param string user_id user-id
    * @param string password password
    * @return string result
    */
    function updatePassword($user_id, $password)
    {
        $param = [
            'sid'          => $this->getSID(),
            'user_id'      => $user_id,
            'new_password' => $password
        ];
        return $this->call('updatePassword', $param); // returns boolean
    }

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
    * check reference by title
    *
    * gets reference-id by object-title
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
            $objects = $this->parseXML($result);
            //echo "<pre><hr>".print_r($objects,1);
            //echo "\n</pre><hr>";
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

