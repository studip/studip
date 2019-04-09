<?php
namespace RESTAPI;
use DBManager, PDO;

/**
 * REST API routing permissions
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or later
 * @since   Stud.IP 2.6
 */
class UserPermissions
{
    /**
     * Create a permission object (for a certain user).
     * Permissions object will be cached for each user.
     *
     * @param mixed $user_id Id of user (optional, defaults to global)
     * @return Permissions Returns permissions object
     */
    public static function get($user_id = null)
    {
        $user_id = $user_id ?: $GLOBALS['user']->id;
        
        static $cache = [];
        if (!isset($cache[$user_id])) {
            $cache[$user_id] = new self($user_id);
        }

        return $cache[$user_id];
    }

    private $user_id;
    private $permissions = [];

    /**
     * Creates the actual permission object (for a certain user).
     *
     * @param mixed $user_id Id of user (optional, defaults to global)
     */
    private function __construct($user_id = null)
    {
        $this->user_id = $user_id;

        // Init with global permissions
        $this->loadPermissions();
    }

    /**
     * Defines whether access if allowed for the current user to the
     * passed route via the passed method.
     *
     * @param String $route_id Route template (hash)
     * @param String $method   HTTP method
     * @param mixed  $granted  Granted state (PHP'ish boolean)
     * @param bool   $overwrite May values be overwritten
     * @return bool Indicates if value could be changed.
     */
    public function set($user_id, $granted = true)
    {
        $this->permissions[$user_id] = (bool)$granted;

        return $this;
    }

    /**
     * Loads permissions for passed user.
     *
     * @return UserPermissions Returns instance of self to allow chaining
     */
    protected function loadPermissions()
    {
        $query = "SELECT consumer_id, granted
                  FROM api_user_permissions
                  WHERE user_id = :user_id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $this->user_id);
        $statement->execute();
        $permissions = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Init with global permissions
        foreach ($permissions as $permission) {
            extract($permission);

            $this->set($consumer_id, $granted);
        }

        return $this;
    }

    /**
     * Checks if access to consumer is allowed for the current user.
     *
     * @param String $consumer_id  Id of the consumer
     * @return bool Indicates whether access is allowed
     */
    public function check($consumer_id)
    {
        return isset($this->permissions[$consumer_id])
            && $this->permissions[$consumer_id];
    }

    /**
     * Stores the set permissions.
     *
     * @return bool Returns true if permissions were stored successfully
     */
    public function store()
    {
        $result = true;

        $query = "INSERT INTO api_user_permissions (user_id, consumer_id, granted, mkdate, chdate)
                  VALUES (:user_id, :consumer_id, :granted, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
                  ON DUPLICATE KEY UPDATE granted = VALUES(granted),
                                          chdate = UNIX_TIMESTAMP()";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $this->user_id);
        $statement->bindParam(':consumer_id', $consumer_id);
        $statement->bindParam(':granted', $granted);

        foreach ($this->permissions as $consumer_id => $granted) {
            $granted = (int)!empty($granted);
            $result = $result && $statement->execute();
        }

        return $result;
    }
    
    /**
     * Get a list of all consumer the user has granted acces to.
     *
     * @return Array List of consumer objects
     */
    public function getConsumers()
    {
        $result = [];
        foreach ($this->permissions as $consumer_id => $granted) {
            if (!$granted) {
                continue;
            }
            $result[$consumer_id] = Consumer\Base::find($consumer_id);
        }
        return $result;
    }
}
