<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * PmWikiContentModule.class.php - Provides access PmWiki Modules
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
*
* This class contains methods to handle PmWiki learning modules 
*
* @author   Marco Diedrich <mdiedric@uos.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       PmWikiContentModule
* @package  ELearning-Interface
*/

class PmWikiContentModule extends ContentModule
{

    /**
    * constructor
    *
    * init class. 
    * @access public
    * @param string $module_id module-id
    * @param string $module_type module-type
    * @param string $cms_type system-type
    */ 

    function __construct($module_id = "", $module_type, $cms_type)
    {
        parent::__construct($module_id, $module_type, $cms_type);
        $this->link = $GLOBALS['connected_cms'][$this->cms_type]->ABSOLUTE_PATH_ELEARNINGMODULES.$this->id."/";
        $this->client = WebserviceClient::instance( $this->link. '?' . 
                                                    $GLOBALS['ELEARNING_INTERFACE_MODULES'][$this->cms_type]['URL_PARAMS'], 
                                                    $GLOBALS['ELEARNING_INTERFACE_MODULES'][$this->cms_type]['WEBSERVICE_CLASS']);
    }

    /**
    * reads data for content module
    *
    */

    function readData()
    {
        global $connected_cms, $view, $search_key, $cms_select, $current_module;

        $args = [$GLOBALS['ELEARNING_INTERFACE_MODULES'][$this->cms_type]['soap_data']['api-key'], $this->id];

        $field_data = $connected_cms[$this->cms_type]->client->call('get_field_info', $args);

        $this->title = $field_data['field_title'];
        $this->authors = $field_data['field_author'];
        $this->chdate = $field_data['change_date'];

        $this->accepted_users = $field_data['field_accepted_users'];

        return false;
    }

    /**
    * get permission-status
    *
    * returns true, if operation is allowed
    * @access public
    * @param string $operation operation
    * @return boolean allowed
    */

    function isAllowed($operation)
    {
        global $connected_cms, $view, $search_key, $cms_select, $current_module;

        if (Config::get()->STUDIP_INSTALLATION_ID)
        {
            $username = Config::get()->STUDIP_INSTALLATION_ID."#".$GLOBALS['auth']->auth['uname'];
        } else
        {
            $username = $GLOBALS['auth']->auth['uname'];
        }

        $args = [$GLOBALS['ELEARNING_INTERFACE_MODULES'][$this->cms_type]['soap_data']['api-key'],$this->id, $username];

        $authorized = $connected_cms[$this->cms_type]->client->call('field_accessable_by_user', $args);

        if ($authorized)
        {
            return true;
        } else 
        {
            # old authorization
            if (is_array($this->accepted_users) && in_array($username, $this->accepted_users))
                return true;
            else
                return false;
        }

    }
}
