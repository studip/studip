<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* extern.inc.php
*
*
*
*
* @author       Peter Thienel <pthienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       extern
* @package  extern.inc.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// extern.inc.php
//
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


$default = "";

// set up dummy user environment (but no session)
$user = new Seminar_User('nobody');
$auth = new Seminar_Default_Auth();
$perm = new Seminar_Perm();

// there is a page_url, switch to the sri-interface
$page_url = Request::quoted('page_url');
if ($page_url) {
    require "lib/extern/sri.inc.php";
    exit;
}

// set base url for URLHelper class
URLHelper::setBaseUrl($GLOBALS['ABSOLUTE_URI_STUDIP']);
$range_id = Request::option('range_id',Context::getId());
$module = Request::quoted('module');
$config_id = Request::option('config_id');
$global_id = Request::option('global_id');
// range_id and module are always necessary
if ($range_id && $module) {
    // $module = ucfirst(mb_strtolower($module));

    // Is it a valid module name?
    foreach ($GLOBALS['EXTERN_MODULE_TYPES'] as $module_type => $module_data) {
        if ($module_data["module"] == $module) {
            $type = $module_type;
            break;
        }
    }
    // Wrong module name!
    if (!$type) {
        echo $GLOBALS['EXTERN_ERROR_MESSAGE'];
        exit;
    }

    if ($config_name) {
        // check for valid configuration name and convert it into a config_id
        if (!$config_id = ExternConfig::GetConfigurationByName($range_id, $type, $config_name)) {
            echo $GLOBALS['EXTERN_ERROR_MESSAGE'];
            exit;
        }
    } elseif (empty($config_id)) {
        // check for standard configuration
        if ($id = ExternConfig::GetStandardConfiguration($range_id, $type)) {
            $config_id = $id;
        } else {
            if (Config::get()->EXTERN_ALLOW_ACCESS_WITHOUT_CONFIG) {
                // use default configuraion
                $default = 'DEFAULT';
                $config_id = '';
            } else {
                echo $GLOBALS['EXTERN_ERROR_MESSAGE'];
                exit;
            }
        }
    }
    // the module itself validates the rest
} else {
    // without a range_id and a module-name there's no chance to printout data
    // except an error message
    echo $GLOBALS['EXTERN_ERROR_MESSAGE'];
    exit;
}

// check for standard global configuration
if (empty($global_id) && ($global_configuration = ExternConfig::GetGlobalConfiguration($range_id))) {
    $global_id = $global_configuration;
}

// all parameters ok, instantiate module and print data
foreach ($GLOBALS['EXTERN_MODULE_TYPES'] as $type) {
    if ($type["module"] == $module) {
        $module_obj = ExternModule::GetInstance($range_id, $module, $config_id, $default, $global_id);
    }
}

// Workaround to include data in scripts
if ($incdata) {
    $module_obj->config->config["Main"]["incdata"] = 1;
}

$args = $module_obj->getArgs();
foreach ($args as $arg) {
    $arguments[$arg] = Request::quoted($arg);
}

// declare Stud.IP encoding by default
header('Content-Type: text/html; charset=utf-8');

if (Request::option('preview')) {
    $module_obj->printoutPreview();
} else {
    Metrics::increment('core.extern_page_request');
    $module_obj->printout($arguments);
}

?>
