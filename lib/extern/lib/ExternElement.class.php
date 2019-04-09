<?
# Lifter002: TODO
# Lifter005: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElement.class.php
*
* This is an abstract class that define an interface to every so called HTML-element
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElement
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
class ExternElement {

    var $type;
    var $config;
    var $name;
    var $attributes;
    var $real_name;
    var $description;
    var $headlines = [];


    /**
    *
    */
    function GetInstance (&$config, $element_name, &$data_fields = null, &$field_names = null) {
        $class_name = "ExternElement" . $element_name;
        require_once "lib/extern/elements/$class_name.class.php";
        $element = new $class_name();
        $element->config =& $config;

        return $element;
    }


    /**
    * Constructor
    * Don't call direct, use GetInstance() instead.
    *
    * @param array config
    * @param string element_name
    */
    function __construct (&$config, $element_name) {
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
    function getRealname () {
        return $this->real_name;
    }

    /**
    *
    */
    function getDefaultConfig () {
        $config = [];

        reset($this->attributes);
        foreach ($this->attributes as $attribute)
            $config[$attribute] = "";

        return $config;
    }

    /**
    *
    */
    function isEditable () {
        if (sizeof($this->attributes))
            return TRUE;

        return FALSE;
    }

    /**
    *
    */
    function getValue ($attribute) {
        return $this->config->getValue($this->type, $attribute);
    }

    /**
    *
    */
    function toString ($args = NULL) {
        return "";
    }

    /**
    *
    */
    function printout ($args = NULL) {
        echo $this->toString($args);
    }

    /**
    *
    */
    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {

        if ($faulty_values == '')
            $faulty_values = [];
        $out = "";
        if ($edit_form == "")
            $edit_form = new ExternEditHtml($this->config, $post_vars, $faulty_values, $anker);

        $edit_form->setElementName($this->getName());
        $element_headline = $this->getEditFormHeadline($edit_form);

        $out = $edit_form->getEditFormContent($this->attributes);

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($out, $submit);
        $out .= $edit_form->editBlank();

        return  $element_headline . $out;
    }

    function getEditFormHeadline (&$edit_form) {
        $headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE);

        return $headline;
    }

    /**
    *
    */
    function printoutEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {

        echo $this->toStringEdit($post_vars, $faulty_values, $edit_form, $anker);
    }

    /**
    *
    */
    function getAttributes ($short = TRUE) {
        if ($short)
            return $this->attributes;

        reset($this->attributes);
        foreach($this->attributes as $attribute)
            $attributes_long[] = $this->name . "_" . $attribute;

        return $attributes_long;
    }

    /**
    *
    */
    function getDescription () {
        return $this->description;
    }

    /**
    *
    */
    function executeCommand ($command, $value = "") {
        switch ($command) {
            case "show" :
                $visible = $this->config->getValue($this->name, "visible");
                if ($value >= 0 || $value < sizeof($visible)) {
                    $visible[$value] = "1";
                    $this->config->setValue($this->name, "visible", $visible);

                    Request::set("{$this->name}_visible",$visible);
                    $_POST["{$this->name}_visible"] = $visible;
                }
                break;

            case "hide" :
                $visible = $this->config->getValue($this->name, "visible");
                if ($value >= 0 || $value < sizeof($visible)) {
                    $visible[$value] = "0";
                    $this->config->setValue($this->name, "visible", $visible);
                    Request::set("{$this->name}_visible",$visible);
                    $_POST["{$this->name}_visible"] = $visible;
                }
                break;

            case "move_left" :
                $order = $this->config->getValue($this->name, "order");
                if ($value >= 0 || $value < sizeof($order)) {
                    $a = $order[$value];
                    if (($value - 1) < 0) {
                        $b = array_pop($order);
                        array_push($order, $a);
                        $order[0] = $b;
                    } else {
                        $b = $order[$value - 1];
                        $order[$value - 1] = $a;
                        $order[$value] = $b;
                    }
                    $this->config->setValue($this->name, "order", $order);
                    Request::set("{$this->name}_order", $order);
                }
                break;

            case "move_right" :
                $order = $this->config->getValue($this->name, "order");
                if ($value >= 0 || $value < sizeof($order)) {
                    $a = $order[$value];
                    if (($value + 1) >= sizeof($order)) {
                        $b = $order[0];
                        $order[0] = $a;
                        $order[$value] = $b;
                    } else {
                        $b = $order[$value + 1];
                        $order[$value + 1] = $a;
                        $order[$value] = $b;
                    }
                    $this->config->setValue($this->name, "order", $order);
                    Request::set("{$this->name}_order", $order);
                }
                break;

            case "show_group" :
                $groups = get_all_statusgruppen($this->config->range_id);
                if (!groups) {
                    $this->config->setValue($this->name, 'groupsvisible', []);
                } else if (is_array($value)) {
                    $groups_visible = [];
                    foreach ($value as $group_id => $checked) {
                        if ($checked) {
                            $groups_visible[] = $group_id;
                        }
                    }
                    $visible = array_intersect($groups_visible, array_keys($groups));
                    $this->config->setValue($this->name, 'groupsvisible', $visible);
                    Request::set("{$this->name}_groupsvisible", array_values($visible));
                }
                break;

            case "hide_group" :
                $visible = $this->config->getValue($this->name, "groupsvisible");
                if ($groups = get_all_statusgruppen($this->config->range_id)) {
                    $groups = array_keys($groups);
                    $visible = array_intersect($groups, $visible);
                } else break;
                // initialize groupsvisible if it isn't set in the config file
                // all groups are visible (1)
                if (!$visible) {
                    $visible = array_keys(get_all_statusgruppen($this->config->range_id));
                }
                $visible = array_diff($visible, [$value]);
                $this->config->setValue($this->name, "groupsvisible", $visible);
                Request::set("{$this->name}_groupsvisible", array_values($visible));
                break;

            default :
                return FALSE;
        }

        return TRUE;
    }

    /**
    *
    */
    function checkFormValues () {

        $fault = [];

        $_POST['Main_copyright'] = htmlReady(decodeHTML(
                $_POST['Main_copyright']));
        $_POST['Main_author'] = htmlReady(decodeHTML(
                $_POST['Main_author']));

        foreach ($this->attributes as $attribute) {
            $form_name = $this->name . "_" . $attribute;

            // Check for an alternative input field. All names of alternative input
            // fields begin with an underscore. The alternative input field overwrites
            // the input field having the same name but without the leading underscore.
            if (isset($_POST["_$form_name"])) {
                if ($_POST[$form_name] == $this->config->config[$this->name][$attribute]
                        && $_POST["_$form_name"] != "")
                    $_POST[$form_name] = $_POST["_$form_name"];
            }
            if (is_array($_POST[$form_name])) {
                $value = $_POST[$form_name];
            } else {
                $value = [$_POST[$form_name]];
            }

            $splitted_attribute = explode("_", $attribute);
            if (sizeof($splitted_attribute) == 1) {
                $html_attribute = $splitted_attribute[0];
            } else {
                $html_attribute = $splitted_attribute[1] . $splitted_attribute[2];
            }

            for ($i = 0; $i < sizeof($value); $i++) {

                if ($html_attribute != 'template') {
                    // Don't accept strings longer than 200 characters!
                    if (mb_strlen($value[$i]) > 200) {
                        $fault[$form_name][$i] = TRUE;
                        continue;
                    }

                    if (preg_match("/(<|>|\"|\|)/i", $value[$i])) {
                        $fault[$form_name][$i] = TRUE;
                        continue;
                    }
                }

                switch ($html_attribute) {

                    case "height" :
                        $fault[$form_name][$i] = (!preg_match("/^\d{0,3}$/", $value[$i])
                                || $value[$i]> 2000 || $value[$i]< 0);
                        break;
                    case "cellpadding" :
                    case "cellspacing" :
                    case "border" :
                    case "sort" :
                        $fault[$form_name][$i] = (!preg_match("/^\d{0,2}$/", $value[$i])
                                || $value[$i]> 100 || $value[$i]< 0);
                        break;
                    case "width" :
                        $fault[$form_name][$i] = (!preg_match("/^\d{0,4}$/", $value[$i])
                                || $value[$i]> 2000 || $value[$i]< 0);
                        if ($_POST["{$form_name}pp"] == "%") {
                            if (is_array($_POST[$form_name])) {
                                $_POST[$form_name][$i] = $_POST[$form_name][$i] . "%";
                            } else {
                                $_POST[$form_name] = $_POST[$form_name] . "%";
                            }
                        }
                        break;
                    case "valign" :
                        $fault[$form_name][$i] = $value[$i]
                                && !preg_match("/^(top|bottom|center)$/", $value[$i]);
                        break;
                    case "align" :
                        $fault[$form_name][$i] = $value[$i]
                                && !preg_match("/^(left|right|center|bottom|middle|top)$/", $value[$i]);
                        break;
                    case "size" :
                        $fault[$form_name][$i] = $value[$i]
                                && !preg_match("/^(-|\+)*(1|2|3|4|5|6|7)$/", $value[$i]);
                        break;
                    case "face" :
                        $fault[$form_name][$i] = $value[$i] && !preg_match("/^(Arial,Helvetica,sans-serif|"
                                . "Times,Times New Roman,serif|Courier,Courier New,monospace)$/", $value[$i]);
                        break;
                    case "background" :
                        $fault[$form_name][$i] = ($value[$i]
                                && (preg_match("/(<|>|\"|<script|<php)/i", $value[$i])
                                || !preg_match("/^[^.\/\\\].*\.(png|jpg|jpeg|gif)$/i", $value[$i])));
                        break;
                    case "wholesite" :
                    case "addinfo" :
                    case "time" :
                    case "lecturer" :
                        // This is especially for checkbox-values. If there is no checkbox
                        // checked, the variable is not declared and it is necessary to set the
                        // variable to "".
                        if (!isset($_POST[$form_name])) {
                            $_POST[$form_name] = "";
                            break;
                        }
                        $fault[$form_name][$i] = !($value[$i] == "1" || $value[$i] == "" || !isset($value[$i]));
                        break;
                    case "name" :
                        $_POST[$form_name] = trim($_POST[$form_name]);
                        $fault[$form_name][$i] = (preg_match("/^.*(<script|<php).*$/i", $value[$i])
                                || !preg_match("/^[0-9a-z\._\- ]+$/i", $value[$i]));
                        break;
                    case "widthpp" :
                        $fault[$form_name][$i] = !($value[$i] == "" || $value[$i] == "%");
                        break;
                    case "nameformat" :
                        $fault[$form_name][$i] = !($value[$i] == "no_title_short" || $value[$i] == "no_title"
                                || $value[$i] == "no_title_rev" || $value[$i] == "full"
                                || $value[$i] == "full_rev" || $value[$i] == "");
                        break;
                    case "dateformat" :
                        $fault[$form_name][$i] = !($value[$i] == "%d. %b. %Y" || $value[$i] == "%d.%m.%Y"
                                || $value[$i] == "%d.%m.%y" || $value[$i] == "%d. %B %Y" || $value[$i] == "%m/%d/%y");
                        break;
                    case "language" :
                        $fault[$form_name][$i] = !($value[$i] == "de_DE" || $value[$i] == "en_GB"
                                || $value[$i] == "");
                        break;
                    default :
                        $fault[$form_name][$i] = $this->checkValue($html_attribute, $value[$i]);

                }

            }

        }

        if (in_array(TRUE, $fault)) {
            return $fault;
        }

        return FALSE;
    }

    function checkValue ($attribute, $value) {

        return FALSE;
    }

    function createUrl ($args = NULL) {
        if (!isset($args['main_module'])) {
            $args['main_module'] = 'Main';
        }
        $config_meta_data = ExternConfig::GetConfigurationMetaData($this->config->range_id, $this->config->getValue($this->getName(), 'config'));
        if (is_array($config_meta_data)) {
            $module_name = $config_meta_data['module_name'];
        } else {
            foreach ((array) $this->link_module_type as $type) {
                $module_name = $GLOBALS['EXTERN_MODULE_TYPES'][$type]['module'];
                $configs = ExternConfig::GetAllConfigurations($this->config->range_id, $type);
                if (sizeof($configs)) {
                    break;
                }
            }
        }
        $sri_link = $this->config->getValue($this->getName(), 'srilink');
        $extern_link = $this->config->getValue($this->getName(), 'externlink');
        if ($this->config->getValue($args['main_module'], 'incdata')) {
            $link = $sri_link;
            if ($args['link_args']) {
                if (mb_strrpos($link, '?')) {
                    $link .= '&' . $args['link_args'];
                } else {
                    $link .= '?' . $args['link_args'];
                }
            }
            if ($this->config->global_id) {
                $link .= '&global_id=' . $this->config->global_id;
            }
        } else {
            if ($sri_link) {
                $link = $GLOBALS['EXTERN_SERVER_NAME'] . 'extern.php';
                if ($args['link_args']) {
                    $link .= '?' . $args['link_args'] . '&';
                } else {
                    $link .= '?';
                }
                if ($this->config->global_id) {
                    $link .= 'global_id=' . $this->config->global_id . '&';
                }
                $link .= 'page_url=' . $sri_link;
            } elseif ($extern_link) {
                if (mb_strrpos($extern_link, '?')) {
                    $link = "$extern_link&module=$module_name";
                } else {
                    $link = "$extern_link?module=$module_name";
                }
                if ($config = $this->config->getValue($this->getName(), 'config')) {
                    $link .= '&config_id=' . $config;
                }
                $link .= '&range_id=' . $this->config->range_id;
                if ($args['link_args']) {
                    $link .= '&' . $args['link_args'];
                }
                if ($this->config->global_id) {
                    $link .= '&global_id=' . $this->config->global_id;
                }
            } else {
                $link = $GLOBALS['EXTERN_SERVER_NAME'] . "extern.php?module=$module_name";
                if ($config = $this->config->getValue($this->getName(), 'config')) {
                    $link .= '&config_id=' . $config;
                }
                $link .= '&range_id=' . $this->config->range_id;
                if ($args['link_args']) {
                    $link .= '&' . $args['link_args'];
                }
                if ($this->config->global_id) {
                    $link .= '&global_id=' . $this->config->global_id;
                }
            }
        }
        return ExternModule::ExtHtmlReady($link);
    }
}

?>
