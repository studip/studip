<?php
namespace RESTAPI\Consumer;
use StudipAutoloader, DBManager, OAuthRequestVerifier, OAuthStore, OAuthServer, Exception;
use \RESTAPI\UserPermissions;

StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . DIRECTORY_SEPARATOR . 'vendor/oauth-php/library/');

/**
 * OAuth consumer for the rest api
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or later
 * @since   Stud.IP 3.0
 */
class OAuth extends Base
{
    /**
     * Configures the model.
     *
     * @param array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['default_values']['consumer_type'] = 'oauth';

        parent::configure($config);
    }

    /**
     * Detects whether the request is authenticated via OAuth.
     *
     * @param mixed $request_type Type of request (optional; defaults to any)
     * @return mixed Instance of self if authentication was detected, false
     *               otherwise
     */
    public static function detect($request_type = null)
    {
        if (OAuthRequestVerifier::requestIsSigned() && $request_type !== 'request') {
            $user_id = false;

            $parameters = (in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST']))
                        ? null
                        : $GLOBALS['_' . $_SERVER['REQUEST_METHOD']];

            $req = new OAuthRequestVerifier(null, null, $parameters);

            // Check oauth timestamp and deny access if timestamp is outdated
            if ($req->getParam('oauth_timestamp') < strtotime('-6 hours')) {
                return false;
            }
            $result = $req->verifyExtended('access');

            // @todo
            # self::$consumer_key = $result['consumer_key'];

            $query = "SELECT user_id FROM api_oauth_user_mapping WHERE oauth_id = :oauth_id";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':oauth_id', $result['user_id']);
            $statement->execute();
            $user_id = $statement->fetchColumn();

            if (!$user_id) {
                return false;
            }

            $consumer = reset(self::findByAuth_Key($result['consumer_key']));
            $consumer->setUser($user_id);
            return $consumer;
        } else {
            try {
                // Check if there is a valid request token in the current request
                // Returns an array with the consumer key, consumer secret, token, token secret and token type.
                $rs = self::getServer()->authorizeVerify();

                $query = "SELECT consumer_id
                          FROM api_consumers
                          WHERE auth_key = :key";
                $statement = DBManager::get()->prepare($query);
                $statement->bindValue(':key', $rs['consumer_key']);
                $statement->execute();
                $id = $statement->fetchColumn();

                if ($id) {
                    return new self($id);
                }
            } catch (Exception $e) {
            }
        }
        return false;
    }

    /**
     * Returns a singleton instance of the oauth server.
     *
     * @return OAuthServer The server object
     */
    public static function getServer()
    {
        static $server = null;
        if ($server === null) {
            $server = new OAuthServer(null, null, null, 'SESSION', [], [
                'allowed_uri_schemes' => []
            ]);
        }
        return $server;
    }

    /**
     * SimpleORMap constructor, registers neccessary callbacks.
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->registerCallback('before_store', 'before_store');
    }

    /**
     * "Before store" trigger. Creates a clone of the consumer in the
     * tables for the vendor oauth library.
     */
    protected function before_store()
    {
        static $mapping = [
            'auth_key'    => 'consumer_key',
            'auth_secret' => 'consumer_secret',
            'active'      => 'enabled',
            'contact'     => 'requester_name',
            'email'       => 'requester_email',
            'callback'    => 'callback_uri',
            'url'         => 'application_uri',
            'title'       => 'application_title',
            'description' => 'application_descr',
            'notes'       => 'application_notes',
            'type'        => 'application_type',
            'commercial'  => 'application_commercial',
        ];

        $consumer = [];
        foreach ($mapping as $from => $to) {
            $consumer[$to] = $this->$from;
        }

        $query = "SELECT osr_id
                  FROM oauth_server_registry
                  WHERE osr_consumer_key = :key AND osr_consumer_secret = :secret";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':key', $this->auth_key);
        $statement->bindValue(':secret', $this->auth_secret);
        $statement->execute();
        $consumer['id'] = $statement->fetchColumn();

        $consumer_key = OAuthStore::instance('PDO')->updateConsumer($consumer, null, true);

        if ($this->isNew()) {
            $consumer = OAuthStore::instance('PDO')->getConsumer($consumer_key, null, true);
            $this->auth_key    = $consumer['consumer_key'];
            $this->auth_secret = $consumer['consumer_secret'];
        }
    }

    /**
     * Grant oauth access for a user.
     *
     * @param mixed $user_id Specific user id or null to default to the
     *                       injected user
     * @throws Exception If no valid user is present
     */
    public function grantAccess($user_id = null)
    {
        if ($user_id === null && $this->hasUser()) {
            $user_id = $this->user->id;
        }
        if (!$user_id) {
            throw new Exception('Can not grant access to unknown user');
        }

        UserPermissions::get($GLOBALS['user']->id)->set($this->id, true)->store();
        return self::getServer()->authorizeFinish(true, self::getOAuthId($user_id));
    }

    /**
     * Revoke oauth access from a user.
     *
     * @param mixed $user_id Specific user id or null to default to the
     *                       injected user
     * @throws Exception If no valid user is present
     */
    public function revokeAccess($user_id = null)
    {
        if ($user_id === null && $this->hasUser()) {
            $user_id = $this->user->id;
        }
        if (!$user_id) {
            throw new Exception('Can not revoke access from unknown user');
        }

        $query = "DELETE oauth_server_token
                  FROM oauth_server_token
                  JOIN oauth_server_registry
                  WHERE ost_usa_id_ref = :id AND osr_consumer_key = :key AND osr_consumer_secret = :secret";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', self::getOAuthId($user_id));
        $statement->bindValue(':key', $this->auth_key);
        $statement->bindValue(':secret', $this->auth_secret);
        $statement->execute();

        UserPermissions::get($GLOBALS['user']->id)->set($this->id, false)->store();
        return self::getServer()->authorizeFinish(false, self::getOAuthId($user_id));
    }

    /**
     * Maps a user to an oauth id. This is neccessary due to the fact that
     * the oauth lib works with different ids than Stud.IP.
     *
     * @param String $user_id Id of the user to get an oauth id for
     * @return String The mapped oauth id
     */
    public static function getOAuthId($user_id)
    {
        $query = "SELECT oauth_id FROM api_oauth_user_mapping WHERE user_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $user_id);
        $statement->execute();
        $oauth_id = $statement->fetchColumn();

        if (!$oauth_id) {
            $query = "INSERT INTO api_oauth_user_mapping (user_id, mkdate)
                      VALUES (:id, UNIX_TIMESTAMP())";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':id', $user_id);
            $statement->execute();
            $oauth_id = DBManager::get()->lastInsertId();
        }

        return $oauth_id;
    }
}
