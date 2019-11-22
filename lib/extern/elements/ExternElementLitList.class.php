<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* ExternElementLitList.class.php
*
*
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  extern
* @module       ExternElementLitList
* @package  studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementLitList.class.php
//
// Copyright (C) 2007 Peter Thienel <thienel@data-quest.de>,
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

require_once 'ExternElementTemplateGeneric.class.php';

class ExternElementLitList extends ExternElement {

    var $attributes = ['div_style_change', 'div_class_change', 'span_style_name', 'span_class_name',
            'showlastchange', 'formatting'];
    var $marker_structure = [
            'LITLIST' => [
                    'LILIST_ITEM' => ['LITLIST_ITEM_ELEMENT'],
                    'LITLIST_NAME']];
    var $rendered_content = '';

    /**
    * Constructor
    *
    * @param array config
    */
    function __construct($config = "") {
        if ($config) {
            $this->config = $config;
        }

        $this->name = "LitList";
        $this->real_name = _("Literaturliste");
        $this->description = _("Eigenschaften einer Literaturliste.");
    }

    function getMarkerDescription ($element_name) {
        $markers['LitList'] = [
            ['<!-- BEGIN LITLISTS -->', ''],
            ['<!-- BEGIN LITLIST -->', ''],
            ['###LITLIST_FULLNAME###',''],
            ['###LITLIST_NAME###',''],
            ['###LITLIST_CHANGE-DATE###',''],
            ['<!-- BEGIN LITLIST_ITEM -->',''],
            ['###LITLIST_ITEM_FULLNAME###',''],
            ['###LITLIST_ITEM_ELEMENT###',''],
            ['###LITLIST_ITEM_CHANGE-DATE###',''],
            ['<!-- END LITLIST_ITEM -->',''],
            ['<!-- END LITLIST -->',''],
            ['<!-- END LITLISTS -->','']
        ];
        return $markers[$element_name];
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

        $edit_form_headlines = [
                'div_change' => _("Bereich mit Änderungsdatum und Name (HTML-Tag &lt;div&gt;)"),
                'span_name' => _("Formatierung des Namens (HTML-Tag &lt;span&gt;)")];
        $content_table = $edit_form->getEditFormContent($this->attributes, $edit_form_headlines);
        $content_table .= $edit_form->editBlankContent();

        $headline = $edit_form->editHeadline(_("Weitere Optionen"));

        $title = _('Änderungsdatum anzeigen') . ':';
        $info = _("Ausgabe des Änderungsdatums und des Namens der Person, die die Änderungen vorgenommen hat");
        $content = $edit_form->editCheckboxGeneric('showlastchange', $title, $info, 1, '');

        $title = _('Alternative Formatierung') . ':';
        $info = _("Geben Sie hier eine alternative Formatierungsregel an, mit der alle Literaturlisten einheitlich Formatiert werden sollen. Wird keine Formatierung angegeben, wird die Formatierung benutzt, die der Autor der Literaturliste vorgesehen hat.");
        $content .= $edit_form->editTextareaGeneric('formatting', $title, $info, 5, 35);

        $content_table .= $edit_form->editContentTable($headline, $content);
        $content_table .= $edit_form->editBlankContent();

        $submit = $edit_form->editSubmit($this->config->getName(),
                $this->config->getId(), $this->getName());
        $out = $edit_form->editContent($content_table, $submit);
        $out .= $edit_form->editBlank();

        return  $element_headline . $out;
    }

    function checkValue ($attribute, $value) {
        if ($attribute == 'formatting') {
            return !preg_match(":^[0-9a-z{}*|#%+\-_^]{0,450}$:", $value);
        }
        if ($attribute == 'showlastchange') {
            if (!isset($_POST['LitList_showlastchange'])) {
                $_POST['LitList_showlastchange'] = 0;
                return FALSE;
            }

            return !($value == '1' || $value == '');
        }
    }

    function getContent ($args) {
        global $_fullname_sql;

        $content = [];
        $dbv = DbView::getView('literatur');
        if (is_array($args) && isset($args['user_id'])) {
            $tree = TreeAbstract::GetInstance("StudipLitList", $args['user_id']);
        } else {
            preg_match(':^([a-z_\-]{0,50})$:i', Request::quoted('username'), $matches);
            $tree = TreeAbstract::GetInstance("StudipLitList", get_userid($matches[1]));
        }
        if ($lists = $tree->getVisibleListIds()) {
            for ($i = 0; $i < count($lists); ++$i) {
                if ($this->config->getValue($this->name, 'showlastchange')) {
    //          && ($tree->tree_data[$lists[$i]]['chdate'] > $last_modified_since) ){
                    $content['LITLISTS']['LITLIST'][$i]['LITLIST_CHANGE-DATE'] = strftime($this->config->getValue('Main', 'dateformat'),
                            $tree->tree_data[$lists[$i]]['chdate']);
                    $content['LITLISTS']['LITLIST'][$i]['LITLIST_FULLNAME'] = $tree->tree_data[$lists[$i]]['fullname'];
                    $content['LITLISTS']['LITLIST'][$i]['LITLIST_NAME'] = $tree->tree_data[$lists[$i]]['name'];
                } else {
                    $content['LITLISTS']['LITLIST'][$i]['LITLIST_NAME'] = $tree->tree_data[$lists[$i]]['name'];
                }
                if ($tree->hasKids($lists[$i])){
                    $dbv->params[0] = $lists[$i];
                    $rs = $dbv->get_query("view:LIT_LIST_GET_ELEMENTS");
                    $j = 0;
                    while ($rs->next_record()){
                        if ( ($this->config->getValue($this->name, 'showlastchange'))) {
                //      && ($tree->tree_data[$rs->f('list_element_id')]['chdate'] > $last_modified_since) ){
                            $content['LITLISTS']['LITLIST'][$i]['LITLIST_ITEM'][$j]['LITLIST_ITEM_CHANGE-DATE'] = strftime($this->config->getValue('Main', 'dateformat'),
                                    $tree->tree_data[$rs->f('list_element_id')]['chdate']);
                            $content['LITLISTS']['LITLIST'][$i]['LITLIST_ITEM'][$j]['LITLIST_ITEM_FULLNAME'] = $tree->tree_data[$rs->f('list_element_id')]['fullname'];
                        }
                        $content['LITLISTS']['LITLIST'][$i]['LITLIST_ITEM'][$j]['LITLIST_ITEM_ELEMENT'] = ExternModule::ExtFormatReady($tree->getFormattedEntry($rs->f('list_element_id'), $rs->Record));
                        $j++;
                    }
                }
            }
        }

        return $content;
    }
}
