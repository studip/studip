<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementSelectSubjectAreas.class.php
*
*
*
*
* @author       Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementReplaceTextSemType
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementReplaceTextSemType.class.php
//
// Copyright (C) 2006 Peter Thienel <thienel@data-quest.de>,
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

class ExternElementSelectSubjectAreas extends ExternElement {

    var $attributes = [];
    var $selector;
    var $all_ranges = [];

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = '') {
        if ($config != '')
            $this->config = $config;

        $this->name = "SelectSubjectAreas";
        $this->real_name = _("Auswahl der anzuzeigenden Studienbereiche");
        $this->description = _("Sie können hier die Studienbereiche auswählen, die auf der externen Seite ausgegeben werden sollen.");
        $this->attributes = ['subjectareasselected', 'selectallsubjectareas', 'reverseselection'];

    }

    /**
    *
    */
    function getDefaultConfig () {

        $config['subjectareasselected'] = '';
        $config['subjectareasselected'] .= '|' . implode('|', $this->all_ranges);
        $config['selectallsubjectareas'] = '1';
        $config['reverseselection'] = '';

        return $config;
    }

    function toStringEdit ($post_vars = "", $faulty_values = "",
            $edit_form = "", $anker = "") {

        if ($faulty_values == '')
            $faulty_values = [];
        $out = '';
        $table = '';
        if ($edit_form == '')
            $edit_form = new ExternEditHtml($this->config, $post_vars, $faulty_values, $anker);

        $edit_form->setElementName($this->getName());
        $element_headline = $this->getEditFormHeadline($edit_form);

        $title = _('Alle Studienbereiche anzeigen') . ':';
        $info = _("Wählen Sie diese Option, wenn alle Veranstaltungen aus allen Studienbereichen angezeigt werden sollen - unabhängig von unten vorgenommener Auswahl.");
        $values = '1';
        $names = '';
        $table = $edit_form->editCheckboxGeneric('selectallsubjectareas', $title, $info, $values, $names);
        $table .= $edit_form->editSelectSubjectAreas(new StudipSemTreeSearch('dummy', 'SelectSubjectAreas', FALSE));

        $title = _('Auswahl umkehren') . ':';
        $info = _("Wählen Sie diese Option, wenn Veranstaltungen aus den ausgewählten Bereichen nicht angezeigt werden sollen.");
        $values = '1';
        $names = '';
        $table .= $edit_form->editCheckboxGeneric('reverseselection', $title, $info, $values, $names);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return  $element_headline . $out;
    }

    function executeCommand ($command, $value = "") {

        if ($command == 'do_search_x') {
            $GLOBALS['com'] = 'edit';
            $this->config->setValue($this->name, 'subjectareasselected',
                    $_POST['SelectSubjectAreas_subjectareasselected']);
        }
        return TRUE;
    }


    function checkValue ($attribute, $value) {
        if ($attribute == 'selectallsubjectareas') {
            // This is necessary for checkbox-values. If there is no checkbox
            // checked, the variable is not declared and it is necessary to set the
            // variable to "".
            if (!isset($_POST[$this->name . '_' . $attribute])) {
                $_POST[$this->name . '_' . $attribute] = '';
                return FALSE;
            }
            return !($value == '1' || $value == '');
        }
        if ($attribute == 'subjectareasselected' && sizeof($_POST[$this->name . '_selectallsubjectareas'])) {
            return ($value == '0');
        }

        if ($attribute == 'subjectareasselected') {
            if (!is_array($_POST[$this->name . '_' . $attribute])) {
                $_POST[$this->name . '_' . $attribute] = '';
                return FALSE;
            }
        }

        if ($attribute == 'reverseselection') {
            // This is necessary for checkbox-values. If there is no checkbox
            // checked, the variable is not declared and it is necessary to set the
            // variable to "".
            if (!isset($_POST[$this->name . '_' . $attribute])) {
                $_POST[$this->name . '_' . $attribute] = '';
                return FALSE;
            }
            return !($value == '1' || $value == '');
        }

        return FALSE;
    }

}
