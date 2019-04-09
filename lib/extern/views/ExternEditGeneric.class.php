<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternEditGeneric.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternEditGeneric
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternEditGeneric.class.php
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


class ExternEditGeneric extends ExternEdit {

    function __construct (&$config, $form_values = "", $faulty_values = "",
             $edit_element = "") {
        parent::__construct($config, $form_values, $faulty_values, $edit_element);
    }

    /**
    * Prints out a form with a pull-down field for different font-faces.
    *
    * @param string attribute The name of the attribute (Syntax: [tag-name]_[attribute_name])
    * @param string title The title of this form.
    * @param string info The info text.
    */
    function editFaceGeneric ($attribute, $title, $info) {
        $faces = [
            "" => _("keine Auswahl"),
            "Arial,Helvetica,sans-serif" => _("serifenlose Schrift"),
          "Times,Times New Roman,serif" => _("Serifenschrift"),
            "Courier,Courier New,monospace" => _("diktengleiche Schrift")
        ];
        $form_name = $this->element_name . "_" . $attribute;
        $value = $this->getValue($attribute);

        $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

        $out = "<label $invalidClass>$title\n";
        $out .= tooltipIcon($info);
        $out .= "<select name=\"$form_name\" size=\"1\">\n";
        foreach ($faces as $face_type => $face_name) {
            if ($value == $face_type)
                $out .= "<option selected=\"selected\" ";
            else
                $out .= "<option ";
            $out .= "value=\"$face_type\">";
            $out .= $face_name . "</option>";
        }
        $out .= "</select>";
        $out .= "</label>";

        return $out;
    }

    /**
    * Prints out a form with a text field.
    *
    * @param string attribute The name of the attribute (Syntax: [tag-name]_[attribute_name])
    * @param mixed title The title(s) of the textfield(s).
    * @param string info The info text.
    * @param int size The size (length) of this textfield.
    * @param int maxlength The maximal length of the text.
    */
    function editTextfieldGeneric ($attribute, $title, $info, $size, $maxlength) {
        $form_name = $this->element_name . "_" . $attribute;
        $value = $this->getValue($attribute);

        if (is_array($title)) {
            $out = "";
            for($i = 0; $i < count($title); $i++) {

                $invalidClass = $this->faulty_values[$form_name][$i] ? "class=\"invalid\" " : "";

                $out .= "<label $invalidClass>{$title[$i]}\n";
                $out .= tooltipIcon(is_array($info) ? $info[$i] : $info);
                $out .= "<input type=\"text\" name=\"{$form_name}[]\" size=\"$size\"";
                $out .= " maxlength=\"$maxlength\" value=\"{$value[$i]}\">";
                $out .= "</label>";
            }
            return $out;
        }

        $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

        $out .= "<label $invalidClass>$title\n";
        $out .= tooltipIcon($info);
        $out .= "<input type=\"text\" name=\"$form_name\" size=\"$size\"";
        $out .= " maxlength=\"$maxlength\" value=\"$value\">";
        $out .= "</label>";

        return $out;
    }

    /**
    * Prints out a Form with a textarea.
    *
    * @param string attribute The name of the attribute (Syntax: [tag-name]_[attribute_name])
    * @param string title The title of this textarea.
    * @param string info The info text.
    * @param int rows The number of rows of this textarea.
    * @param int cols The number of columns of this textarea.
    */
    function editTextareaGeneric ($attribute, $title, $info, $rows, $cols) {
        $form_name = $this->element_name . "_" . $attribute;
        $value = $this->getValue($attribute);

        $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

        $out = "<label $invalidClass>$title";
        $out .= tooltipIcon($info);
        $out .= "<textarea name=\"$form_name\" cols=\"$cols\" rows=\"$rows\" wrap=\"virtual\">";
        $out .= $value;
        $out .= "</textarea>";
        $out .= "</label>";

        return $out;
    }

    /**
    * Prints out a Form with checkboxes.
    *
    * @param string attribute The name of the attribute (Syntax: [tag-name]_[attribute_name])
    * @param string title The title of this form with checkboxes.
    * @param string info The info text.
    * @param array check_values The values of the checkboxes.
    * @param array check_names The names of the checkboxes.
    */
    function editCheckboxGeneric ($attribute, $title, $info, $check_values, $check_names) {
        $form_name = $this->element_name . "_" . $attribute;
        $value = $this->getValue($attribute);

        $size = 0;
        if(is_array($check_values)) {
            $size = count($check_values);
        }
        $out = "";

        if ($size > 1) {
        //  $form_name .= "[]";
            if (is_array($title)) {
                for ($i = 0; $i < $size; $i++) {

                    $invalidClass = $this->faulty_values[$form_name][$i] ? "class=\"invalid\" " : "";

                    $out .= "<label $invalidClass>";
                    $out .= "<input type=\"checkbox\" name=\"{$form_name}[]\" value=\"{$check_values[$i]}\"";
                    if (is_array($value) && in_array($check_values[$i], $value)) {
                        $out .= " checked";
                    }

                    if ($size == 1) {
                        $out .= ">";
                    } else {
                        $out .= ">{$check_names[$i]}";
                    }

                    $out .= $title[$i];
                    $out .= tooltipIcon($info);
                    $out .= "</label>";
                }
            }
            else {
                $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

                $out .= "<label $invalidClass>";
                for ($i = 0; $i < $size; $i++) {
                    $out .= "<input type=\"checkbox\" name=\"{$form_name}[]\" value=\"{$check_values[$i]}\"";
                    if (is_array($value) && in_array($check_values[$i], $value)) {
                        $out .= " checked";
                    }

                    if ($size == 1) {
                        $out .= ">";
                    } else {
                        $out .= ">{$check_names[$i]}";
                    }
                }

                $out .= $title;
                $out .= tooltipIcon($info);
                $out .= "</label>";
            }
        }
        else {
            $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

            $out .= "<label $invalidClass>";
            $out .= "<input type=\"checkbox\" name=\"{$form_name}\" value=\"$check_values\"";

            if ($value == $check_values) {
                $out .= " checked";
            }

            $out .= ">";

            $out .= $title;
            $out .= tooltipIcon($info);
            $out .= "</label>";
        }

        return $out;
    }

    /**
    * Prints out a Form with radio-buttons.
    *
    * @param string attribute The name of the attribute (Syntax: [tag-name]_[attribute_name])
    * @param string title The title of this form with radio-buttons.
    * @param string info The info text.
    * @param array radio_values The values of the radio-buttons.
    * @param array radio_names The names of the radio-buttons.
    */
    function editRadioGeneric ($attribute, $title, $info, $radio_values, $radio_names) {
        $form_name = $this->element_name . "_" . $attribute;
        $value = $this->getValue($attribute);

        $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

        $out = "<label $invalidClass>$title\n";
        $out .= tooltipIcon($info);

        $out .= "<br>";
        for ($i = 0; $i < count($radio_values); $i++) {
            $out .= "<input type=\"radio\" name=\"$form_name\" value=\"{$radio_values[$i]}\"";
            if ($value == $radio_values[$i])
                $out .= " checked";
            $out .= ">{$radio_names[$i]}";
        }
        $out .= "</label>";

        return $out;
    }

    /**
    * Prints out a Form with an option-list.
    *
    * @param string attribute The name of the attribute (Syntax: [tag-name]_[attribute_name])
    * @param string title The title of this option-list.
    * @param string info The info text.
    * @param array radio_values The values of the options.
    * @param array radio_names The names of the options.
    * @param int length The visible size of the option-list (default 1, pull-down).
    * @param boolean multiple Set this TRUE, if you want a multiple option-list (default FALSE)
    */
    function editOptionGeneric ($attribute, $title, $info, $option_values, $option_names,
            $size = 1, $multiple = FALSE) {

        $form_name = $this->element_name . "_" . $attribute;
        $value = $this->getValue($attribute);

        $invalidClass = $this->faulty_values[$form_name][0] ? "class=\"invalid\" " : "";

        $out = "<label $invalidClass>$title\n";
        $out .= tooltipIcon($info);
        if ($multiple)
            $out .= "<select name=\"{$form_name}[]\" size=\"$size\" multiple>";
        else
            $out .= "<select name=\"$form_name\" size=\"$size\">";

        for ($i = 0; $i < count($option_values); $i++) {
            $out .= "<option value=\"{$option_values[$i]}\"";
            if ($multiple) {
                if (in_array($option_values[$i], (array) $value)) {
                    $out .= " selected";
                }
            } else {
                if ($value == $option_values[$i]) {
                    $out .= " selected";
                }
            }
            $out .= ">{$option_names[$i]}</option>\n";
        }

        $out .= "</select>";
        $out .= "</label>";

        return $out;
    }

}
