<?php
/*
 * studip_session.php - base class for session/authorization infos
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/webservices/api/studip_seminar.php';

class StudipSessionHelper
{
    function is_session_valid($session_id)
    {
        $session_id = preg_replace('/\W/', '', $session_id);

        return Token::isValid($session_id);
    }

    function get_session_user_id($session_id)
    {
        $session_id = preg_replace('/\W/', '', $session_id);

        return Token::isValid($session_id);
    }

    function get_session_username($session_id)
    {
        $session_id = preg_replace('/\W/', '', $session_id);
        $user_id = Token::isValid($session_id);

        if (!empty($user_id)) {
            return get_username($user_id);
        } else {
            return null;
        }
    }
}
