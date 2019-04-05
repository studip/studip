<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ClipBoard.class.php
// a generic clipboard-class to use in Stud.IP
//
// Copyright (c) 2004 André Noack <noack@data-quest.de>, Cornelis Kater <kater@data-quest.de>,
// data-quest GmbH <info@data-quest.de>
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
* ClipBoard
*
* a generic clipboard class to use in Stud.IP
*
* @access   public
* @author   André Noack <noack@data-quest.de>, Cornelis Kater <kater@data-quest.de>,
* @package  core
**/
class ClipBoard {

    var $elements = null;
    var $form_obj = null;
    var $object_types = null;
    var $form_name = "clipboard_form";
    var $msg;
    //querys for different object_types
    var $elements_query = array (
                "sem" => "SELECT Seminar_id, Name  FROM Seminare WHERE Seminar_id  IN (?) ORDER BY Name",
                "user" => "SELECT user_id, CONCAT(Nachname, ', ', Vorname) AS name FROM auth_user_md5 WHERE user_id  IN (?) ORDER BY name",
                "inst" => "SELECT Institut_id, Name  FROM Institute WHERE Institut_id  IN (?) ORDER BY Name",
                "date" => "SELECT termin_id, content  FROM termine WHERE termin_id IN (?) ORDER BY content",
                "res" => "SELECT resource_id, name  FROM resources_objects WHERE resource_id  IN (?) ORDER BY name"
                );
    var $object_types_short = array(
                "sem" => "S",
                "user" => "N",
                "inst" => "E",
                "date" => "T",
                "res" => "R"
                );



    public static function GetInstance($name)
    {
        static $instance = [];
        if (!is_object($instance[$name])) {
            $instance[$name] = new static($name);
        }
        return $instance[$name];
    }

    public function __construct($name)
    {
        $this->form_name = $name."_clipboard_form";
        $this->elements =& $_SESSION["_".$this->form_name];
    }

    public function insertElement($id_to_insert, $object_type)
    {
        if (!is_array($id_to_insert)) {
            $id_to_insert = array($id_to_insert);
        }
        $inserted = 0;
        foreach ($id_to_insert as $object_id) {
            if (!isset($this->elements[$object_id])) {
                $this->elements[$object_id] = $object_type;
                ++$inserted;
            }
        }
        if ($inserted == 1) {
            $this->msg .= "msg§" . _("Es wurde ein Verweis in Ihre Merkliste aufgenommen.") . "§";
        } else if ($inserted) {
            $this->msg .= "msg§" . sprintf(_("Es wurden %s Verweise in Ihre Merkliste aufgenommen."), $inserted) . "§";
        }
        $this->setDefaultValue();
        return $inserted;
    }

    public function deleteElement($id_to_delete)
    {
        if (!is_array($id_to_delete)) {
            $id_to_delete = array($id_to_delete);
        }
        $deleted = 0;
        foreach ($id_to_delete as $clip_obj_id) {
            if (isset($this->elements[$clip_obj_id])) {
                unset($this->elements[$clip_obj_id]);
                ++$deleted;
            }
        }
        if ($deleted == 1) {
            $this->msg .= "msg§" . _("Es wurde ein Verweis aus Ihrer Merkliste entfernt.") . "§";
        } else if ($deleted) {
            $this->msg .= "msg§" . sprintf(_("Es wurden %s Verweis aus Ihrer Merkliste entfernt."), $deleted) . "§";
        }
        $this->setDefaultValue();
        return $deleted;
    }

    public function getNumElements()
    {
        return is_array($this->elements) ? count($this->elements) : 0;
    }

    public function isInClipboard($id_to_check)
    {
        return isset($this->elements[$id_to_check]);
    }

    public function getElements()
    {
        $returned_elements = null;
        if (is_array($this->elements)) {
            foreach($this->elements as $object_id=>$object_type) {
                $this->object_types[$object_type][] = $object_id;
            }
            $this->elements = null;
            if (is_array($this->object_types))
                foreach($this->object_types as $object_type => $object_elements) {
                    if (empty($object_elements)) {
                        continue;
                    }

                    $statement = DBManager::get()->prepare($this->elements_query[$object_type]);
                    $statement->execute(array($object_elements));

                    while ($row = $statement->fetch(PDO::FETCH_NUM)) {
                        $returned_elements[$row[0]] = array("name" => $row[1], "type" => $object_type);
                        $this->elements[$row[0]] = $object_type;
                    }
                }
        }
        return $returned_elements;
    }

    public function getFormObject()
    {
        if (!is_object($this->form_obj)) {
            $this->setFormObject();
        }
        $this->setDefaultValue();
        return $this->form_obj;
    }

    public function setDefaultValue()
    {
        if ($this->getNumElements() == 1 && is_object($this->form_obj)) {
            reset($this->elements);
            $this->form_obj->form_fields['clip_content']['default_value'] = key($this->elements);
            return true;
        }
        return false;
    }

    public function setFormObject()
    {
        $form_name = $this->form_name;
        $form_fields['clip_content'] = array('type' => 'select', 'multiple' => true, 'options_callback' => array($this, "getClipOptions"));
        $form_fields['clip_cmd'] = array('type' => 'select', 'options' => array(array('name' => _("Aus Merkliste löschen"), 'value' => 'del')));
        $form_buttons['clip_ok'] = array('type' => 'accept', 'caption' => _('OK'), 'info' => _("Gewählte Aktion starten"));
        if (!is_object($this->form_obj)) {
            $this->form_obj = new StudipForm($form_fields, $form_buttons, $form_name, false);
        } else {
            $this->form_obj->form_fields = $form_fields;
        }
        return true;
    }

    public function getClipOptions($caller, $name)
    {
        $options = array();
        $cols = 40;
        if ($elements = $this->getElements()) {
            foreach ($elements as $clip_obj_id => $object_data) {
                $options[] = array('name' => $this->object_types_short[$object_data["type"]].": ". my_substr($object_data["name"],0,$cols), 'value' => $clip_obj_id);
            }
        } else {
            $options[] = array('name' => ("Ihre Merkliste ist leer!"), 'value' => 0);
            $options[] = array('name' => str_repeat("¯",floor($cols * .8)) , 'value' => 0);
        }
        return $options;
    }

    public function showClip($title = true, $width = '250px')
    {
        $this->getFormObject();

        if ($title) {
            echo '<div align="center"><b>' . _('Merkliste:') . '</b></div>';
        }
        echo $this->form_obj->getFormField("clip_content", array_merge(array('size' => $this->getNumElements()), array('style' => 'font-size:8pt;width:' . $width)));
        echo $this->form_obj->getFormField("clip_cmd", array('style' => 'font-size:8pt;width:' . $width));

        echo '<div align="center">';
        print $this->form_obj->getFormButton("clip_ok", array('style'=>'vertical-align:middle;margin:3px;'));
        if ($this->form_obj->form_buttons['clip_reload']) {
            print $this->form_obj->getFormButton("clip_reload", array('style'=>'vertical-align:middle;margin:3px;'));
        }
        echo '</div>';
    }

    public function doClipCmd()
    {
        $this->getFormObject();
        switch ($this->form_obj->getFormFieldValue("clip_cmd")) {
            case "del":
                $selected = $this->form_obj->getFormFieldValue("clip_content");
                if (is_array($selected)) {
                    $this->deleteElement($selected);
                    $this->form_obj->doFormReset();
                }
                break;
        }
    }
}
?>
