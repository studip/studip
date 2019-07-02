<?php
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;

/**
 * Model for a two factor authentication secret.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 4.4
 */
class TFASecret extends SimpleORMap
{
    // Possible authentication types (email may require more tokens in a short
    // period of time with a larger window to accept them).

    // TODO: Reactivate when we actually can use PHP7
    // const TYPES = [
    //     'email' => [
    //         'window' => 30,
    //         'period' => 1,
    //     ],
    //     'app' => [
    //         'window' => 1,
    //         'period' => 30,
    //     ],
    // ];
    private static $types = [
        'email' => [
            'window' => 30,
            'period' => 1,
        ],
        'app' => [
            'window' => 1,
            'period' => 30,
        ],
    ];

    /**
     * Configures the model.
     *
     * @param  array  $config Configuration
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'users_tfa';

        $config['belongs_to']['user'] = [
            'class_name' => User::class,
        ];
        $config['has_many']['tokens'] = [
            'class_name' => TFAToken::class,
            'on_delete'  => 'delete',
        ];

        parent::configure($config);
    }

    /**
     * Overwrites the SORM setNew() method. This will create the secret string.
     *
     * @param boolean $is_new State of "new"
     * @todo is there a more sorm way by using registered_callbacks?
     */
    public function setNew($is_new)
    {
        if ($is_new) {
            if (!$this->isNew()) {
                return;
            }
            $this->secret    = TOTP::create()->getSecret();
            $this->confirmed = false;
        }

        return parent::setNew($is_new);
    }

    /**
     * Overwrite the restore method. This ensures access to token is only valid
     * by root accounts and the owner of the secret.
     *
     * @return mixed
     * @todo is there a more sorm way by using registered_callbacks?
     */
    public function restore()
    {
        $result = parent::restore();
        if ($result && !$this->mayAccess()) {
            throw new AccessDeniedException('You are not allowed to access this secret');
        }
        return $result;
    }

    /**
     * Returns whether the current user may access this object.
     *
     * @return bool
     */
    private function mayAccess()
    {
        return $this->user_id
            && (
                $this->user_id === User::findCurrent()->id
                || $GLOBALS['user']->perms === 'root'
            );
    }

    /**
     * Returns a token for a given timeslice.
     *
     * @param  int $timestamp Timeslice (optional, defaults to now)
     * @return string token
     */
    public function getToken($timestamp = null)
    {
        return $this->getTOTP($this->secret)->at($timestamp ?: time());
    }

    /**
     * Validates a 2fa token against the secret. This will create the token
     * again on server side and checks if it matches.
     *
     * Tokens may be reused if you allow it. This is used for validation tokens
     * stored in a cookie or session. If tokens are not allowed to be reused,
     * they are stored in the database to prevent replay attacks.
     *
     * @param  string  $token       Token to check
     * @param  int     $timestamp   Timeslice for the token (optional, defaults
     *                              to now)
     * @param  boolean $allow_reuse Allow reuse of the token
     *
     * @return bool
     */
    public function validateToken($token, $timestamp = null, $allow_reuse = false)
    {
        if (!$token || !ctype_digit($token)) {
            return false;
        }

        if (!$allow_reuse && TFAToken::exists([$this->user_id, $token])) {
            return false;
        }

        $window = self::$types[$this->type]['window'];
        if ($allow_reuse) {
            $window = 0;
        }

        if ($this->getTOTP()->verify($token, $timestamp, $window)) {
            if (!$this->confirmed) {
                $this->confirmed = true;
                $this->store();
            }

            if (!$allow_reuse) {
                TFAToken::create([
                    'user_id' => $this->user_id,
                    'token'   => $token,
                ]);
            }

            return true;
        }

        return false;
    }

    /**
     * Returns a totp object used for validation/creation of tokens.
     * @return TOTP
     */
    private function getTOTP()
    {
        return TOTP::create($this->secret, self::$types[$this->type]['period']);
    }

    /**
     * Returns the provisioning uri for this secret. Used in the qr code for
     * apps.
     * @return string
     */
    public function getProvisioningUri()
    {
        $totp = $this->getTOTP();
        $totp->setLabel($this->user->email);
        $totp->setIssuer(Config::get()->UNI_NAME_CLEAN);
        return $totp->getProvisioningUri();
    }
}
