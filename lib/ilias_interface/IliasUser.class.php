<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
* class to handle user-accounts
*
* This class contains methods to handle connected user-accounts.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       ConnectedUser
* @package  ELearning-Interface
*/
class IliasUser
{
    const USER_TYPE_ORIGINAL= '1';
    const USER_TYPE_CREATED= '0';

    public $index;
    public $version;
    public $id;
    public $studip_id;
    public $studip_login;
    public $studip_password;
    public $login;
    public $external_password;
    public $category;
    public $gender;
    public $title_front;
    public $title_rear;
    public $title;
    public $firstname;
    public $lastname;
    public $institution;
    public $department;
    public $street;
    public $city;
    public $zipcode;
    public $country;
    public $phone_home;
    public $fax;
    public $matriculation;
    public $email;
    public $type;
    public $is_connected;

    public $db_class;
    /**
    * constructor
    *
    * init class. don't call directly, class is loaded by ConnectedIlias.
    * @access public
    * @param string $index ILIAS installation index
    */
    function __construct($index, $version, $user_id = false)
    {
        global $auth;

        $this->studip_id = $user_id ? $user_id : $GLOBALS['user']->id;
        $this->auth_plugin = DBManager::get()->query("SELECT IFNULL(auth_plugin, 'standard') FROM auth_user_md5 WHERE user_id = '" . $this->studip_id. "'")->fetchColumn();
        $this->index = $index;
        $this->version = $version;

        $this->readData();
        $this->getStudipUserData();
    }

    /**
    * get data
    *
    * gets data from database
    * @access public
    * @return boolean returns false, if no data was found
    */
    function readData()
    {
        $query = "SELECT external_user_id, external_user_name, external_user_password, external_user_category, external_user_type
                  FROM auth_extern
                  WHERE studip_user_id = ? AND external_user_system_type = ? ORDER BY external_user_type DESC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->studip_id, $this->index]);
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            $this->id = '';
            $this->is_connected = false;
            return false;
        }

        $this->id                = $data['external_user_id'];
        $this->login             = $data['external_user_name'];
        $this->external_password = $data['external_user_password'];
        $this->category          = $data['external_user_category'];
        $this->type              = $data['external_user_type'];
        $this->is_connected      = true;
    }

    /**
    * get stud.ip-user-data
    *
    * gets stud.ip-user-data from database
    * @access public
    * @return boolean returns false, if no data was found
    */
    function getStudipUserData()
    {
        $query = "SELECT username, password, title_front, title_rear, Vorname, 
                         Nachname, Email, privatnr, privadr, geschlecht
                  FROM auth_user_md5
                  LEFT JOIN  user_info USING (user_id)
                  WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->studip_id]);
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return false;
        }

        $this->studip_login    = $data['username'];
        $this->studip_password = $data['password'];
        $this->title_front     = $data['title_front'];
        $this->title_rear      = $data['title_rear'];
        $this->firstname       = $data['Vorname'];
        $this->lastname        = $data['Nachname'];
        $this->email           = $data['Email'];
        $this->phone_home      = $data['privatnr'];
        $this->street          = $data['privadr'];
        switch($data['geschlecht']) {
            case 1: $this->gender   = 'm'; break;
            case 2: $this->gender   = 'f'; break;
            default: $this->gender  = 'f';
        }

        if ($this->title_front != '') {
            $this->title = $this->title_front;
        }
        if ($this->title_front != '' && $this->title_rear != '') {
            $this->title .= ' ';
        }
        if ($this->title_rear != '') {
            $this->title .= $this->title_rear;
        }
        return true;
    }

    /**
    * get array of user account data
    *
    * returns array of user account data
    * @access public
    * @return array user account data
    */
    function getUserArray()
    {
        // data for user-account in ILIAS
        $user_data["id"] = $this->id;
        $user_data["login"] = $this->studip_login;
        $user_data["passwd"] = $this->external_password;
        $user_data["firstname"] = $this->firstname;
        $user_data["lastname"] = $this->lastname;
        $user_data["title"] = $this->title;
        $user_data["gender"] = $this->gender;
        $user_data["email"] = $this->email;
        $user_data["street"] = $this->street;
        $user_data["phone_home"] = $this->phone_home;
        $user_data["time_limit_unlimited"] = 1;
        $user_data["active"] = 1;
        $user_data["approve_date"] = date('Y-m-d H:i:s');
        $user_data["accepted_agreement"] = true;
        $user_data["agree_date"] = date('Y-m-d H:i:s');
        return $user_data;
    }

    /**
    * get id
    *
    * returns id
    * @access public
    * @return string id
    */
    function getId()
    {
        return $this->id;
    }

    /**
     * set id
     *
     * returns id
     * @access public
     * @return string id
     */
    function setId($ilias_user_id)
    {
        $this->id = $ilias_user_id;
    }

    /**
    * get stud.ip user-id
    *
    * returns id
    * @access public
    * @return string stud.ip user-id
    */
    function getStudipId()
    {
        return $this->studip_id;
    }

    /**
    * get username
    *
    * returns username
    * @access public
    * @return string username
    */
    function getUsername()
    {
        return $this->login;
    }

    /**
    * set username
    *
    * sets username
    * @access public
    * @param string $user_login username
    */
    function setUsername($user_login)
    {
        $this->login = $user_login;
    }

    /**
    * get password
    *
    * returns password
    * @access public
    * @return string password
    */
    function getPassword()
    {
        return $this->external_password;
    }

    /**
    * set password
    *
    * sets password
    * @access public
    * @param string $user_password password
    */
    function setPassword($user_password)
    {
        $this->external_password = $user_password;
    }

    /**
    * get user category
    *
    * returns id
    * @access public
    * @return string id
    */
    function getCategory()
    {
        return $this->category;
    }

    /**
    * set user category
    *
    * sets user category
    * @access public
    * @param string $user_category category
    */
    function setCategory($user_category)
    {
        $this->category = $user_category;
    }

    /**
    * get gender
    *
    * returns gender-setting
    * @access public
    * @return string gender-setting
    */
    function getGender()
    {
        return $this->gender;
    }

    /**
    * set gender
    *
    * sets gender
    * @access public
    * @param string $user_gender gender-setting
    */
    function setGender($user_gender)
    {
        $this->gender = $user_gender;
    }

    /**
    * get full name
    *
    * returns full name
    * @access public
    * @return string name
    */
    function getName()
    {
        if ($this->title != "")
            return $this->title . ' ' . $this->firstname . ' ' . $this->lastname;
        else
            return $this->firstname . ' ' . $this->lastname;
    }

    /**
    * get firstname
    *
    * returns firstname
    * @access public
    * @return string firstname
    */
    function getFirstname()
    {
        return $this->firstname;
    }

    /**
    * set firstname
    *
    * sets firstname
    * @access public
    * @param string $user_firstname firstname
    */
    function setFirstname($user_firstname)
    {
        $this->firstname = $user_firstname;
    }

    /**
    * get lastname
    *
    * returns lastname
    * @access public
    * @return string lastname
    */
    function getLastname()
    {
        return $this->lastname;
    }

    /**
    * set lastname
    *
    * sets lastname
    * @access public
    * @param string $user_lastname lastname
    */
    function setLastname($user_lastname)
    {
        $this->lastname = $user_lastname;
    }

    /**
    * get email-adress
    *
    * returns email-adress
    * @access public
    * @return string email-adress
    */
    function getEmail()
    {
        return $this->email;
    }

    /**
    * set email-adress
    *
    * sets email-adress
    * @access public
    * @param string $user_email email-adress
    */
    function setEmail($user_email)
    {
        $this->email = $user_email;
    }

    /**
    * get user-type
    *
    * returns user-type
    * @access public
    * @return string user-type
    */
    function getUserType()
    {
        return $this->type;
    }

    /**
    * set user-type
    *
    * sets user-type
    * @access public
    * @param string $user_type user-type
    */
    function setUserType($user_type)
    {
        $this->type = $user_type;
    }

    /**
    * save connection for user-account
    *
    * saves user-connection to database and sets type for actual user
    * @access public
    * @param string $user_type user-type
    */
    function setConnection($user_type)
    {
        $this->setUserType($user_type);

        $query = "INSERT INTO auth_extern (studip_user_id, external_user_id, external_user_name, 
                                           external_user_password, external_user_category,
                                           external_user_system_type, external_user_type)
                  VALUES (?, ?, ?, ?, ?, ?, ?)
                  ON DUPLICATE KEY
                    UPDATE external_user_name = VALUES(external_user_name),
                           external_user_password = VALUES(external_user_password),
                           external_user_category = VALUES(external_user_category),
                           external_user_id = VALUES(external_user_id)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            (string)$this->studip_id,
            (string)$this->id,
            (string)$this->login,
            (string)$this->external_password,
            (string)$this->category,
            (string)$this->index,
            (int)$this->type,
        ]);

        $this->is_connected = true;
        $this->readData();
    }
    
    /**
     * remove connection for user-account
     *
     * deletes user-connection from database (only for manually connected user)
     * @access public
     */
    function unsetConnection()
    {
        if ($this->getUserType() != self::USER_TYPE_ORIGINAL) {
            return;
        }
        
        $query = "DELETE FROM auth_extern WHERE studip_user_id = ? AND external_user_system_type = ? AND external_user_type = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
                        (string)$this->studip_id,
                        (string)$this->index,
                        (int)self::USER_TYPE_ORIGINAL,
        ]);
        
        $this->is_connected = false;
        $this->readData();
    }
    
    /**
    * get connection-status
    *
    * returns true, if there is a connected user
    * @access public
    * @return boolean connection-status
    */
    function isConnected()
    {
        return $this->is_connected;
    }

    /**
     * get authentication token
     *
     * generates authentication token and updates auth_extern
     * @access public
     */
    function getToken()
    {
        $token = md5(uniqid("iliastoken538"));
        $query = "UPDATE `auth_extern` SET `external_user_token` = ?, `external_user_token_valid_until` = ? 
                            WHERE `auth_extern`.`studip_user_id` = ? AND `auth_extern`.`external_user_system_type` = ? AND `auth_extern`.`external_user_type` = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
                        $token,
                        time() + 600,
                        (string)$this->studip_id,
                        (string)$this->index,
                        (int)$this->type
        ]);
        return $token;
    }
}
?>
