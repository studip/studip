<?php
/**
 * Token.php - Token class
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author    Marco Diedrich <mdiedric@uos.de>
 * @license   GPL2 or any later version
 */
class Token extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'user_token';

        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id',
        ];

        // Create new token and ensure token is unique upon store
        $config['registered_callbacks']['before_create'][] = function ($object) {
            do {
                $token = md5(uniqid(__CLASS__, true));
            } while (Token::exists($token));

            $object->token = $token;
        };

        // Ensure tokens are not changed
        $config['registered_callbacks']['before_store'][] = function ($object) {
            if (!$object->isNew() && $object->isFieldDirty('token')) {
                return false;
            }
        };

        parent::configure($config);
    }

    public static function create($duration = 30, $user_id = null)
    {
        $token = new static();
        $token->user_id    = $user_id ?: $GLOBALS['user']->id;
        $token->expiration = strtotime("+{$duration} seconds");
        $token->store();

        return $token->token;
    }

    public static function isValid($token, $user_id = null)
    {
        $token = static::find($token);

        // No db entry for token
        if (!$token || $token->isExpired()) {
            return null;
        }

        // Token is valid
        $token_user_id = $token->user_id;
        $token->delete();

        return func_num_args() === 1
             ? $token_user_id
             : $token_user_id === ($user_id ?: $GLOBALS['user']->id);
    }

    /**
     * Compatbility method for legacy plugins.
     * @param  string  $token Token to check
     * @return mixed used_id if valid, null otherwise
     * @todo Remove for Stud.IP 5.0
     * @deprecated
     */
    public static function is_valid($token)
    {
        return self::isValid($token);
    }

    /**
     * Compatbility method for legacy plugins.
     * @param  string  $user_id owner of this token
     * @param  string  $duration validity duration
     * @todo Remove for Stud.IP 5.0
     * @deprecated
     */
    public function __construct($user_id = null, $duration = 30)
    {
        parent::__construct($user_id);

        // assume user id if no token with this id exists
        if (isset($user_id) && $this->isNew()) {
            $this->user_id    = $user_id;
            $this->expiration = strtotime("+{$duration} seconds");
            $this->store();
        }
    }

    /**
     * Compatbility method for legacy plugins.
     * @return string token value
     * @todo Remove for Stud.IP 5.0
     * @deprecated
     */
    public function get_token()
    {
        return $this->token;
    }

    public function isExpired()
    {
        return $this->expiration < time();
    }
}
