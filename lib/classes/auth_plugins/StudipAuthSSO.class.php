<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
 * StudipAuthSSO.class.php - abstract base class for SSO auth plugins
 * Copyright (c) 2007  Elmar Ludwig, Universitaet Osnabrueck
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/*
 * Abstract base class for SSO authentication plugins.
 */
abstract class StudipAuthSSO extends StudipAuthAbstract
{
    /**
     * Return the current username.
     */
    abstract function getUser ();

    /**
     * Check whether this user can be authenticated. The default
     * implementation just checks whether $username is not empty.
     */
    function isAuthenticated ($username, $password)
    {
        return !empty($username);
    }

    /**
     * SSO auth plugins cannot determine if a username is used.
     */
    function isUsedUsername ($username)
    {
        return false;
    }
}
