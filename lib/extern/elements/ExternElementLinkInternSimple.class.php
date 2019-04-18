<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementLinkInternSimple.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementLinkInternSimple
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementLinkInternSimple.class.php
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

require_once 'ExternElementLinkIntern.class.php';

// this class is only for compatibility reasons (Stud.IP < 0.95)
// this class is replaced by ExternElementLinkIntern and the
// use of global configurations
class ExternElementLinkInternSimple extends ExternElement {

    var $attributes = ["font_size", "font_face", "font_color", "font_class", "font_style",
            "a_class", "a_style", "config", "srilink", "externlink"];
    var $link_module_type;

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config)
            $this->config = $config;

        $this->name = "LinkIntern";
        $this->real_name = _("Links");
        $this->description = _("Eigenschaften der Schrift für Links.");
        $this->headlines = [_("Schriftformatierung"), _("Linkformatierung"),
                _("Verlinkung zum Modul")];
    }

    /**
    *
    */
    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {
        global $EXTERN_MODULE_TYPES;
        $out = "";
        $table = "";
        if ($edit_form == "") {
            $edit_form = new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);
        }

        $edit_form->setElementName($this->getName());
        $element_headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE, $anker);

        $attributes = ["font_size", "font_face", "font_color", "font_class", "font_style",
            "a_class", "a_style"];
        $headlines = ["font" => $this->headlines[0],
                "a" => $this->headlines[1]];
        $content_table = $edit_form->getEditFormContent($attributes, $headlines);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline($this->headlines[2]);

        $title = _('Konfiguration') . ':';
        $info = _("Der Link ruft das Modul mit der gewählten Konfiguration auf. Wählen Sie \"Standard\", um die von Ihnen gesetzte Standardkonfiguration zu benutzen. Ist für das aufgerufene Modul noch keine Konfiguration erstellt worden, wird die Stud.IP-Default-Konfiguration verwendet.");
        $configs = ExternConfig::GetAllConfigurations($this->config->range_id, $this->link_module_type);
        if (sizeof($configs)) {
            $module_name = $EXTERN_MODULE_TYPES[$this->link_module_type]["module"];
            $values = array_keys($configs[$module_name]);
            unset($names);
            foreach ($configs[$module_name] as $config)
                $names[] = $config["name"];
        }
        else {
            $values = [];
            $names = [];
        }
        array_unshift($values, "");
        array_unshift($names, _("Standardkonfiguration"));
        $table = $edit_form->editOptionGeneric("config", $title, $info, $values, $names);

        $title = _('SRI-Link') . ':';
        $info = _("Wenn Sie die SRI-Schnittstelle benutzen, müssen Sie hier die vollständige URL (mit http://) der Seite angeben, in der das Modul, das durch den Link aufgerufen wird, eingebunden ist. Lassen Sie dieses Feld unbedingt leer, falls Sie die SRI-Schnittstelle nicht nutzen.");
        $table .= $edit_form->editTextfieldGeneric("srilink", $title, $info, 50, 250);

        $title = _('Extern-Link') . ':';
        $info = _("Wenn Sie die SRI-Schnittstelle nicht benutzen, können Sie hier die vollständige URL (mit http://) der Seite angeben, in der das Modul, das durch den Link aufgerufen wird, eingebunden wird. Lassen Sie dieses Feld unbedingt leer, falls Sie die SRI-Schnittstelle nutzen.");
        $table .= $edit_form->editTextfieldGeneric("externlink", $title, $info, 50, 250);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return $element_headline . $out;
    }

    function checkValues ($attribute, $value) {
        if ($attribute == "srilink")
            return preg_match("|^https?://.*$|i", $value);
    }

    function toString ($args = null) {
        if (!$args["main_module"]) {
            $args["main_module"] = "Main";
        }
        $config_meta_data = ExternConfig::GetConfigurationMetaData($this->config->range_id, $this->config->getValue($this->name, 'config'));
        $sri_link = $this->config->getValue($this->name, "srilink");
        $extern_link = $this->config->getValue($this->name, "externlink");
        if ($this->config->config[$args["main_module"]]["incdata"]) {
            $link = $sri_link;
            if ($args["link_args"]) {
                if (preg_match("#.*\?.*#", $link)) {
                    $link .= "&" . $args["link_args"];
                } else {
                    $link .= "?" . $args["link_args"];
                }
            }
        } else {
            if ($sri_link) {
                $link = $GLOBALS['EXTERN_SERVER_NAME'] . 'extern.php';
                if ($args["link_args"]) {
                    $link .= "?" . $args["link_args"] . "&";
                } else {
                    $link .= "?";
                }
                $link .= "page_url=" . $sri_link;
            } elseif ($extern_link) {
                if (mb_strrpos($extern_link, '?')) {
                    $link = "$extern_link&module={$config_meta_data['module_name']}";
                } else {
                    $link = "$extern_link?module={$config_meta_data['module_name']}";
                }
                if ($config = $this->config->getValue($this->name, 'config')) {
                    $link .= "&config_id=" . $config;
                }
                $link .= "&range_id={$this->config->range_id}";
                if ($args["link_args"]) {
                    $link .= "&" . $args["link_args"];
                }
            } else {
                $link = $GLOBALS['EXTERN_SERVER_NAME'] . "extern.php?module={$config_meta_data['module_name']}";
                if ($config = $this->config->getValue($this->name, 'config')) {
                    $link .= "&config_id=" . $config;
                }
                $link .= "&range_id={$this->config->range_id}";
                if ($args["link_args"]) {
                    $link .= "&" . $args["link_args"];
                }
            }
        }
        if ($this->config->global_id) {
            $link .= "&global_id=" . $this->config->global_id;
        }

        // to set the color of the font in the style-attribute of the a-tag
        if ($color = $this->config->getValue($this->name, "font_color")) {
            $this->config->setValue($this->name, "a_style", "color:$color;"
                    . $this->config->getValue($this->name, "a_style"));
        }

        if ($font_attr = $this->config->getAttributes($this->name, "font")) {
            $out = "<font$font_attr>" . $args["content"] . "</font>";
        } else {
            $out = $args["content"];
        }
        $out = "<a href=\"$link\"" . $this->config->getAttributes($this->name, "a") . ">" . $out . "</a>";

        return $out;
    }

}
