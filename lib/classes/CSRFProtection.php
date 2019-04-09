<?php
# Lifter010: DONE

/**
 * CSRFProtection.php - protect from request forgery
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      mlunzena@uos.de
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * To protect Stud.IP from forged request from other sites a security token is
 * generated and stored in the session and all forms (or rather POST request)
 * have to contain that token which is then compared on the server side to
 * verify the authenticity of the request. GET request are not checked as these
 * are assumed to be idempotent anyway.
 *
 * If a forgery is detected, an InvalidSecurityTokenException is thrown and a
 * log entry is recorded in the error log.
 *
 * The (form or request) parameter is named "security token". If you are
 * authoring an HTML form, you have to include this as an
 * input[@type=hidden] element. This is easily done by calling:
 *
 * \code
 * echo CSRFProtection::tokenTag();
 * \endcode
 *
 * Checking the token is implicitly done when calling #page_open in file
 * lib/phplib/page4.inc
 */
class CSRFProtection
{
    /**
     * The name of the parameter.
     */
    const TOKEN = 'security_token';


    /**
     * This checks the request and throws an InvalidSecurityTokenException if
     * fails to verify its authenticity.
     *
     * @throws MethodNotAllowed               The request has to be unsafe
     *                                        in terms of RFC 2616.
     * @throws InvalidSecurityTokenException  The request is invalid as the
     *                                        security token does not match.
     */
    static function verifyUnsafeRequest()
    {
        if (self::isSafeRequestMethod()) {
            throw new MethodNotAllowedException();
        }

        if (!Request::isXhr() && !self::checkSecurityToken()) {
            throw new InvalidSecurityTokenException();
        }
    }

    /**
     * @return boolean true if the request method is either GET or HEAD
     */
    private static function isSafeRequestMethod()
    {
        return in_array(Request::method(), ['GET', 'HEAD']);
    }

    /**
     * This checks the request and throws an InvalidSecurityTokenException if
     * fails to verify its authenticity.
     *
     * @throws InvalidSecurityTokenException  request is invalid
     */
    static function verifySecurityToken()
    {
        if (!self::verifyRequest()) {
            throw new InvalidSecurityTokenException();
        }
    }

    /**
     * This checks the request and returns either true or false. It is
     * implicitly called by CSRFProtection::verifySecurityToken() and
     * it should never be needed to call this.
     *
     * @returns boolean  returns true if the request is valid
     */
    static function verifyRequest()
    {
        return
            Request::isGet() ||
            Request::isXhr() ||
            self::checkSecurityToken();
    }

    /**
     * Verifies the equality of the request parameter "security_token" and
     * the token stored in the session.
     *
     * @return boolean  true if equal
     */
    static private function checkSecurityToken()
    {
        return isset($_POST[self::TOKEN]) && self::token() === $_POST[self::TOKEN]; 
    }

    /**
     * Returns the token stored in the session generating it first
     * if required.
     *
     * @return string  a base64 encoded string of 32 random bytes
     * @throws SessionRequiredException  there is no session to store the token in
     */
    static function token()
    {
        // w/o a session, throw an exception
        if (session_id() === '') {
            throw new SessionRequiredException();
        }

        // create a token, if there is none
        if (!isset($_SESSION[self::TOKEN])) {
            $_SESSION[self::TOKEN] = base64_encode(self::randomBytes(32));
        }

        return $_SESSION[self::TOKEN];
    }

    /**
     * Returns a snippet of HTML containing an input[@type=hidden] element
     * like this:
     *
     * \code
     * <input type="hidden" name="security_token" value="012345678901234567890123456789==">
     * \endcode
     *
     * @return string  the HTML snippet containing the input element
     */
    static function tokenTag()
    {
        return sprintf('<input type="hidden" name="%s" value="%s">',
                       self::TOKEN,
                       self::token()
        );
    }


    /**
     * Returns a string of highly randomized bytes (over the full 8-bit range).
     *
     * This function is better than simply calling mt_rand() or any other
     * built-in PHP function because it can return a long string of bytes
     * (compared to < 4 bytes normally from mt_rand()) and uses the best
     * available pseudo-random source.
     *
     * This function was copied from Drupal's includes/bootstrap.inc.
     *
     * @param integer $count The number of characters (bytes) to return in the string.
     */
    static private function randomBytes($count)
    {
        static $random_state, $bytes;

        // Initialize on the first call. The contents of $_SERVER includes a mix of
        // user-specific and system information that varies a little with each page.
        if (!isset($random_state)) {
            $random_state = print_r($_SERVER, TRUE);
            if (function_exists('getmypid')) {
                // Further initialize with the somewhat random PHP process ID.
                $random_state .= getmypid();
            }
            $bytes = '';
        }
        if (mb_strlen($bytes) < $count) {
            // /dev/urandom is available on many *nix systems and is considered the
            // best commonly available pseudo-random source.
            if ($fh = @fopen('/dev/urandom', 'rb')) {
                // PHP only performs buffered reads, so in reality it will always read
                // at least 4096 bytes. Thus, it costs nothing extra to read and store
                // that much so as to speed any additional invocations.
                $bytes .= fread($fh, max(4096, $count));
                fclose($fh);
            }
            // If /dev/urandom is not available or returns no bytes, this loop will
            // generate a good set of pseudo-random bytes on any system.
            // Note that it may be important that our $random_state is passed
            // through hash() prior to being rolled into $output, that the two hash()
            // invocations are different, and that the extra input into the first one -
            // the microtime() - is prepended rather than appended. This is to avoid
            // directly leaking $random_state via the $output stream, which could
            // allow for trivial prediction of further "random" numbers.
            while (mb_strlen($bytes) < $count) {
                $random_state = hash('sha256', microtime() . mt_rand() . $random_state);
                $bytes .= hash('sha256', mt_rand() . $random_state, TRUE);
            }
        }
        $output = mb_substr($bytes, 0, $count);
        $bytes = mb_substr($bytes, $count);
        return $output;
    }
}
