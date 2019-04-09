<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternEditHtml.class.php
*
* Form templates to edit values of html-tag attributes.
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternEditHtml
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternEditHtml.class.php
// Form templates to edit values of html-tag attributes.
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


require_once "lib/extern/views/ExternEditGeneric.class.php";


class ExternEditHtml extends ExternEditGeneric {

    function __construct (&$config, $form_values = "", $faulty_values = "",
             $edit_element = "") {
        parent::__construct($config, $form_values, $faulty_values, $edit_element);
    }

    /**
    * Prints out a form for entering the height of a html-element (e.g. &lt;tr&gt;, &lt;th&gt;)
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editHeight ($attribute) {
        $info = _("Geben Sie die Höhe der Tabellenzeile in Pixeln an.");
        $form_name = $this->element_name . "_" . $attribute;
        $value = $this->getValue($attribute);

        $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

        $out = "<label $invalidClass for=\"$form_name\">";
        $out .= _("Zeilenhöhe:");
        $out .= tooltipIcon($info);
        $out .= "</label>";

        $out .= "<section class=\"hgroup\">";
        $out .= "<label>";
        $out .= "<input type=\"text\" name=\"$form_name\" size=\"3\"";
        $out .= " maxlength=\"3\" class=\"no-hint \" value=\"$value\">&nbsp;Pixel";
        $out .= "</label>";
        $out .= "</section>";

        return $out;
    }

    /**
    * Prints out a form for entering the border-width of a table.
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editBorder ($attribute) {
        $info = _("Geben Sie die Breite des äußeren Tabellenrahmens in Pixeln an.");
        $form_name = $this->element_name . "_" . $attribute;
        $value = $this->getValue($attribute);

        $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

        $out = "<label $invalidClass for=\"$form_name\">";
        $out .= _("Rahmendicke:");
        $out .= tooltipIcon($info);

        $out .= "<section class=\"hgroup\">";
        $out .= "<input type=\"text\" name=\"$form_name\" size=\"2\"";
        $out .= " maxlength=\"2\" class=\"no-hint size-s \" value=\"$value\">&nbsp;Pixel";
        $out .= "</section>";
        $out .= "</label>";

        return $out;
    }

    /**
    * Prints out a form for entering the font-color.
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editColor ($attribute) {
        $info = _("Geben Sie einen HTML-Farbnamen oder eine Farbe im Hex-Format (#RRGGBB) in das Textfeld ein, oder wählen Sie eine Farbe aus der Auswahlliste.");
        $titel = _("Schriftfarbe");

        return $this->editColorGeneric($attribute, $titel, $info);
    }

    /**
    * Prints out a form for entering the backgroung-color of a table- or td-tag
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editBgcolor ($attribute) {
        $info = _("Geben Sie einen HTML-Farbnamen oder eine Farbe im Hex-Format (#RRGGBB) in das Textfeld ein, oder wählen Sie eine Farbe aus der Auswahlliste.");
        $title = _("Hintergrundfarbe:");

        return $this->editColorGeneric($attribute, $title, $info);
    }

    /**
    * Prints out a form for entering the bordercolor of a table.
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editBordercolor ($attribute) {
        $info = _("Geben Sie einen HTML-Farbnamen oder eine Farbe im Hex-Format (#RRGGBB) in das Textfeld ein, oder wählen Sie eine Farbe aus der Auswahlliste.");
        $title = _("Rahmenfarbe");

        return $this->editColorGeneric($attribute, $title, $info);
    }

    /**
    * Prints out a form for entering the second backgroung-color of a td- or th-tag (only for
    * zebra-effect.
    *
    * @param string name The name of the text field.
    * @param string value The value for the text pre-emption.
    */

    function editBgcolor2 ($attribute) {
        $info = _("Geben Sie einen HTML-Farbnamen oder eine Farbe im Hex-Format (#RRGGBB) in das Textfeld ein, oder wählen Sie eine Farbe aus der Auswahlliste. ");
        $info .= _("Diese Farbe wird als zweite Farbe bei aktiviertem Zebra-Effekt ausgegeben.");
        $title = _("2. Hintergrundf.:");

        return $this->editColorGeneric($attribute, $title, $info);
    }

    /**
    * Prints out a form for entering the link color in the body tag
    *
    * @param string name The name of the text field.
    * @param string value The value for the text pre-emption.
    */

    function editText ($attribute) {
        $info = _("Geben Sie einen HTML-Farbnamen oder eine Farbe im Hex-Format (#RRGGBB) in das Textfeld ein, oder wählen Sie eine Farbe aus der Auswahlliste. ");
        $info .= _("Diese Farbe wird seitenweit als Schriftfarbe benutzt.");
        $title = _("Schriftfarbe:");

        return $this->editColorGeneric($attribute, $title, $info);
    }

    /**
    * Prints out a form for entering the link color in the body tag
    *
    * @param string name The name of the text field.
    * @param string value The value for the text pre-emption.
    */

    function editLink ($attribute) {
        $info = _("Geben Sie einen HTML-Farbnamen oder eine Farbe im Hex-Format (#RRGGBB) in das Textfeld ein, oder wählen Sie eine Farbe aus der Auswahlliste. ");
        $info .= _("Diese Farbe wird seitenweit für Verweise zu noch nicht besuchten Zielen benutzt.");
        $title = _("Linkfarbe (nicht besucht):");

        return $this->editColorGeneric($attribute, $title, $info);
    }

    /**
    * Prints out a form for entering the link color in the body tag
    *
    * @param string name The name of the text field.
    * @param string value The value for the text pre-emption.
    */

    function editVlink ($attribute) {
        $info = _("Geben Sie einen HTML-Farbnamen oder eine Farbe im Hex-Format (#RRGGBB) in das Textfeld ein, oder wählen Sie eine Farbe aus der Auswahlliste. ");
        $info .= _("Diese Farbe wird seitenweit für Verweise zu bereits besuchten Zielen benutzt.");
        $title = _("Linkfarbe (besucht):");

        return $this->editColorGeneric($attribute, $title, $info);
    }

    /**
    * Prints out a form for entering the link color in the body tag
    *
    * @param string name The name of the text field.
    * @param string value The value for the text pre-emption.
    */

    function editAlink ($attribute) {
        $info = _("Geben Sie einen HTML-Farbnamen oder eine Farbe im Hex-Format (#RRGGBB) in das Textfeld ein, oder wählen Sie eine Farbe aus der Auswahlliste. ");
        $info .= _("Diese Farbe wird seitenweit für aktivierte Verweise benutzt.");
        $title = _("Linkfarbe (aktiviert):");

        return $this->editColorGeneric($attribute, $title, $info);
    }

    /**
    * Prints out a text field and a selection list for entering the color of
    * a table border.
    *
    * The name of the text field is given by $name. The name of the selection list
    * is $name . "_list".
    *
    * @param string title the
    * @param string name the name of the text field and selection list
    * @param string value
    */

    function editColorGeneric ($attribute, $title, $info) {
        $form_name = $this->element_name . "_" . $attribute;
        $value = $this->getValue($attribute);

        $colors = [_("keine Auswahl") => "", "aliceblue" => "#F0F8FF", "antiquewhite" => "#FAEBD7",
                  "aquamarine" => "#7FFFD4", "azure" => "#F0FFFF", "beige" => "#F5F5DC",
                            "blueviolet" => "#8A2BE2", "brown" => "#A52A2A", "burlywood" => "#DEB887",
                            "cadetblue" => "#5F9EA0", "chartreuse" => "#7FFF00",
                            "chocolate" => "#D2691E", "coral" => "#FF7F50",
                            "cornflowerblue" => "#6495ED", "cornsilk" => "#FFF8DC",
                            "crimson" => "#DC143C", "darkblue" => "#00008B", "darkcyan" => "#008B8B",
                            "darkgoldenrod" => "#B8860B", "darkgray" => "#A9A9A9",
                            "darkgreen" => "#006400", "darkkhaki" => "#BDB76B",
                            "darkmagenta" => "#8B008B", "darkolivegreen" => "#556B2F",
                            "darkorange" => "#FF8C00", "darkorchid" => "#9932CC",
                            "darkred" => "#8B0000", "darksalmon" => "#E9967A",
                            "darkseagreen" => "#8FBC8F", "darkslateblue" => "#483D8B",
                            "darkslategray" => "#2F4F4F", "darkturquoise" => "#00CED1",
                            "darkviolet" => "#9400D3", "deeppink" => "#FF1493",
                            "deepskyblue" => "#00BFFF", "dimgray" => "#696969",
                            "dodgerblue" => "#1E90FF", "firebrick" => "#B22222",
                            "floralwhite" => "#FFFAF0", "forestgreen" => "#228B22",
                            "gainsboro" => "#DCDCDC", "ghostwhite" => "#F8F8FF", "gold" => "#FFD700",
                            "goldenrod" => "#DAA520", "greenyellow" => "#ADFF2F",
                            "honeydew" => "#F0FFF0", "hotpink" => "#FF69B4", "indianred" => "#CD5C5C",
                            "indigo" => "#4B0082", "ivory" => "#FFFFF0", "khaki" => "#F0E68C",
                            "lavender" => "#E6E6FA", "lavenderblush" => "#FFF0F5",
                            "lawngreen" => "#7CFC00", "lemonchiffon" => "#FFFACD",
                            "lightblue" => "#ADD8E6", "lightcoral" => "#F08080",
                            "lightcyan" => "#E0FFFF", "lightgoldenrodyellow" => "#FAFAD2",
                            "lightgreen" => "#90EE90", "lightgrey" => "#D3D3D3",
                            "lightpink" => "#FFB6C1", "lightsalmon" => "#FFA07A",
                            "lightseagreen" => "#20B2AA", "lightskyblue" => "#87CEFA",
                            "lightslategray" => "#778899", "lightsteelblue" => "#B0C4DE",
                            "lightyellow" => "#FFFFE0", "limegreen" => "#32CD32",
                            "linen" => "#FAF0E6", "mediumaquamarine" => "#66CDAA",
                            "mediumblue" => "#0000CD", "mediumorchid" => "#BA55D3",
                            "mediumpurple" => "#9370DB", "mediumseagreen" => "#3CB371",
                            "mediumslateblue" => "#7B68EE", "mediumspringgreen" => "#00FA9A",
                            "mediumturquoise" => "#48D1CC", "mediumvioletred" => "#C71585",
                            "midnightblue" => "#191970", "mintcream" => "#F5FFFA",
                            "mistyrose" => "#FFE4E1", "moccasin" => "#FFE4B5",
                            "navajowhite" => "#FFDEAD", "oldlace" => "#FDF5E6",
                            "olivedrab" => "#6B8E23", "orange" => "#FFA500", "orangered" => "#FF4500",
                            "orchid" => "#DA70D6", "palegoldenrod" => "#EEE8AA", "palegreen" => "#98FB98",
                            "paleturquoise" => "#AFEEEE", "palevioletred" => "#DB7093", "papayawhip" => "#FFEFD5",
                            "peachpuff" => "#FFDAB9", "peru" => "#CD853F", "pink" => "#FFC0CB",
                            "plum" => "#DDA0DD", "powderblue" => "#B0E0E6", "rosybrown" => "#BC8F8F",
                            "royalblue" => "#4169E1", "saddlebrown" => "#8B4513", "salmon" => "#FA8072",
                            "sandybrown" => "#F4A460", "seagreen" => "#2E8B57", "seashell" => "#FFF5EE",
                            "sienna" => "#A0522D", "skyblue" => "#87CEEB", "slateblue" => "#6A5ACD",
                            "slategray" => "#708090", "snow" => "#FFFAFA", "springgreen" => "#00FF7F",
                            "steelblue" => "#4682B4", "tan" => "#D2B48C", "thistle" => "#D8BFD8",
                            "tomato" => "#FF6347", "turquoise" => "#40E0D0", "violet" => "#EE82EE",
                            "wheat" => "#F5DEB3", "whitesmoke" => "#F5F5F5", "yellowgreen" => "#9ACD32"];

        $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

        $out = "<label $invalidClass for=\"_${form_name}\">$title";
        $out .= tooltipIcon($info);
        $out .= "<section class=\"hgroup\">";
        $out .= "<input type=\"text\" name=\"$form_name\" class=\"no-hint\" ";
        $out .= " maxlength=\"20\" value=\"$value\">\n";

        $out .= "<select name=\"_{$form_name}\"";
        $out .= "onChange=\"document.edit_form.{$form_name}.value=document.edit_form._{$form_name}.";
        $out .= "options[document.edit_form._{$form_name}.selectedIndex].value;\" ";
        $out .= " class=\"nested-select\">\n";
        foreach ($colors as $color_name => $color_value) {
            if ($value == $color_value) {
                $out .= "<option selected=\"selected\" ";
            } else {
                $out .= "<option ";
            }
            if (!$color_value) {
                $out .= ' class="is-placeholder" ';
            }
            $out .= "data-text-color=\"$color_value\" value=\"$color_value\">";
            $out .= $color_name . "</option>";
        }
        $out .= "</select>";

        $out .= "</section>";
        $out .= "</label>";

        return $out;
    }

    /**
    * Prints out a form for entering the cellpadding of a table.
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editCellpadding ($attribute) {
        $info = _("Geben Sie den Abstand zwischen Zelleninhalt und Zellenrand in Pixeln an.");
        $form_name = $this->element_name . "_" . $attribute;
        $value = $this->getValue($attribute);

        $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

        $out = "<label $invalidClass>";
        $out .= _("Cellpadding:");
        $out .= tooltipIcon($info);
        $out .= "<section class=\"hgroup\">";
        $out .= "<input type=\"text\" name=\"$form_name\" size=\"2\"";
        $out .= " maxlength=\"2\" class=\"no-hint size-s \" value=\"$value\">&nbsp;Pixel";
        $out .= "</section>";
        $out .= "</label>";

        return $out;
    }

    /**
    * Prints out a form for entering the cellspacing of a table.
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editCellspacing ($attribute) {
        $info = _("Geben Sie den Abstand zwischen benachbarten Zellen in Pixeln an.");
        $form_name = $this->element_name . "_" . $attribute;
        $value = $this->getValue($attribute);

        $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

        $out = "<label ".$invalidClass.">";
        $out .= _("Cellspacing:");
        $out .= tooltipIcon($info);
        $out .= "<section class=\"hgroup\">";
        $out .= "<input type=\"text\" name=\"$form_name\" size=\"2\"";
        $out .= " maxlength=\"2\" class=\"no-hint size-s\" value=\"$value\">&nbsp;Pixel";
        $out .= "</section>";
        $out .= "</label>";

        return $out;
    }

    /**
    * Prints out a form for entering the width of a html-element (e.g. &lt;td&gt;, &lt;table&gt;).
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editWidth ($attribute) {
        $info = _("Geben Sie die Breite des Elements in Prozent oder Pixeln an.");
        $form_name = $this->element_name . "_" . $attribute;
        $value = $this->getValue($attribute);
        $value_pp = "";

        if (mb_substr($value, -1) == "%") {
            $value_pp = "%";
            $value = mb_substr($value, 0, -1);
        }

        $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

        $out = "<label $invalidClass for=\"$form_name\">";
        $out .= _("Breite:");
        $out .= tooltipIcon($info);
        $out .= "</label>";

        $out .= "<section class=\"hgroup\">";
        $out .= "<input type=\"text\" name=\"$form_name\" size=\"3\"";
        $out .= " maxlength=\"3\" class=\"no-hint size-s\" value=\"$value\">&nbsp;\n";

        $out .= "<label $invalidClass>";
        $out .= "<input type=\"radio\" name=\"{$form_name}pp\" value=\"%\"";
        if ($value_pp == "%")
            $out .= " checked=\"checked\"";
        $out .= ">";
        $out .= _("Prozent");
        $out .= "</label><label $invalidClass>";
        $out .= "<input type=\"radio\" name=\"";
        $out .= $form_name . "pp\" value=\"\"";
        if ($value_pp == "")
            $out .= " checked=\"checked\"";
        $out .= ">";
        $out .= _("Pixel");
        $out .= "</label>";
        $out .= "</section>";

        return $out;
    }

    /**
    * Prints out a form for entering the horizontal alignment of a html-element (e.g. &lt;td&gt;, &lt;table&gt;).
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editAlign ($attribute) {
        $info = _("Wählen Sie aus der Auswahlliste die Art der horizontalen Ausrichtung.");
        $form_name = $this->element_name . "_" . $attribute;
        $value = $this->getValue($attribute);

        $align_types = [
            "" => _("keine Auswahl"),
            "left" => _("linksbündig"),
            "right" => _("rechtsbündig"),
          "center" => _("zentriert")
        ];
        $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

        $out = "<label $invalidClass>";
        $out .= _("horizontale Ausrichtung:")."\n";
        $out .= tooltipIcon($info);
        $out .= "<select name=\"$form_name\" size=\"1\">\n";
        foreach ($align_types as $align_type => $align_name) {
            if ($value == $align_type)
                $out .= "<option selected=\"selected\" ";
            else
                $out .= "<option ";
            $out .= "value=\"$align_type\">";
            $out .= $align_name . "</option>";
        }
        $out .= "</select>";
        $out .= "</label>";

        return $out;
    }
    
    /**
    * Prints out a form for entering the vertikal alignment of a html-element (e.g. &lt;td&gt;, &lt;table&gt;).
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editValign ($attribute) {
        $info = _("Wählen Sie aus der Auswahlliste die Art der vertikalen Ausrichtung.");
        $form_name = $this->element_name . "_" . $attribute;
        $value = $this->getValue($attribute);

        $valign_types = [
            "" => _("keine Auswahl"),
            "top" => _("obenbündig"),
            "bottom" => _("untenbündig"),
          "center" => _("zentriert")
        ];
        $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

        $out = "<label $invalidClass>";
        $out .= _("vertikale Ausrichtung:")."\n";
        $out .= "<select name=\"$form_name\" size=\"1\">\n";
        foreach ($valign_types as $valign_type => $valign_name) {
            if ($value == $valign_type)
                $out .= "<option selected=\"selected\" ";
            else
                $out .= "<option ";
            $out .= "value=\"$valign_type\">";
            $out .= $valign_name . "</option>";
        }
        $out .= "</select>";
        $out .= "</label>";

        return $out;
    }

    /**
    * Prints out a form for entering the font-size.
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editSize ($attribute) {
        $info = _("Geben Sie die relative Schriftgröße an.");
        $title = _("Schriftgröße:");
        $values = ["", "1", "2", "3", "4", "5", "6", "7"];
        $names = [_("keine Auswahl"), "1", "2", "3", "4", "5", "6", "7"];

        return $this->editOptionGeneric($attribute, $title, $info, $values, $names, 1, FALSE);
    }

    /**
    * Prints out a form for entering the font-face.
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editFace ($attribute) {
        $title = _("Schriftart:");
        $info = _("Wählen Sie eine Schriftart aus.");

        return $this->editFaceGeneric($attribute, $title, $info);
    }

    /**
    * Prints out a form for entering the css-classname of a html-element.
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editClass ($attribute) {
        $info = _("Geben Sie einen CSS-Klassennamen aus Ihrer Stylesheet-Definition an.");
        $title = _("CSS-Klasse:");

        return $this->editTextfieldGeneric($attribute, $title, $info, 30, 128);
    }

    /**
    * Prints out a form for entering css-styles of a html-element
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editStyle ($attribute) {
        $info = _("Geben Sie Style-Sheet-Angaben ein.");
        $title = _("Style:");

        return $this->editTextfieldGeneric($attribute, $title, $info, 35, 250);
    }

    /**
    * Prints out a form for entering the title of a html-page.
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editTitle ($attribute) {
        $info = _("Geben Sie einen Seitentitel an.");
        $title = _("Seiten-Titel:");

        return $this->editTextfieldGeneric($attribute, $title, $info, 35, 128);
    }

    /**
    * Prints out a form for choosing between a horizontal or vertikal zebra-effect
    * on table rows/columns.
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editZebraTd ($attribute) {
        $info = _("Aktivieren Sie einen vertikalen oder horizontalen Zebra-Effekt für Tabellenzeilen/-spalten. ");
        $info .= _("Geben Sie hierfür eine zweite Hintergrundfarbe an.");
        $title = _("Zebra-Effekt:");
        $names = [_("aus"), _("horizontal"), _("vertikal")];
        $values = ["", "HORIZONTAL", "VERTICAL"];

        return $this->editRadioGeneric($attribute, $title, $info, $values, $names);
    }

    /**
    * Prints out a form for activating a zebra-effect on th-tags.
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editZebraTh ($attribute) {
        $info = _("Aktivieren Sie einen Zebra-Effekt für die Spaltenüberschriften. ");
        $info .= _("Geben Sie hierfür eine zweite Hintergrundfarbe an.");
        $title = _("Zebra-Effekt:");
        $names = [_("aus"), _("an")];
        $values = ["", "1"];

        return $this->editRadioGeneric($attribute, $title, $info, $values, $names);
    }

    /**
    * Prints out a form for entering the URL of a background picture for the hole document.
    *
    * @access   public
    * @param    string attribute The name of the attribute (syntax: HTML-TAG_HTML-ATTRIBUTE).
    * @return   string A complete table row includes a closed table with the form.
    */
    function editBackground ($attribute) {
        $info = _("Geben Sie die URL eines Bildes an, das als Hintergrundbild für die gesamte Seite dienen soll.");
        $title = _("Hintergrundbild:");

        return $this->editTextfieldGeneric($attribute, $title, $info, 35, 150);
    }

}

?>
