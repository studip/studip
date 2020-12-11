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
    /**
     * Configures the model
     *
     * @param array $config
     */
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

    /**
     * Creates a new token.
     *
     * @param  integer $duration Lifetime of the token
     * @param  mixed   $user_id  Optional id of the user (defaults to current user)
     * @return string the token
     */
    public static function create($duration = 30, $user_id = null)
    {
        $token = new static();
        $token->user_id    = $user_id ?? $GLOBALS['user']->id;
        $token->expiration = strtotime("+{$duration} seconds");
        $token->store();

        return $token->token;
    }

    /**
     * Checks if a token is valid (for a given user).
     *
     * Based on the number of paremters this either returns the id of the user
     * the token belongs to or a boolean if the id of a user was already given.
     *
     * @param  string  $token   Token to check
     * @param  mixed   $user_id Optional id of a user
     * @return mixed  User id of none was given or boolean
     */
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
             : $token_user_id === ($user_id ?? $GLOBALS['user']->id);
    }

    /**
     * Returns whether the token is expired
     * @return boolean
     */
    public function isExpired()
    {
        return $this->expiration < time();
    }
}
