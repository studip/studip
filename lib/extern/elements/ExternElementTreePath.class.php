<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementTreePath.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementTreePath
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementTreePath.class.php
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

class ExternElementTreePath extends ExternElement {

    var $attributes = ["delimiter", "font_size", "font_face", "font_color", "font_class", "font_style",
            "a_class", "a_style"];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config)
            $this->config = $config;

        $this->name = "TreePath";
        $this->real_name = _("Navigations-Pfad");
        $this->description = _("Eigenschaften des Navigations-Pfades innerhalb einer Baum-Navigation.");
    }

    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {
        if ($faulty_values == '')
            $faulty_values = [];
        $out = '';
        $tag_headline = '';
        $table = '';
        if ($edit_form == '')
            $edit_form = new ExternEditHtml($this->config, $post_vars, $faulty_values, $anker);

        $edit_form->setElementName($this->getName());
        $element_headline = $this->getEditFormHeadline($edit_form);

        $headline = $edit_form->editHeadline(_("Allgemeine Angaben"));
        $title = _('Pfad-Trennzeichen') . ':';
        $info = _("Geben Sie ein oder mehrere Zeichen ein, die als Trennzeichen zwischen den Links im Navigations-Pfad erscheinen sollen.");
        $content = $edit_form->editTextfieldGeneric("delimiter", $title, $info, 25, 50);

        $content_table = $edit_form->editContentTable($headline, $content);
        $content_table .= $edit_form->editBlankContent();

        $out = $content_table . $edit_form->getEditFormContent($this->attributes);

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($out, $submit);
        $out .= $edit_form->editBlank();

        return  $element_headline . $out;
    }

}
