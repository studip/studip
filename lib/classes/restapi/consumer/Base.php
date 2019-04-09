<?php
namespace RESTAPI\Consumer;

use AuthUserMd5;
use DBManager;
use DBManagerException;
use PDO;

/**
 * Base consumer class for the rest api
 *
 * Consumers provide means for authenticating a user and the access
 * permissions for routes are bound to specific consumers.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or later
 * @since   Stud.IP 3.0
 */
abstract class Base extends \SimpleORMap
{
    /**
     * Each consumer type has to implement a detect feature which
     * should extract crucial information from the request and return
     * an instance of itself if the consumer detects a valid signature
     * it can respond to.
     *
     * @param mixed $request_type Type of request (optional; defaults to any)
     * @return mixed Detected consumer object or false
     */
    abstract public static function detect($request_type = null);

    /* Concrete */

    /**
     * Configures the model.
     *
     * @param array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'api_consumers';

        parent::configure($config);
    }

    /**
     * Stores all known consumer types
     */
    protected static $known_types = [];

    /**
     * Add a consumer type to the list of consumer types
     *
     * @param String $type  Name of the type
     * @param String $class Associated consumer class
     */
    public static function addType($type, $class)
    {
        self::$known_types[$type] = $class;
    }

    /**
     * Removes a consumer type from the list of consumer types
     *
     * @param String $type Name of the type
     */
    public static function removeType($type)
    {
        unset(self::$known_types[$type]);
    }

    /**
     * Overloaded find method. Will return a concrete specialized consumer
     * object of the associated type.
     *
     * @param String $id Id of the consumer
     * @return RESTAPI\Consumer\Base Associated consumer object (derived
     *                               from consumer base type)
     * @throws Exception if either consumer id or consumer type is invalid
     */
    public static function find($id)
    {
        $query = "SELECT consumer_type
                  FROM api_consumers
                  WHERE consumer_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $id);
        $statement->execute();
        $type = $statement->fetchColumn();

        if (!isset(self::$known_types[$type])) {
            throw new \Exception('Consumer #' . $id . ' is of unknown type "' . $type . '"');
        }

        return new self::$known_types[$type]($id);
    }

    /**
     * Returns a list of all known consumers.
     *
     * @return Array List of all known consumers (as specialized consumer
     *               objects)
     */
    public static function findAll()
    {
        $query = "SELECT consumer_id FROM api_consumers";
        $statement = DBManager::get()->query($query);
        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        return array_map('self::find', $ids);
    }

    /**
     * Creates a new consumer of the given type.
     *
     * @param String $type Name of the type
     * @return RESTAPI\Consumer\Base Consumer object of the given (derived
     *                               from consumer base type)
     * @throws Exception if type is invalid
     */
    public static function create($type)
    {
        if (!isset(self::$known_types[$type])) {
            throw new Exception('Consumer is of unknown type "' . $type . '"');
        }

        return new self::$known_types[$type];
    }

    /**
     * This method is used to detect a consumer (of a specific type) by
     * executing the detect method on all known consumer types.
     *
     * @param mixed $type Name of the type (optional; defaults to all types)
     * @param mixed $request_type Type of request (optional; defaults to any)
     * @return mixed Either the detected consumer or false if no consumer
     *               was detected
     * @throws Exception if type is invalid
     */
    public static function detectConsumer($type = null, $request_type = null)
    {
        $needles = $type === null
                 ? array_keys(self::$known_types)
                 : [$type];
        foreach ($needles as $needle) {
            if (!isset(self::$known_types)) {
                throw new Exception('Trying to detect consumer of unkown type "' . $needle . '"');
            }
            $consumer_class = self::$known_types[$needle];
            if ($consumer = $consumer_class::detect($request_type)) {
                return $consumer;
            }
        }
        return false;
    }

    /**
     * Contains user information
     */
    protected $user = null;

    /**
     * Extended SimpleORMap constructor. A certain user can be injected upon
     * creation.
     *
     * @param mixed $id Id of the consumer or null to create a new one
     * @param mixed $user Either a user object or id to inject to the consumer
     *                    or null if no user should be injected
     */
    public function __construct($id = null, $user = null)
    {
        parent::__construct($id);

        if ($user !== null) {
            $this->setUser($user);
        }
    }

    /**
     * Retrieve the api permissions associated with this consumer.
     *
     * @return RESTAPI\ConsumerPermissions Permission object for this consumer
     */
    public function getPermissions()
    {
        return new RESTAPI\ConsumerPermissions($this->id);
    }

    /**
     * Inject a user to this consumer. Injecting in this context refers to
     * "having a user authenticated by this consumer".
     *
     * @param mixed $user Either a user object or a user id
     * @return RESTAPI\Consumer\Base Returns instance of self to allow
     *                               chaining
     */
    public function setUser($user)
    {
        if (!is_object($user)) {
            $user = AuthUserMd5::find($user);
        }
        $this->user = $user;
        return $this;
    }

    /**
     * Returns whether the consumer has an injected user or not.
     *
     * @return bool True if a valid user is found, false otherwise
     */
    public function hasUser()
    {
        return $this->user !== null && $this->user->id && $this->user->id !== 'nobody';
    }

    /**
     * Return the injected user.
     *
     * @param mixed User object or false if no user was injected
     */
    public function getUser()
    {
        return $this->user;
    }
}
