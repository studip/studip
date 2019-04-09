<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: test
# Lifter010: TODO
/**
* ExternModule.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternModule
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElement.class.php
// This is an abstract class that define an interface to every so called HTML-element
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

require_once 'lib/extern/views/ExternEditModule.class.php';

class ExternModule {

    var $type = NULL;
    var $name;
    var $config;
    var $registered_elements = [];
    var $elements = [];
    var $field_names = [];
    var $data_fields = [];
    var $args = [];
    private static $is_raw_output = false;


    /**
    *
    */
    function GetInstance ($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {

        if ($module_name != '') {
            $module_name = ucfirst($module_name);
            require_once "lib/extern/modules/ExternModule$module_name.class.php";

            $class_name = "ExternModule" . $module_name;
            $module = new $class_name($range_id, $module_name, $config_id, $set_config, $global_id);
            return $module;
        }

        return NULL;
    }

    /**
    * The constructor of a child class has to call this parent constructor!
    */
    function __construct($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {

        foreach ($GLOBALS["EXTERN_MODULE_TYPES"] as $type => $module) {
            if ($module["module"] == $module_name) {
                $this->type = $type;
                break;
            }
        }

        // the module is called via extern.php (not via the admin area) and there is
        // no config_id so it's necessary to check the range_id
        if (!$config_id && !$this->checkRangeId($range_id)) {
            $this->printError();
        }
        if (is_null($this->type)) {
            $this->printError();
        }

        $this->name = $module_name;

        if ($config_id) {
            $this->config = ExternConfig::GetInstance($range_id, $module_name, $config_id);
        } else  {
            $this->config = ExternConfig::GetInstance($range_id, $module_name);
        }

        // the "Main"-element is included in every module and needs information
        // about the data this module handles with
        $this->elements["Main"] = ExternElementMain::GetInstance(
            $this->config, $module_name,
            $this->data_fields, $this->field_names
        );

        // instantiate the registered elements
        foreach ($this->registered_elements as $name => $registered_element) {
            if (is_int($name) || !$name) {
                $this->elements[$registered_element] = ExternElement::GetInstance($this->config, $registered_element);
            } else {
                $this->elements[$name] = ExternElement::GetInstance($this->config, $registered_element);
                $this->elements[$name]->name = $name;
            }
        }

        if ($set_config && !$config_id) {
            $config = $this->getDefaultConfig();
            $this->config->setDefaultConfiguration($config);
        }

        // overwrite modules configuration with global configuration
        if (!is_null($global_id)) {
            $this->config->setGlobalConfig(ExternConfig::GetInstance($range_id, $module_name, $global_id),
                    $this->registered_elements);
        }

        $this->setup();
    }

    /**
    *
    */
    function getType () {
        return $this->type;
    }

    /**
    *
    */
    function getName () {
        return $this->name;
    }

    /**
    *
    */
    function &getConfig () {
        return $this->config;
    }

    /**
    *
    */
    function getDefaultConfig () {
        $default_config = [];

        if ($default_config = $this->getRangeDefaultConfig('global')) {
            return $default_config;
        }
        foreach ($this->elements as $element) {
            if ($element->isEditable()) {
                $default_config[$element->getName()] = $element->getDefaultConfig();
            }
        }

        return $default_config;
    }

    function getRangeDefaultConfig ($range_id = 'global') {
        $query = "SELECT config_type FROM extern_config WHERE config_id = ? AND range_id = ?";
        $parameters = [$this->getName(), $range_id ];
        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        $row = $statement->fetchColumn();
        if ($row !== false) {
            $config_obj = ExternConfig::GetInstance($range_id, $this->getName(), $this->getName());
            $config = $config_obj->getConfiguration();
            return $config;
        }

        return FALSE;
    }

    /**
    *
    */
    function getAllElements () {
        return $this->elements;
    }

    /**
    *
    */
    function getValue ($attribute) {
        return $this->config->getValue($this->name, $attribute);
    }

    /**
    *
    */
    function setValue ($attribute, $value) {
        $this->config->setValue($this->name, $attribute, $value);
    }

    /**
    *
    */
    function getAttributes ($element_name, $tag_name) {
        return $this->config->getAttributes($element_name, $tag_name);
    }

    function getArgs () {

        return $this->args;
    }

    /**
    *
    */
    function toString ($args) {}

    /**
    *
    */
    function toStringEdit ($open_elements = "", $post_vars = "",
            $faulty_values = "", $anker = "") {

        require_once "lib/extern/views/ExternEditModule.class.php";
        $edit_form = new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);

        $out = $edit_form->editHeader();

        foreach ($this->elements as $element) {
            if ($element->isEditable()) {
                if (isset($open_elements[$element->getName()])) {
                    $out .= $element->toStringEdit($post_vars, $faulty_values, $edit_form, $anker);
                } else {
                    $edit_form->setElementName($element->getName());
                    $out .= $edit_form->editElementHeadline($element->getRealName(),
                            $this->getName(), $this->config->getId(), FALSE, $anker);
                }
            }
        }

        $out .= $edit_form->editFooter();

        return $out;
    }

    /**
    *
    */
    function printout ($args) {
        echo $this->toString($args);
    }
    /**
    *
    */
    function printoutEdit ($element_name = "", $post_vars = "",
            $faulty_values = "", $anker = "") {

        echo $this->toStringEdit($element_name, $post_vars, $faulty_values, $anker);
    }

    /**
    *
    */
    function checkFormValues ($element_name = "") {
        $faulty_values = [];

        if ($element_name == "") {
            foreach ($this->elements as $element) {
                if ($faulty = $element->checkFormValues()) {
                    $faulty_values = $faulty_values + $faulty;
                }
            }
        }
        else {
            if ($faulty_values = $this->elements[$element_name]->checkFormValues()) {
                return $faulty_values;
            }
        }

        if (sizeof($faulty_values)) {
            return $faulty_values;
        }

        return FALSE;
    }

    /**
    *
    */
    function store ($element_name = '', $values = '') {
        $this->config->restore($this, $element_name, $values);
        $this->config->store();
    }

    /**
    *
    */
    function getDescription () {
        return $GLOBALS["EXTERN_ELEMENT_TYPES"][$this->type]["description"];
    }

    /**
    *
    */
    function executeCommand ($element, $command, $value) {
        if ($element == "Main" || in_array($element, $this->registered_elements)) {
            return $this->elements[$element]->executeCommand($command, $value);
        }
    }

    /**
    *
    */
    function checkRangeId ($range_id) {
        if ($range_id == 'studip') {
            return in_array('studip', $GLOBALS['EXTERN_MODULE_TYPES'][$this->type]['view']);
        }

        return in_array(get_object_type($range_id), $GLOBALS['EXTERN_MODULE_TYPES'][$this->type]['view']);
    }

    /**
    *
    */
    function printError () {
        page_close();
        exit;
    }

    /**
    *
    */
    function getModuleLink ($module_name, $config_id, $sri_link) {
        if ($this->config->global_id) {
            $global_param = "global_id=" . $this->config->global_id;
        } else {
            $global_param = '';
        }
        if ($this->config->config["Main"]["incdata"]) {
            if (mb_strrpos($sri_link, '?')) {
                $link = $sri_link . ($global_param != '' ? '&' : '') . $global_param;
            } else {
                $link = $sri_link . ($global_param != '' ? '?' : '') . $global_param;
            }
        } else {
            $global_param = $global_param != '' ? $global_param . '&' : '';
            if ($sri_link) {
                $link = $GLOBALS['EXTERN_SERVER_NAME'] . "extern.php?{$global_param}page_url=$sri_link";
            } else {
                $link = $GLOBALS['EXTERN_SERVER_NAME'] . "extern.php?{$global_param}module=$module_name";
                if ($config_id) {
                    $link .= "&config_id=$config_id";
                }
                $link .= "&range_id={$this->config->range_id}";
            }
        }

        return $link;
    }

    /**
    *
    */
    function setup () {}

    function updateGenericDatafields ($element_name, $object_type) {
        $datafields_config = $this->config->getValue($element_name, 'genericdatafields');
        if (!is_array($datafields_config)) {
            $datafields_config = [];
        }
        $datafields = get_generic_datafields($object_type);
        foreach ((array) $datafields['ids'] as $df) {
            if (!in_array($df, $datafields_config)) {
                $datafields_config[] = $df;
            }
        }
        $this->config->setValue($element_name, 'genericdatafields', $datafields_config);
        $this->config->store();
    }

    function insertDatafieldMarkers ($object_type, &$markers, $element_name) {
        $datafields_config = $this->config->getValue($element_name, 'genericdatafields');
        if (!is_array($datafields_config)) {
            $datafields_config = [];
        }
        /*
        $datafields_obj = new DataFields();
        $datafields = $datafields_obj->getFields($object_type);
        $i = 1;
        foreach ((array) $datafields_config as $df_id) {
            if (isset($datafields[$df_id])) {
                $markers[$element_name][] = array("###DATAFIELD_$i###", $datafields[$df_id]['name']);
            }
            $i++;
        }
        */
        $datafields = get_generic_datafields($object_type);
        $i = 1;
        foreach ((array) $datafields_config as $df_id) {
            if (isset($datafields['ids_names'][$df_id])) {
                $markers[$element_name][] = ["###DATAFIELD_$i###", $datafields['ids_names'][$df_id]];
            }
            $i++;
        }
    }

    function insertPluginMarkers ($plugin_type, &$markers, $element_name) {
        $plugin_manager = PluginManager::getInstance();

        foreach ($plugin_manager->getPluginInfos($plugin_type) as $plugin) {
            $keyname = 'PLUGIN_' . mb_strtoupper($plugin['name']);
            $markers[$element_name][] = ["###$keyname###", $plugin['description']];
        }
    }

    public static function SetRawOutput ($raw = TRUE) {
        self::$is_raw_output = $raw;
    }

    public static function ExtHtmlReady ($text, $allow_links = FALSE) {
        if (self::$is_raw_output) {
            return $text;
        }
        return $allow_links ? formatLinks($text) : htmlReady($text);
    }

    public static function ExtFormatReady ($text) {
        if (self::$is_raw_output) {
            return $text;
        }
        return Studip\Markup::apply(new StudipFormat(), $text, true);
    }

    public static function ExtWikiReady ($text, $show_comments = 'all') {
        if (self::$is_raw_output) {
            return $text;
        }
        return wikiReady($text, TRUE, TRUE, $show_comments);
    }

    public static function GetOrderedModuleTypes () {
        $order = [];
        foreach ($GLOBALS['EXTERN_MODULE_TYPES'] as $key => $module) {
            $order[$GLOBALS['EXTERN_MODULE_TYPES'][$key]['order']] = $key;
        }
        ksort($order, SORT_NUMERIC);
        return $order;
    }

    function getLinkToModule ($linked_element_name = null, $params = null, $with_module_params = false, $self = false) {
        if ($with_module_params) {
            $module_params = $this->getModuleParams();
            $params = array_merge($module_params, $params);
        }
        $query_parts = [];
        if (is_array($params)) {
            $param_key = 'ext_' . mb_strtolower($this->name);
            foreach ($params as $name => $value) {
                $query_parts[] = "{$param_key}[{$name}]=" . $value;
            }
        }

        if (is_null($linked_element_name)) {
            $sriurl = trim($this->config->getValue('Main', 'sriurl'));
            $includeurl = trim($this->config->getValue('Main', 'includeurl'));
        } else {
            $sriurl = trim($this->config->getValue($linked_element_name, 'srilink'));
            $includeurl = trim($this->config->getValue($linked_element_name, 'includlink'));
        }

        if ($sriurl) {
            $url = $sriurl;
        } else if ($includeurl) {
            $url = $includeurl;
        } else {
            $url = $GLOBALS['EXTERN_SERVER_NAME'] . 'extern.php';
        }

        if (parse_url($url, PHP_URL_QUERY)) {
            $url .= '&';
        } else {
            $url .= '?';
        }

        if ($self) {
            $module = $this->name;
        } else {
            // get module name by config id
            $linked_element_id = $this->config->getValue($linked_element_name, 'config');
            // linked with module declared as standard?
            if ($linked_element_id) {
                $config_meta_data = ExternConfig::GetConfigurationMetaData($this->config->range_id, $linked_element_id);
            } else {
                $config_meta_data = ['module_name' => $this->config->module_name];
            }
            if (is_array($config_meta_data)) {
                $module = $config_meta_data['module_name'];
            //  var_dump($this->config);
            } else {
                return '';
            }
        }

        $url .= "module={$module}&config_id=" . (is_null($linked_element_name) ? $this->config->getId() : $this->config->getValue($linked_element_name, 'config')) . "&range_id={$this->config->range_id}";
        if (sizeof($query_parts)) {
            $url .= '&' . implode('&', $query_parts);
        }
        return $url;
    }

    function getLinkToSelf ($params = null, $with_module_params = false, $linked_element_name = null) {
        return $this->getLinkToModule($linked_element_name, $params, $with_module_params, true);
    }

    function getModuleParams ($params = null) {
        $param_key = 'ext_' . mb_strtolower($this->name);
        if (is_array($_REQUEST[$param_key])) {
            $ret = [];
            if (is_null($params)) {
                if (is_array($_GET[$param_key])) {
                    foreach ($_GET[$param_key] as $key => $value) {
                        $ret[$key] = urldecode($value);
                    }
                }
                if (is_array($_POST[$param_key])) {
                    foreach ($_POST[$param_key] as $key => $value) {
                        $ret[$key] = $value;
                    }
                }
                return $ret;
            }
            foreach ($params as $param) {
                if (isset($_GET[$param_key][$param])) {
                    $ret[$param] = urldecode($_GET[$param_key][$param]);
                }
                if (isset($_POST[$param_key][$param])) {
                    $ret[$param] = $_POST[$param_key][$param];
                }
            }
            return $ret;
        }
        return [];
    }

    /**
     * Checks access for a module in a given view.of the admin area.
     *
     * @param string $view view in the admin area ('extern_inst' or 'extern_global')
     * @param int $module_id (optional) ID of the module (see extern_config.inc.php)
     * @param string $module_name (optional) name of the module (see extern_config.inc.php)
     * @return bool access granted
     */
    public static function HaveAccessModuleType ($view, $module_id = NULL, $module_name = NULL) {
        if (!is_null($module_id)) {
            if (!is_array($GLOBALS['EXTERN_MODULE_TYPES'][$module_id])) {
                return false;
            }
            switch ($view) {
                case 'extern_inst' :
                    return (in_array('inst', $GLOBALS['EXTERN_MODULE_TYPES'][$module_id]['view']) || in_array('fak', $GLOBALS['EXTERN_MODULE_TYPES'][$module_id]['view']));
                case 'extern_global' :
                    return in_array('studip', $GLOBALS['EXTERN_MODULE_TYPES'][$module_id]['view']);
                default :
                    return false;
            }
        } else if (!is_null($module_name)) {
            foreach ($GLOBALS['EXTERN_MODULE_TYPES'] as $id => $module) {
                if ($module_id == $id || $module_name == $module['module']) {
                    switch ($view) {
                        case 'extern_inst' :
                            return (in_array('inst', $module['view']) || in_array('fak', $module['view']));
                        case 'extern_global' :
                            return in_array('studip', $module['view']);
                        default :
                            return false;
                    }
                }
            }
        }
        return false;
    }
}
?>
