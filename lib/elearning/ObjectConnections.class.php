<?php
# Lifter002: DONE
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
* class to handle object connections
*
* This class contains methods to handle connections between stud.ip-objects and external content.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       ObjectConnections
* @package  ELearning-Interface
*/
class ObjectConnections
{
    var $id;
    var $object_connections;
    /**
    * constructor
    *
    * init class.
    * @access public
    * @param string $object_id object-id
    */
    function __construct($object_id = "")
    {
        $this->id = $object_id;
        if ($object_id != "")
            $this->readData();
    }

    /**
    * read object connections
    *
    * gets object connections from database
    * @access public
    */
    function readData()
    {
        global $ELEARNING_INTERFACE_MODULES;

        $this->object_connections = [];

        $query = "SELECT system_type, module_type, module_id, chdate
                  FROM object_contentmodules
                  WHERE object_id = ?
                  ORDER BY chdate DESC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->id]);

        $module_count = 0;
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            // show only connected modules with valid module-type
            if ($ELEARNING_INTERFACE_MODULES[$row['system_type']]['types'][$row['module_type']] == '') {
                continue;
            }
            $module_count += 1;
            $d_system_type = $row['system_type'];
            $d_module_type = $row['module_type'];
            $d_module_id   = $row['module_id'];

            $reference = $d_system_type . '_' . $d_module_type . '_' . $d_module_id;
            $this->object_connections[$reference]['cms']    = $d_system_type;
            $this->object_connections[$reference]['type']   = $d_module_type;
            $this->object_connections[$reference]['id']     = $d_module_id;
            $this->object_connections[$reference]['chdate'] = $row['chdate'];
        }

        if ($module_count == 0) {
            $this->object_connections = false;
        }
    }

    /**
    * get object connections
    *
    * returns object connections
    * @access public
    * @return array object connections
    */
    function getConnections()
    {
        return $this->object_connections;
    }

    /**
    * get connection-status
    *
    * returns true, if object has connections
    * @access public
    * @return boolean connection-status
    */
    function isConnected()
    {
        return (boolean) $this->object_connections;
    }

    /**
     * get connection-status
     *
     * returns true, if object has connections
     * @access public
     * @param string $object_id object-id (optional)
     * @return boolean connection-status
     */
    public static function isObjectConnected($object_id = null)
    {
        if (isset($object_id)) {
            $query = "SELECT 1 FROM object_contentmodules WHERE object_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$object_id]);
            return (bool)$statement->fetchColumn();
        }
    }

    /**
    * get module-id
    *
    * returns module-id of given connection
    * @access public
    * @param string $connection_object_id object-id
    * @param string $connection_module_type module-type
    * @param string $connection_cms system-type
    * @return string module-id
    */
    public static function getConnectionModuleId($connection_object_id, $connection_module_type, $connection_cms)
    {
        $query = "SELECT module_id
                  FROM object_contentmodules
                  WHERE object_id = ? AND system_type = ? AND module_type = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $connection_object_id,
            $connection_cms,
            $connection_module_type
        ]);
        return $statement->fetchColumn() ?: false;
    }

    /**
    * set connection
    *
    * sets connection with object
    * @access public
    * @param string $connection_object_id object-id
    * @param string $connection_module_id module-id
    * @param string $connection_module_type module-type
    * @param string $connection_cms system-type
    * @return boolean successful
    */
    public static function setConnection($connection_object_id, $connection_module_id, $connection_module_type, $connection_cms)
    {
        $query = "SELECT 1
                  FROM object_contentmodules
                  WHERE object_id = ? AND module_id = ? AND system_type = ?
                    AND module_type = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $connection_object_id,
            $connection_module_id,
            $connection_cms,
            $connection_module_type
        ]);
        $check = $statement->fetchColumn();

        if ($check) {
            $query = "UPDATE object_contentmodules
                      SET module_type = ?, chdate = UNIX_TIMESTAMP()
                      WHERE object_id = ? AND module_id = ? AND system_type = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $connection_module_type,
                $connection_object_id,
                $connection_module_id,
                $connection_cms
            ]);
        } else {
            $query = "INSERT INTO object_contentmodules
                        (object_id, module_id, system_type, module_type, mkdate, chdate)
                      VALUES (?, ?, ?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $connection_object_id,
                $connection_module_id,
                $connection_cms,
                $connection_module_type
            ]);
        }
        return true;
    }

    /**
    * unset connection
    *
    * deletes connection with object
    * @access public
    * @param string $connection_object_id object-id
    * @param string $connection_module_id module-id
    * @param string $connection_module_type module-type
    * @param string $connection_cms system-type
    * @return boolean successful
    */
    public static function unsetConnection($connection_object_id, $connection_module_id, $connection_module_type, $connection_cms)
    {
        $query = "SELECT 1
                  FROM object_contentmodules
                  WHERE object_id = ? AND module_id = ? AND system_type = ?
                    AND module_type = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $connection_object_id,
            $connection_module_id,
            $connection_cms,
            $connection_module_type
        ]);
        $check = $statement->fetchColumn();


        if ($check) {
            $query = "DELETE FROM object_contentmodules
                      WHERE object_id = ? AND module_id = ? AND system_type = ?
                        AND module_type = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $connection_object_id,
                $connection_module_id,
                $connection_cms,
                $connection_module_type
            ]);
            return true;
        }
        return false;
    }

    public static function GetConnectedSystems($object_id)
    {
        $query = "SELECT DISTINCT system_type
                  FROM object_contentmodules
                  WHERE object_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$object_id]);
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function DeleteAllConnections($object_id, $cms_type)
    {
        $query = "DELETE FROM object_contentmodules
                  WHERE object_id = ? AND system_type = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$object_id, $cms_type]);
        return $statement->rowCount();
    }
}
