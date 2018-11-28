<?php
# Lifter010: DONE
/**
 * LoginException.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
*/

/**
 * Class LoginException
 * If this exception is thrown, the user will get the login-screen immediately.
 * If the user already was logged in he/she gets logged out and then to the login-screen.
 *
 *     if ($GLOBALS['user']->id === "nobody") {
 *         throw new LoginException();
 *     }
 */
class LoginException extends Exception
{
}
