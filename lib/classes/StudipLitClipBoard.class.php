<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitClipBoard.class.php
// Class to
//
// Copyright (c) 2003 André Noack <noack@data-quest.de>
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

/**
 *
 * @access   public
 * @author   André Noack <noack@data-quest.de>
 * @package
 **/
class StudipLitClipBoard {

    var $dbv;
    var $elements = null;
    var $form_obj = null;
    var $form_name = "lit_clipboard_form";


    function GetInstance(){
        static $instance;
        if (!is_object($instance[0])){
            $instance[0] = new StudipLitClipBoard();
        }
        return $instance[0];
    }

    function __construct(){
        $this->dbv = DbView::getView('literatur');
        $this->elements =& $_SESSION["_lit_clipboard_elements"];
    }

    function insertElement($id_to_insert){
        if (!is_array($id_to_insert)){
            $id_to_insert = [$id_to_insert];
        }
        $inserted = 0;
        foreach ($id_to_insert as $catalog_id){
            if (!isset($this->elements[$catalog_id])){
                $this->elements[$catalog_id] = true;
                ++$inserted;
            }
        }
        if ($inserted == 1){
            PageLayout::postSuccess('Es wurde ein Literaturverweis in Ihre Merkliste aufgenommen');
        } else if ($inserted){
            PageLayout::postSuccess(sprintf(_('Es wurden %s Literaturverweise in Ihre Merkliste aufgenommen.'), $inserted));
        }
        $this->setDefaultValue();
        return $inserted;
    }

    function deleteElement($id_to_delete){
        if (!is_array($id_to_delete)){
            $id_to_delete = [$id_to_delete];
        }
        $deleted = 0;
        foreach ($id_to_delete as $catalog_id){
            if (isset($this->elements[$catalog_id])){
                unset($this->elements[$catalog_id]);
                ++$deleted;
            }
        }
        if ($deleted == 1){
            PageLayout::postSuccess(_('Es wurde ein Literaturverweis aus Ihrer Merkliste gelöscht'));
        } else if ($deleted){
            PageLayout::postSuccess(sprintf(_('Es wurden %s Literaturverweise aus Ihrer Merkliste gelöscht.'), $deleted));
        }
        $this->setDefaultValue();
        return $deleted;
    }

    function getNumElements(){
        return (is_array($this->elements)) ? count($this->elements) : 0;
    }

    function isInClipboard($catalog_id){
        return isset($this->elements[$catalog_id]);
    }

    function getElements(){
        $returned_elements = null;
        if (is_array($this->elements)){
            $this->dbv->params[0] = array_keys($this->elements);
            $this->elements = null;
            $rs = $this->dbv->get_query("view:LIT_GET_CLIP_ELEMENTS");
            while ($rs->next_record()){
                $returned_elements[$rs->f("catalog_id")] = $rs->f("short_name");
                $this->elements[$rs->f("catalog_id")] = true;
            }
        }
        return $returned_elements;
    }

    function &getFormObject(){
        if (!is_object($this->form_obj)){
            $this->setFormObject();
        }
        $this->setDefaultValue();
        return $this->form_obj;
    }

    function setDefaultValue(){
        if ($this->getNumElements() == 1 && is_object($this->form_obj)){
            reset($this->elements);
            $this->form_obj->form_fields['clip_content']['default_value'] = key($this->elements);
            return true;
        }
        return false;
    }

    function setFormObject(){
        $form_name = $this->form_name;
        $form_fields['clip_content'] = ['type' => 'select', 'multiple' => true, 'options_callback' => [$this, "getClipOptions"]];
        $form_fields['clip_cmd'] = ['type' => 'select', 'options' => [['name' => _("Aus Merkliste löschen"), 'value' => 'del']]];
        $form_buttons['clip_ok'] = ['type' => 'accept', 'caption' => _('OK'), 'info' => _("Gewählte Aktion starten")];
        if (!is_object($this->form_obj)){
            $this->form_obj = new StudipForm($form_fields, $form_buttons, $form_name, false);
        } else {
            $this->form_obj->form_fields = $form_fields;
        }
        return true;
    }

    function getClipOptions($caller, $name){
        $options = [];
        $cols = 40;
        if ($elements = $this->getElements()){
            foreach ($elements as $catalog_id => $title){
                $options[] = ['name' => my_substr($title,0,$cols), 'value' => $catalog_id];
            }
        } else {
            $options[] = ['name' => ("Ihre Merkliste ist leer!"), 'value' => 0];
            $options[] = ['name' => str_repeat("¯",floor($cols * .8)) , 'value' => 0];
        }
        return $options;
    }

    function doClipCmd(){
        $this->getFormObject();
        switch ($this->form_obj->getFormFieldValue("clip_cmd")){
            case "del":
                $selected = $this->form_obj->getFormFieldValue("clip_content");
                if (is_array($selected)){
                    $this->deleteElement($selected);
                    $this->form_obj->doFormReset();
                } else {
                    PageLayout::postInfo(_('Sie haben keinen Eintrag in Ihrer Merkliste ausgewählt!'));
                }
                break;
        }
    }
}

//test
/*
page_open(array("sess" => "Seminar_Session"));
$test = new StudipLitClipBoard();
$test->insertElement("4a0b71db53eaca61dc51f1ba581abe22");
$test->insertElement("c74cf4c401f969d786ff1bd68205d9ad");
$test->insertElement("322d5cc958c70753718bfc288e7bdbde");
echo "<pre>";
$test2 =& $test->getFormObject();
echo $test2->getFormField("clip_content");
print_r($test->getFormObject());
*/
?>
