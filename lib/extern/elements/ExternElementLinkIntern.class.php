<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementLinkIntern.class.php
*
*
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementLinkIntern
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementLinkIntern.class.php
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

class ExternElementLinkIntern extends ExternElement {

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
    }

    /**
    *
    */
    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {

        $out = "";
        $table = "";
        if ($edit_form == "")
            $edit_form = new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);

        $edit_form->setElementName($this->getName());
        $element_headline = $edit_form->editElementHeadline($this->real_name,
                $this->config->getName(), $this->config->getId(), TRUE, $anker);

        $attributes = ["font_size", "font_face", "font_color", "font_class", "font_style",
            "a_class", "a_style"];
        $headlines = ["font" => _("Schriftformatierung"),
                "a" => _("Linkformatierung")];
        $content_table = $edit_form->getEditFormContent($attributes, $headlines);
        $content_table .= $edit_form->editBlankContent();

        $this->toStringConfigSelector($edit_form, $content_table);

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return $element_headline . $out;
    }

    function toStringConfigSelector (&$edit_form, &$content_table) {
        global $EXTERN_MODULE_TYPES;
        $headline = $edit_form->editHeadline(_("Verlinkung zum Modul"));
        $title = _("Konfiguration:");
        $info = _("Der Link ruft das Modul mit der gewählten Konfiguration auf. Wählen Sie \"Standard\", um die von Ihnen gesetzte Standardkonfiguration zu benutzen. Ist für das aufgerufene Modul noch keine Konfiguration erstellt worden, wird die Stud.IP-Default-Konfiguration verwendet.");
        $values = [];
        $names = [];
        $spacer = '';
        $first_module = TRUE;
        foreach ((array) $this->link_module_type as $module_type) {
            $configs = ExternConfig::GetAllConfigurations($this->config->range_id, $module_type);
            if (sizeof($configs)) {
                if ($first_module) {
                    $names[] = _("Standardkonfiguration") . ' ('. $EXTERN_MODULE_TYPES[$module_type]['name'] . ')';
                    $values[] = '';
                    $first_module = FALSE;
                }
                $configs = $configs[$EXTERN_MODULE_TYPES[$module_type]['module']];
                foreach ($configs as $config_id => $config) {
                    $names[] = $config['name'] . ' ('. $EXTERN_MODULE_TYPES[$module_type]['name'] . ')';
                    $values[] = $config_id;
                }
            }
        }
        $table = $edit_form->editOptionGeneric('config', $title, $info, $values, $names);

        $title = _("SRI-Link:");
        $info = _("Wenn Sie die SRI-Schnittstelle benutzen, müssen Sie hier die vollständige URL (mit http://) der Seite angeben, in der das Modul, das durch den Link aufgerufen wird, eingebunden ist. Lassen Sie dieses Feld unbedingt leer, falls Sie die SRI-Schnittstelle nicht nutzen.");
        $table .= $edit_form->editTextfieldGeneric("srilink", $title, $info, 50, 250);

        $title = _("Extern-Link:");
        $info = _("Wenn Sie die SRI-Schnittstelle nicht benutzen, können Sie hier die vollständige URL (mit http://) der Seite angeben, in der das Modul, das durch den Link aufgerufen wird, eingebunden wird. Lassen Sie dieses Feld unbedingt leer, falls Sie die SRI-Schnittstelle nutzen.");
        $table .= $edit_form->editTextfieldGeneric("externlink", $title, $info, 50, 250);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();
    }

    function checkValues ($attribute, $value) {
        if ($attribute == "srilink") {
            return preg_match("|^https?://.*$|i", $value);
        }
    }

    function toString ($args = null) {
        $link = $this->createUrl($args);
        // to set the color of the font in the style-attribute of the a-tag
        if ($color = $this->config->getValue($this->name, "font_color")) {
            $this->config->setValue($this->name, "a_style", "color:$color;"
                    . $this->config->getValue($this->name, "a_style"));
        }

        if ($font_attr = $this->config->getAttributes($this->name, "font"))
            $out = "<font$font_attr>" . $args["content"] . "</font>";
        else
            $out = $args["content"];
        $out = "<a href=\"$link\"" . $this->config->getAttributes($this->name, "a") . ">" . $out . "</a>";

        return $out;
    }
}
