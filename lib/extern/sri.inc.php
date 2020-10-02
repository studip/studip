<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* sri.inc.php
*
* The Stud.IP-remote-include interface to extern modules.
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       extern
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// sri.inc.php
// Stud.IP-remote-include interface to extern modules.
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

if (!Config::get()->EXTERN_SRI_ENABLE) {
    echo $EXTERN_ERROR_MESSAGE;
    exit;
}

if (!ini_get('allow_url_fopen')){
    @ini_set('allow_url_fopen','1');
}
// this script is included in extern.inc.php

$all_semester = SemesterData::getAllSemesterData();

if ($sri_file = @file($page_url))
    $sri_page = implode("", $sri_file);
else {
    echo $EXTERN_ERROR_MESSAGE;
    exit;
}

$sri_pattern = "'(.*)(\<studip_remote_include\>.*\<\/studip_remote_include\>)(.*)'is";

if (!preg_match($sri_pattern, $sri_page, $sri_matches)) {
    echo $EXTERN_ERROR_MESSAGE;
    exit;
}

$parser = xml_parser_create();
xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
xml_parse_into_struct($parser, $sri_matches[2], $xml_values, $xml_tags);

$allowed_xml_tags = ["module", "range", "config", "sem", "global"];

foreach ($allowed_xml_tags as $xml_tag) {
    if ($xml_tags[$xml_tag]) {
        $attributes = $xml_values[$xml_tags[$xml_tag][0]]["attributes"];
        foreach ($attributes as $attribute => $value) {
            $parameter_name = $xml_tag . "_" . $attribute;
            $$parameter_name = $value;
        }
    }
}

// check given data
// no range_id? sorry...
if (!$range_id) {
    echo $EXTERN_ERROR_MESSAGE;
    exit;
}

// Is it a valid module name?
reset($EXTERN_MODULE_TYPES);
foreach ($EXTERN_MODULE_TYPES as $module_type => $module_data) {
    if ($module_data["module"] == $module_name) {
        $type = $module_type;
        break;
    }
}
// Wrong module name!
if (!$type) {
    echo $EXTERN_ERROR_MESSAGE;
    exit;
}

// if there is no config_id or config_name, take the DEFAULT configuration
if ($config_name) {
    // check for valid configuration name and convert it into a config_id
    if (!$config_id = ExternConfig::GetConfigurationByName($range_id, $type, $config_name)) {
        echo $EXTERN_ERROR_MESSAGE;
        exit;
    }
}
elseif (!$config_id) {
    // check for standard configuration
    if ($id = ExternConfig::GetStandardConfiguration($range_id, $type)) {
        $config_id = $id;
    } else {
        if (Config::get()->EXTERN_ALLOW_ACCESS_WITHOUT_CONFIG) {
            // use default configuraion
            $default = 'DEFAULT';
            $config_id = '';
        } else {
            echo $EXTERN_ERROR_MESSAGE;
            exit;
        }
    }
}

// if there is no global_id or global_name, take the DEFAULT global configuration
if ($global_name) {
    // check for valid configuration name and convert it into a config_id
    if (!$global_id = ExternConfig::GetConfigurationByName($range_id, $type, $config_name)) {
        echo $EXTERN_ERROR_MESSAGE;
        exit;
    }
}
elseif (!$global_id) {
    // check for standard configuration
    if ($id = ExternConfig::GetGlobalConfiguration($range_id))
        $global_id = $id;
    else {
        // use no global configuration
        $global_id = NULL;
    }
}

// sem == -1: show data from last semester
// sem == +1: show data from next semester
// other values: show data from current semester
$now = time();
foreach ($all_semester as $key => $sem_record) {
    if ($now >= $sem_record["beginn"] && $now <= $sem_record["ende"]) {
        $current = $key;
        break;
    }
}
if ($sem_offset == "-1") {
    $start = $all_semester[$current - 1]["beginn"];
    $end = $all_semester[$current - 1]["ende"];
} elseif ($sem_offset == "+1") {
    $start = $all_semester[$current + 1]["beginn"];
    $end = $all_semester[$current + 1]["ende"];
} else {
    $start = $all_semester[$current]["beginn"];
    $end = $all_semester[$current]["ende"];
}

// all parameters ok, instantiate module and print data
foreach ($EXTERN_MODULE_TYPES as $type) {
    if ($type["module"] == $module_name) {
        $class_name = "ExternModule" . $module_name;
        require_once "lib/extern/modules/$class_name.class.php";
        $module_obj = ExternModule::GetInstance($range_id, $module_name, $config_id, $default, $global_id);
    }
}
// drop URL parameters from page_url
$page_url = preg_replace('/\?.*/', '', Request::get('page_url'));

$sri_url = $module_obj->config->getValue('Main', 'sriurl');

if (isset($sri_url)) {
    // drop URL parameters from sri_url
    $sri_url = preg_replace('/\?.*/', '', $sri_url);
}

if ($page_url != $sri_url || !sri_is_enabled($module_obj->config->range_id)) {

    echo $EXTERN_ERROR_MESSAGE;
    exit;
}

$args = $module_obj->getArgs();
foreach ($args as $arg) {
    $arguments[$arg] = Request::quoted($arg);
}

echo $sri_matches[1];
$module_obj->printout($arguments);
echo $sri_matches[3];
