<?php

/**
 * PHPLib Sessions using PHP 4 build-in sessions and PHPLib storage container
 *
 * @copyright  (c) 1998,1999 NetUSE GmbH Boris Erdmann, Kristian Koehntopp,
 *             2000 Maxim Derkachev <kot@books.ru>,
 *             2000 Teodor Cimpoesu <teo@digiro.net>
 * @author     André Noack <noack@data-quest.de> Maxim Derkachev <kot@books.ru>,
 *               Teodor Cimpoesu <teo@digiro.net>,Ulf Wendel <uw@netuse.de>
 */
class Seminar_Session
{
    /**
     * Current session id.
     *
     * @var  string
     * @see  id(), Session()
     */
    private $id;


    /**
     * Current session name also cookie name
     *
     * @var  string
     * @see  name(), Session()
     */
    private $name;

    /**
     *
     * @var  string
     */
    private $cookie_path;

    /**
     * If set, the domain for which the session cookie is set.
     *
     * @var  string
     */
    private $cookie_domain;

    /**
     * If set, the domain for which the session cookie is set.
     *
     * @var  bool
     */
    private $cookie_secure = false;

    /**
     * If set, the domain for which the session cookie is set.
     *
     * @var  bool
     */
    private $cookie_httponly = true;

    /**
     * session storage module - user, files or mm
     *
     * @var  string
     */
    private $module = 'user';


    /**
     * where to save session files if module == files
     *
     * @var string
     */
    private $save_path;


    /**
     * Name of data storage container
     *
     * var string
     */
    private $that_class = 'CT_Sql';

    /**
     *
     * @var  object CT_*
     */
    private $that;

    /**
     * Purge all session data older than this.
     *
     * @var int
     */
    private $gc_time;


    /**
     * @var
     */
    private static $studipticket;
    /**
     * @var
     */
    private static $current_session_state;

    /**
     * Returns true, if the current session is valid and belongs to an
     * authenticated user. Does not start a session.
     *
     * @static
     * @return bool
     */
    public static function is_current_session_authenticated()
    {
        return self::get_current_session_state() == 'authenticated';
    }

    /**
     * Returns the state of the current session. Does not start a session.
     * possible return values:
     * 'authenticated' - session is valid and user is authenticated
     * 'nobody' - session is valid, but user is not authenticated
     * false - no valid session
     *
     * @static
     * @return string|false
     */
    public static function get_current_session_state()
    {

        if (!is_null(self::$current_session_state)) {
            return self::$current_session_state;
        }
        $state = false;
        if (is_object($GLOBALS['user'])) {
            $state = in_array($GLOBALS['user']->id, ['nobody', 'form']) ? 'nobody' : 'authenticated';
        } else {
            $sid = $_COOKIE[__CLASS__];
            if ($sid) {
                $session_vars = self::get_session_vars($sid);
                $session_auth = $session_vars['auth']->auth;
                if ($session_auth['uid'] && !in_array($session_auth['uid'], ['nobody', 'form'])) {
                    $state = 'authenticated';
                } else {
                    $state = in_array($session_auth['uid'], ['nobody', 'form']) ? 'nobody' : false;
                }
            }
        }
        return (self::$current_session_state = $state);
    }

    /**
     * returns a SessionDecoder object containing the session variables
     * for the given session id
     *
     * @static
     * @param string $sid a session id
     * @return SessionDecoder
     */
    public static function get_session_vars($sid)
    {
        $sess = $GLOBALS['sess'];
        if (!is_object($sess)) {
            $sess = new self();
        }
        $storage_class = $sess->that_class;
        $storage = new $storage_class();
        $storage->ac_start();
        return new SessionDecoder($storage->ac_get_value($sid));
    }

    /**
     * returns a random string token for XSRF prevention
     * the string is stored in the session
     *
     * @static
     * @return string
     */
    public static function get_ticket()
    {
        if (!self::$studipticket) {
            self::$studipticket = $_SESSION['last_ticket'] = md5(uniqid('studipticket', 1));
        }
        return self::$studipticket;
    }

    /**
     * checks the given string token against the one stored
     * in the session
     *
     * @static
     * @param string $studipticket
     * @return bool
     */
    public static function check_ticket($studipticket)
    {
        $check = (isset($_SESSION['last_ticket']) && $_SESSION['last_ticket'] == $studipticket);
        $_SESSION['last_ticket'] = null;
        return $check;
    }


    /**
     *
     */
    function __construct()
    {
        if (Config::get()->CACHING_ENABLE && $GLOBALS['CACHE_IS_SESSION_STORAGE']) {
            $this->that_class = 'CT_Cache';
        }
        $this->cookie_path = $this->cookie_path ?: $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'];
        $this->cookie_secure = $_SERVER['HTTPS'] === 'on';
        $this->name(get_class($this));
    }

    /**
     * Start a new session or recovers from an existing session
     *
     * @return boolean   session_start() return value
     * @access public
     */
    function start()
    {
        $this->set_container();
        session_set_cookie_params(
            0,
            implode('/', array_map('rawurlencode', explode('/', $this->cookie_path))),
            $this->cookie_domain,
            $this->cookie_secure,
            $this->cookie_httponly
        );
        session_cache_limiter("nocache");
        //check for illegal cookiename
        if (isset($_COOKIE[$this->name])) {
            if (mb_strlen($_COOKIE[$this->name]) != 32 || preg_match('/[^0-9a-f]+/', $_COOKIE[$this->name])) {
                session_id(md5(uniqid($this->name, 1)));
            }
        } else {
            session_id(md5(uniqid($this->name, 1)));
        }

        $ok = session_start();
        $this->id = session_id();
        return $ok;
    }

    /**
     * Sets or returns the name of the current session
     *
     * @param  string  If given, sets the session name
     * @return string  session_name() return value
     * @access public
     */
    function name($name = '')
    {
        if ($name) {
            $this->name = $name;
            $ok = session_name($name);
        } else {
            $ok = session_name();
        }
        return $ok;
    }

    /**
     * ?
     *
     */
    function set_container()
    {

        switch ($this->module) {
            case "user" :
                $name = $this->that_class;
                $this->that = new $name;
                $this->that->ac_start();
                // set custom session handlers
                session_set_save_handler([$this, 'open'],
                    [$this, 'close'],
                    [$this, 'thaw'],
                    [$this, 'freeze'],
                    [$this, 'del'],
                    [$this, 'gc']
                );
                break;

            case "mm":
                session_module_name('mm');
                break;

            case "files":
            default:
                if ($this->save_path) {
                    session_save_path($this->save_path);
                }
                session_module_name('files');
                break;
        }
    }

    /**
     * @param array $keep_session_vars
     */
    function regenerate_session_id($keep_session_vars = [])
    {
        $keep = [];
        if (is_array($_SESSION)) {
            foreach (array_keys($_SESSION) as $k) {
                if (in_array($k, $keep_session_vars)) {
                    $keep[$k] = $_SESSION[$k];
                }
            }
            $_SESSION = [];
        }
        $this->delete();
        $this->start();
        foreach ($keep_session_vars as $k) {
            $_SESSION[$k] = $keep[$k];
        }
    }

    /**
     * Delete the current session destroying all registered data.
     *
     * Note that it does more but the PHP 4 session_destroy it also
     * throws away a cookie is there's one.
     *
     * @return boolean session_destroy return value
     * @access public
     */
    function delete()
    {
        $cookie_params = session_get_cookie_params();
        setCookie(
            $this->name,
            '',
            0,
            implode('/', array_map('rawurlencode', explode('/', $cookie_params['path']))),
            $cookie_params['domain'],
            $cookie_params['secure'],
            $cookie_params['httponly']
        );
        $_COOKIE[$this->name] = "";
        $_SESSION = [];
        return session_destroy();
    }

    // the following functions used in session_set_save_handler

    /**
     * Open callback
     *
     */
    function open()
    {
        return true;
    }


    /**
     * Close callback
     *
     */
    function close()
    {
        return true;
    }


    /**
     * Delete callback
     */
    function del()
    {
        if ($this->module == 'user') {
            $this->that->ac_delete($this->id, $this->name);
        }
        return true;
    }


    /**
     * Write callback.
     *
     */
    function freeze($id = null, $sess_data = null)
    {
        if ($this->module == 'user') {
            if (!isset($sess_data)) {
                $sess_data = session_encode();
            }
            $r = $this->that->ac_store($this->id, $this->name, $sess_data);
            if (!$r) {
                $this->that->ac_halt("Session: freeze() failed.");
            }
        }
        return true;
    }

    /**
     * Read callback.
     */
    function thaw()
    {
        if ($this->module == 'user') {
            return $this->that->ac_get_value(session_id(), $this->name) ?: '';
        }
        return '';
    }

    /**
     * @return bool
     */
    function gc()
    {
        if ($this->module == 'user') {
            //bail out if cronjob activated and not called in cli context
            if (Config::getInstance()->getValue('CRONJOBS_ENABLE')
                && ($task = array_pop(CronjobTask::findByClass('SessionGcJob')))
                && count($task->schedules->findBy('active', 1))
                && PHP_SAPI !== 'cli'
            ) {
                return false;
            }
            if (empty($this->gc_time)) {
                $this->gc_time = ini_get("session.gc_maxlifetime");
            }
            return (bool)$this->that->ac_gc($this->gc_time, $this->name);
        }
        return true;
    }
}
