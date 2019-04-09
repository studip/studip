<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementTemplateGeneric.class.php
*
*
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementTemplateGeneric
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementTemplateGeneric.class.php
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

require_once 'vendor/exTpl/Template.php';

class ExternElementTemplateGeneric extends ExternElement {

    var $attributes = ['template'];
    var $markers;
    var $new_datafields = FALSE;


    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = '') {
        if ($config)
            $this->config = $config;

        $this->name = "TemplateGeneric";
        $this->real_name = _("Standard Template");
        $this->description = _("Hier kann ein Template hinterlegt werden.");
    }

    /**
    *
    */
    function getDefaultConfig () {
        $config = [
            'template' => _("Geben Sie hier das Template ein.")
        ];

        return $config;
    }

    function toStringEdit ($post_vars = '', $faulty_values = '',
            $edit_form = '', $anker = '') {
        global $EXTERN_MODULE_TYPES;

        if ($faulty_values == '')
            $faulty_values = [];
        $out = '';
        $tag_headline = '';
        $table = '';
        if ($edit_form == '')
            $edit_form = new ExternEditHtml($this->config, $post_vars, $faulty_values, $anker);

        $edit_form->setElementName($this->getName());
        $element_headline = $this->getEditFormHeadline($edit_form);

        $headline = $edit_form->editHeadline(_("Beschreibung der Marker"));
        $table = $edit_form->editMarkerDescription($this->markers, $this->new_datafields);

        $content_table = $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Template"));
        $info = _("Geben Sie hier das Template ein.");
        $table = $edit_form->editTextareaGeneric('template', '', $info, 40, 80);

        $content_table .= $edit_form->editContentTable($headline, $table);
        $content_table .= $edit_form->editBlankContent();

        $hidden = [$this->getName . '_chdate' => time()];
        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName(), $hidden);
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return  $element_headline . $out;
    }

    function toString ($args = null) {
        $template = $this->config->getValue($this->getName(), 'template');
        $template = preg_replace(
            ['/###([\w-]+)###/', '/<!--\s+BEGIN\s+([\w-]+)\s+-->/', '/<!--\s+END\s+[\w-]+\s+-->/'],
            ['{% $1 %}', '{% foreach $1 %}', '{% endforeach %}'], $template);
        exTpl\Template::setTagMarkers('{%', '%}');

        try {
            $template = new exTpl\Template($template);
            $out = $template->render((array) $args['content'] + (array) $args['content']['__GLOBAL__']);
        } catch (exTpl\TemplateParserException $ex) {
            $out = $GLOBALS['EXTERN_ERROR_MESSAGE'] . '<br>' . $ex->getMessage();
        }

        return $out;
    }

    function checkValue ($attribute, $value) {
        if ($attribute == 'template') {
        //  return preg_match("|^https?://.*$|i", $value);
            return FALSE;
        }
        return FALSE;
    }
}

?>
