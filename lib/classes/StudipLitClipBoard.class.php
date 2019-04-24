<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/**
 *
 * @access   public
 * @author   André Noack <noack@data-quest.de>
 * @license     GPL2 or any later version
 **/
class StudipLitClipBoard
{
    var $dbv;
    var $elements  = null;
    var $form_obj  = null;
    var $form_name = "lit_clipboard_form";
    
    public static function GetInstance()
    {
        static $instance;
        if (!is_object($instance[0])) {
            $instance[0] = new StudipLitClipBoard();
        }
        return $instance[0];
    }
    
    public function __construct()
    {
        $this->dbv      = DbView::getView('literatur');
        $this->elements =& $_SESSION["_lit_clipboard_elements"];
    }
    
    function insertElement($id_to_insert)
    {
        if (!is_array($id_to_insert)) {
            $id_to_insert = [$id_to_insert];
        }
        $inserted = 0;
        foreach ($id_to_insert as $catalog_id) {
            if (!isset($this->elements[$catalog_id])) {
                $this->elements[$catalog_id] = true;
                ++$inserted;
            }
        }
        if ($inserted === 1) {
            PageLayout::postSuccess('Es wurde ein Literaturverweis in Ihre Merkliste aufgenommen');
        } else if ($inserted) {
            PageLayout::postSuccess(sprintf(
                    _('Es wurden %s Literaturverweise in Ihre Merkliste aufgenommen.'),
                    $inserted)
            );
        }
        $this->setDefaultValue();
        return $inserted;
    }
    
    public function deleteElement($id_to_delete)
    {
        if (!is_array($id_to_delete)) {
            $id_to_delete = [$id_to_delete];
        }
        $deleted = 0;
        foreach ($id_to_delete as $catalog_id) {
            if (isset($this->elements[$catalog_id])) {
                unset($this->elements[$catalog_id]);
                ++$deleted;
            }
        }
        if ($deleted === 1) {
            PageLayout::postSuccess(_('Es wurde ein Literaturverweis aus Ihrer Merkliste gelöscht'));
        } else if ($deleted) {
            PageLayout::postSuccess(sprintf(_('Es wurden %s Literaturverweise aus Ihrer Merkliste gelöscht.'), $deleted));
        }
        $this->setDefaultValue();
        return $deleted;
    }

    public function getNumElements()
    {
        return (is_array($this->elements)) ? count($this->elements) : 0;
    }

    public function isInClipboard($catalog_id)
    {
        return isset($this->elements[$catalog_id]);
    }
    
    public function getElements()
    {
        $returned_elements = null;
        if (is_array($this->elements)) {
            $this->dbv->params[0] = array_keys($this->elements);
            $this->elements       = null;
            $rs                   = $this->dbv->get_query("view:LIT_GET_CLIP_ELEMENTS");
            while ($rs->next_record()) {
                $returned_elements[$rs->f("catalog_id")] = $rs->f("short_name");
                $this->elements[$rs->f("catalog_id")]    = true;
            }
        }
        return $returned_elements;
    }
    
    public function &getFormObject()
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
        $form_name                   = $this->form_name;
        $form_fields['clip_content'] = ['type' => 'select', 'multiple' => true, 'options_callback' => [$this, "getClipOptions"]];
        $form_fields['clip_cmd']     = ['type' => 'select', 'options' => [['name' => _("Aus Merkliste löschen"), 'value' => 'del']]];
        $form_buttons['clip_ok']     = ['type' => 'accept', 'caption' => _('OK'), 'info' => _("Gewählte Aktion starten")];
        if (!is_object($this->form_obj)) {
            $this->form_obj = new StudipForm($form_fields, $form_buttons, $form_name, false);
        } else {
            $this->form_obj->form_fields = $form_fields;
        }
        return true;
    }
    
    public function getClipOptions($caller, $name)
    {
        $options = [];
        $cols    = 40;
        if ($elements = $this->getElements()) {
            foreach ($elements as $catalog_id => $title) {
                $options[] = ['name' => my_substr($title, 0, $cols), 'value' => $catalog_id];
            }
        } else {
            $options[] = ['name' => ("Ihre Merkliste ist leer!"), 'value' => 0];
            $options[] = ['name' => str_repeat("¯", floor($cols * .8)), 'value' => 0];
        }
        return $options;
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
                } else {
                    PageLayout::postInfo(_('Sie haben keinen Eintrag in Ihrer Merkliste ausgewählt!'));
                }
                break;
        }
    }
}
