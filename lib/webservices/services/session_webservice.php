<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * session_webservice.php - Provides webservices for infos about
 *  authorization
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once('lib/webservices/api/studip_session.php');

class SessionService extends AccessControlledService
{
    function __construct()
    {
    $this->add_api_method('is_session_valid',
                          ['string', 'string'],
                          'bool',
                          'checks if session-id is valid');

    $this->add_api_method('get_session_username',
                          ['string', 'string'],
                          'string',
                          'returns username for session-id');

    $this->add_api_method('get_prefixed_session_username',
                          ['string', 'string'],
                          'string',
                          'returns prefixed username for session-id');
    }

    function is_session_valid_action($api_key, $session_id)
    {
        return StudipSessionHelper::is_session_valid($session_id);
    }

    function get_session_username_action($api_key, $session_id)
    {
        return StudipSessionHelper::get_session_username($session_id);
    }

    function get_prefixed_session_username_action($api_key, $session_id)
    {
        if (Config::get()->STUDIP_INSTALLATION_ID)
        {
            $prefix = Config::get()->STUDIP_INSTALLATION_ID;
        } else
        {
            $prefix = $GLOBALS['HTTP_SERVER']['HTTP_HOST'];
        }
            return $prefix."#".StudipSessionHelper::get_session_username($session_id);
    }
}
