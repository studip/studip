<?php
namespace RESTAPI\Consumer;
use StudipAuthAbstract, RESTAPI\RouterException;

/**
 * Basic HTTP Authentication consumer for the rest api
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or later
 * @since   Stud.IP 3.0
 */
class HTTP extends Base
{
    /**
     * Detects if a user is authenticated via basic http authentication.
     * The only supported authentication for now is via the url:
     *
     * http://username:password@host/path?query
     *
     * @param mixed $request_type Type of request (optional; defaults to any)
     * @return mixed Instance of self if authentication was detected, false
     *               otherwise
     * @throws RouterException if authentication fails
     * @todo Integrate and test HTTP_AUTHORIZATION header authentication
     */
    public static function detect($request_type = null)
    {
        if (isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])
            || (false && isset($_SERVER['HTTP_AUTHORIZATION'])))
        {
            $user_id = false;

            if (isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
                $username = $_SERVER['PHP_AUTH_USER'];
                $password = $_SERVER['PHP_AUTH_PW'];
            } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                list($username, $password) = explode(':', base64_decode(mb_substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
            }

            $check = StudipAuthAbstract::CheckAuthentication($username, $password);
            if (!$check['uid'] || $check['uid'] == 'nobody') {
                throw new RouterException(401, 'HTTP Authentication failed');
            }

            return new self(null, $check['uid']);
        }
        return false;
    }
}
