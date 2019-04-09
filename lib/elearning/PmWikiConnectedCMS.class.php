<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * PmWikiConnectedCMS.class.php - Provides search capabilities
 * to search WikiFarm
 *
 * Copyright (C) 2006 - Marco Diedrich (mdiedric@uos.de)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'clients/xml_rpc_webservice_client.php';
require_once 'clients/soap_webservice_client.php';
require_once 'clients/webservice_client.php';

/**
* main-class for connection to PmWiki
*
* This class contains the main methods of the elearning-interface to connect to PmWiki. Extends ConnectedCMS.
*
* @author   Marco Diedrich <mdiedric@uos.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       PmWikiConnectedCMS
* @package  ELearning-Interface
*/

class PmWikiConnectedCMS extends ConnectedCMS
{
    function __construct($cms)
    {
        parent::__construct($cms);
        $this->client = WebserviceClient::instance( $GLOBALS['ELEARNING_INTERFACE_MODULES'][$this->cms_type]['ABSOLUTE_PATH_SOAP'] .
                                                                                                '?' . $GLOBALS['ELEARNING_INTERFACE_MODULES'][$this->cms_type]['URL_PARAMS'],
                                                                                                $GLOBALS['ELEARNING_INTERFACE_MODULES'][$this->cms_type]['WEBSERVICE_CLASS']);

        $this->api_key = $GLOBALS['ELEARNING_INTERFACE_MODULES'][$this->cms_type]['soap_data']['api-key'];
    }
    
    function init($cms)
    {
        parent::init($cms);
        $this->field_script = $GLOBALS['ELEARNING_INTERFACE_MODULES'][$cms]["field_script"];
    }

    /**
    * search for content modules
    *
    * returns found content modules
    * @access public
    * @param string $key keyword
    * @return array list of content modules
    */

    function searchContentModules($key)
    {
        $fields_found = $this->client->call("search_content_modules", $args = [
                $GLOBALS['ELEARNING_INTERFACE_MODULES'][$this->cms_type]['soap_data']['api-key'], 
                $key]); 

        $result = [];

        foreach($fields_found as $field)
        {

            $result[$field['field_id']] = [    'ref_id'            => $field['field_id'], 
                                                                                        'type'              => $field['field_type'],
                                                                                        'obj_id'            => $field_id,
                                                                                        'create_date' => $field['create_date'],
                                                                                        'last_update' => $field['change_date'],
                                                                                        'title'             => $field['field_title'], 
                                                                                        'description' => $field['field_description']];
        }
        return $result;
    }

}
