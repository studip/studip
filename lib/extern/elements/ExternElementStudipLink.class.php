<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementStudipLink.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementStudipLink
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementStudipLink.class.php
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


class ExternElementStudipLink extends ExternElement {

    var $attributes = ["linktext", "imageurl", "image", "a_class", "a_style", "font_face",
            "font_size", "font_color", "font_class", "font_style", "align"];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config)
            $this->config = $config;

        $this->name = "StudipLink";
        $this->real_name = _("Link zum Stud.IP Administrationsbereich");
        $this->description = _("Link zum direkten Einsprung in den Stud.IP Administrationsbereich.");
    }

    /**
    *
    */
    function getDefaultConfig () {

        $config = [
            "linktext" => _("Daten ändern"),
            "imageurl" => "",
            "image" => "1",
            "align" => "left"
        ];

        return $config;
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

        $content_table = $edit_form->getEditFormContent($this->attributes);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Weitere Angaben"));

        $content = $edit_form->editAlign("align");

        $title = _("Linktext:");
        $info = _("Geben Sie den Text für den Link ein.");
        $content .= $edit_form->editTextfieldGeneric("linktext", $title, $info, 40, 150);

        $title = _("Bild anzeigen:");
        $info = _("Anwählen, wenn ein Bild als Link angezeigt werden soll.");
        $value = "1";
        $content .= $edit_form->editCheckboxGeneric("image", $title, $info, $value, "");

        $title = _("Bild-URL:");
        $info = _("Geben Sie die URL eines Bildes ein, dass als Link dienen soll. Wenn Sie keine URL angeben, wird ein Standard-Bild (Pfeile) ausgegeben.");
        $content .= $edit_form->editTextfieldGeneric("imageurl", $title, $info, 40, 150);

        $content_table .= $edit_form->editContentTable($headline, $content);
        $content_table .= $edit_form->editBlankContent();

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return  $element_headline . $out;
    }

    function toString ($args = null) {
        $out = "<table width=\"{$args['width']}\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
        $out .= "<tr>";
        $out .= "<td height=\"{$args['height']}\" width=\"100%\" align=\""
                . $this->config->getValue($this->name, "align") . "\">\n";
        $font = "<font" . $this->config->getAttributes($this->name, "font") . ">";
        $out .= sprintf(
            "<a href=\"%s\"%s target=\"_blank\" rel=\"noopener noreferrer\">%s%s</font></a>",
            $args['link'],
            $this->config->getAttributes($this->name, "a"), $font,
            $this->config->getValue($this->name, "linktext")
        );
        if ($this->config->getValue($this->name, "image")) {
            if ($image_url = $this->config->getValue($this->name, "imageurl"))
                $img = Assets::img($image_url);
            else {
                $img = Icon::create('door-enter', 'clickable')->asImg();
            }
            $out .= sprintf(
                "&nbsp;<a href=\"%s\"%s target=\"_blank\" rel=\"noopener noreferrer\">%s</a>",
                $args['link'],
                $this->config->getAttributes($this->name, "a"),
                $img
            );
        }
        $out .= "\n</td></tr></table>\n";

        return $out;
    }

    function checkValue ($attribute, $value) {
        if ($attribute == "image") {
            // This is especially for checkbox-values. If there is no checkbox
            // checked, the variable is not declared and it is necessary to set the
            // variable to 0.
            if (!isset($_POST[$this->name . "_" . $attribute])) {
                $_POST[$this->name . "_" . $attribute] = 0;
                return FALSE;
            }
            return !($value == "1" || $value == "0");
        }

        return FALSE;
    }

}

?>
