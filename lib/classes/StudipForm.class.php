<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipForm.class.php
// Class to build HTML formular and handle persistence using PhpLib
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

use Studip\Button, Studip\LinkButton;

/**
* Class to build Studip HTML forms
*
* @deprecated
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
**/
class StudipForm {

    var $form_name;

    var $field_attributes_default = [];

    var $form_fields = [];

    var $form_buttons = [];

    var $persistent_values = true;

    var $form_values = [];

    var $value_changed = [];


    static function TimestampToSQLDate($tstamp){
        return date("Y-m-d", $tstamp);
    }

    static function SQLDateToTimestamp($sqldate){
        $date_values = explode("-", $sqldate); //YYYY-MM-DD
        if (checkdate((int)$date_values[1],(int)$date_values[2],(int)$date_values[0])){
            return mktime(12,0,0,$date_values[1],$date_values[2],$date_values[0]);
        } else {
            return false;
        }
    }

    static function _GetRawFieldValue($field_name, $form_name) {
        return Request::get($form_name. '_' . $field_name);
    }

    static function _IsSended($form_name){
        return Request::get($form_name . "_" . md5("is_sended")) !== null;
    }

    static function _IsClicked($button, $form_name){
        return Request::submitted($form_name . "_" . $button);
    }

    function __construct($form_fields, $form_buttons, $form_name = "studipform", $persistent_values = true) {
        $this->form_name = $form_name;
        $this->persistent_values = $persistent_values;
        $this->form_fields = $form_fields;
        $this->form_buttons = $form_buttons;
        if ($this->persistent_values){
            $this->form_values =& $_SESSION["_p_values"]["_" . $this->form_name . "_values"];
        }
        if ($this->isSended()){
            foreach ($this->form_fields as $name => $foo){
                if (!$foo['disabled']){
                    if ( ($field_value = Request::get($this->form_name . "_" . $name)) !== null) {
                            $new_form_values[$name] = trim($field_value);
                    } elseif ( is_array($field_value = Request::getArray($this->form_name . "_" . $name))) {
                        foreach ($field_value as $key => $value){
                            $new_form_values[$name][$key] = trim($value);
                        }
                    } else {
                        $new_form_values[$name] = null;
                    }
                }
            }
            foreach ($this->form_fields as $name => $value){
                if (!$value['disabled']){
                    if ($value['type'] == 'combo'){
                        if ($this->form_values[$name] != $new_form_values[$value['text']]){ //textfeld wurde verändert
                            $new_form_values[$name] = $new_form_values[$value['text']];
                        } else if ($this->form_values[$name] != $new_form_values[$value['select']] && !$new_form_values[$value['text']]){ //textfeld nicht geändert, select geändert
                            $new_form_values[$name] = $new_form_values[$value['select']];
                        } else {
                            $new_form_values[$name] = $this->form_values[$name];
                        }
                    }
                    if ($value['type'] == 'date'){
                        $new_form_values[$name] = Request::int($this->form_name . "_" . $name . "_year") . "-"
                                                . sprintf('%02s', Request::int($this->form_name . "_" . $name . "_month")) . "-"
                                                . sprintf('%02s', Request::int($this->form_name . "_" . $name . "_day"));
                    }
                    if($value['type'] == 'datepicker'){
                        $date = explode('.',Request::get($this->form_name . "_" . $name));
                        $new_form_values[$name] = $date[2] . "-"
                                                . sprintf('%02s', $date[1]) . "-"
                                                . sprintf('%02s', $date[0]);
                    }
                    if ($value['type'] == 'time'){
                        $new_form_values[$name] = sprintf('%02s', Request::int($this->form_name . "_" . $name . "_hours")) . ":"
                                                . sprintf('%02s', Request::int($this->form_name . "_" . $name . "_minutes"));
                    }
                    if ($value['type'] == 'checkbox'){
                        $new_form_values[$name] = Request::int($this->form_name . "_" . $name, 0);
                    }
                    if ( (isset($this->form_values[$name]) && $this->form_values[$name] != $new_form_values[$name])
                        || (!isset($this->form_values[$name]) && $new_form_values[$name] != $this->form_fields[$name]['default_value']) ){
                        $this->value_changed[$name] = true;
                    }
                }
            }
            $this->form_values = array_merge((array)$this->form_values, (array)$new_form_values);
        }
    }

    function getDefaultValues(){
        foreach ($this->form_fields as $name => $value){
            $this->form_values[$name] = $value['default_value'];
        }
    }

    function checkDefaultValues(){
        if (is_array($this->form_values)){
            foreach ($this->form_fields as $name => $value){
                if (!$value['ignore_check']){
                    if((is_null($this->form_values[$name]) ? 0 : $this->form_values[$name])
                        != (is_null($value['default_value']) ? 0 : $value['default_value']) ){
                        return true;
                    }
                }
            }
        }
        return false;
    }

    function getFormField($name, $attributes = false, $default = false, $subtype = false){
        if (!$attributes){
            $attributes = $this->field_attributes_default;
        }
        if (!$default){
            if (isset($this->form_values[$name])){
                $default = $this->form_values[$name];
            } else {
                $default = $this->form_fields[$name]['default_value'];
            }
        }
        if (is_array($this->form_fields[$name]['attributes'])){
            $attributes = array_merge((array)$attributes, (array)$this->form_fields[$name]['attributes']);
        }

        if ($this->form_fields[$name]['disabled']){
            $attributes['disabled'] = 'disabled';
        }

        if ($this->form_fields[$name]['required']){
            $attributes['required'] = 'required';
        }

        if (!isset($attributes['id'])) {
            $attributes['id'] = $this->form_name . '_' . $name;
        }

        if($this->form_fields[$name]['type']){
            $method = "getFormField" . $this->form_fields[$name]['type'];
            return $this->$method($name,$attributes,$default,$subtype);
        }
    }

    function getFormFieldNoForm($name, $attributes, $default){
        $ret = "\n<span ";
        $ret .= $this->getAttributes($attributes);
        $ret .= ">";
        if(is_array($default)) $default = join('; ', $default);
        $ret .= htmlReady($default,1,1);
        $ret .= "</span>";
        if (!$attributes['disabled']) $ret .= $this->getHiddenField($name, $default);
        return $ret;
    }

    function getFormFieldText($name, $attributes, $default){
        $ret = "\n<input type=\"text\" name=\"{$this->form_name}_{$name}\" " . (($default) ? "value=\"".htmlReady($default)."\" " : "");
        $ret .= $this->getAttributes($attributes);
        $ret .= ">";
        return $ret;
    }

    function getFormFieldCheckbox($name, $attributes, $default){
        $ret = "\n<input type=\"checkbox\" name=\"{$this->form_name}_{$name}\" value=\"1\"" . (($default) ? " checked " : "");
        $ret .= $this->getAttributes($attributes);
        $ret .= ">";
        return $ret;
    }

    function getFormFieldRadio($name, $attributes, $default, $subtype){
        if (is_array($this->form_fields[$name]['options'])){
            $options = $this->form_fields[$name]['options'];
        } else if ($this->form_fields[$name]['options_callback']){
            $options = call_user_func($this->form_fields[$name]['options_callback'],$this,$name);
        }
        if($subtype !== false){
            return $this->getOneRadio($name, $attributes, ($default == $options[$subtype]['value']), $subtype);
        } else {
            $ret = '<div id="' . $attributes['id'] .'"><section class="hgroup">';
            for ($i = 0; $i < count($options); ++$i){
                $ret .= $this->getOneRadio($name, $attributes, ($default == $options[$i]['value']), $i);
                $ret .= "\n" . $this->form_fields[$name]['separator'];
            }
            $ret .= '</section></div>';
        }
        return $ret;
    }

    function getOneRadio($name, $attributes, $default, $subtype){
        $attributes['id'] = $this->form_name . '_' . $name . '_' . $subtype;
        $ret = "\n<input type=\"radio\" name=\"{$this->form_name}_{$name}\" value=\"{$this->form_fields[$name]['options'][$subtype]['value']}\"" . (($default) ? " checked " : "");
        $ret .= $this->getAttributes($attributes);
        $ret .= ">";
        $attributes['for'] = $attributes['id'];
        unset($attributes['id']);
        $ret .= $this->getFormFieldCaption($this->form_fields[$name]['options'][$subtype]['name'], $attributes);
        return $ret;
    }

    function getFormFieldTextarea($name, $attributes, $default){
        $ret = "\n<textarea wrap=\"virtual\"  name=\"{$this->form_name}_{$name}\" ";
        $ret .= $this->getAttributes($attributes);
        $ret .= ">";
        $ret .= htmlReady($default);
        $ret .= "</textarea>";
        return $ret;
    }

    function getFormFieldDatepicker($name, $attributes, $default)
    {
        $date_values = explode("-", $default); //YYYY-MM-DD
        $value = '';
        if(count($date_values)==3){
            $value = $date_values[2]. '.' . $date_values[1]. '.' .$date_values[0];
        }
        $ret .= $this->getFormFieldText($name, array_merge(['size'=>11,'maxlength'=>11], (array)$attributes), $value);
        $ret .='<script>jQuery("#'.$this->form_name.'_'.$name.'").datepicker();</script>';
        return $ret;
    }

    function getFormFieldTime($name, $attributes, $default) {
        $date_values = explode(":", $default); //hh:mm
        $ret = '<div id="' . $attributes['id'] .'"><section class="hgroup">';
        unset($attributes['id']);
        $ret .= $this->getFormFieldText($name . "_hours", array_merge(['size'=>2,'maxlength'=>2], (array)$attributes), $date_values[0]);
        $ret .= "\n" . $this->form_fields[$name]['separator'];
        $ret .= $this->getFormFieldText($name . "_minutes", array_merge(['size'=>2,'maxlength'=>2], (array)$attributes), $date_values[1]);
        $ret .= '</section></div>';
        return $ret;
    }

    function getFormFieldSelect($name, $attributes, $default){
        $ret = "\n<select name=\"{$this->form_name}_{$name}";
        if ($this->form_fields[$name]['multiple']){
            $ret .= "[]\" multiple ";
        } else {
            $ret .= "\" ";
        }
        $ret .= $this->getAttributes($attributes);
        $ret .= ">";
        if ($default === false){
            $default = $this->form_fields[$name]['default_value'];
        }
        if (is_array($this->form_fields[$name]['options'])){
            $options = $this->form_fields[$name]['options'];
        } else if ($this->form_fields[$name]['options_callback']){
            $options = call_user_func($this->form_fields[$name]['options_callback'],$this,$name);
        }
        for ($i = 0; $i < count($options); ++$i){
            $options_name = (is_array($options[$i])) ? $options[$i]['name'] : $options[$i];
            $options_value = (is_array($options[$i])) ? $options[$i]['value'] : $options[$i];
            $options_attributes = (is_array($options[$i])) ? $options[$i]['attributes'] : [];
            $selected = false;
            if ((is_array($default) && in_array("" . $options_value, $default))
            || (!is_array($default) && ($default == "" . $options_value))){
                $selected = true;
            }
            if ($this->form_fields[$name]['max_length']){
                $options_name = my_substr($options_name,0, $this->form_fields[$name]['max_length']);
            }
            $ret .= "\n<option value=\"".htmlReady($options_value)."\" " . (($selected) ? " selected " : "");
            $ret .= $this->getAttributes($options_attributes);
            $ret .= ">".htmlReady($options_name)."</option>";
        }
        $ret .= "\n</select>";
        return $ret;
    }

    function getFormFieldSelectBox($name, $attributes, $default){
        $box_attributes = $this->form_fields[$name]['box_attributes'] ? $this->form_fields[$name]['box_attributes'] : [];
        $ret = "\n<div class=\"selectbox\" ".$this->getAttributes($box_attributes)." >";
        $ret .= "\n<fieldset id=\"{$attributes['id']}\">";
        unset($attributes['id']);
        if ($this->form_fields[$name]['multiple']) {
            $element = 'checkbox';
            $element_name = $this->form_name . '_' . $name . '[]';
        } else {
            $element = 'radio';
            $element_name = $this->form_name . '_' . $name;
        }
        if ($default === false){
            $default = $this->form_fields[$name]['default_value'];
        }
        if (is_array($this->form_fields[$name]['options'])){
            $options = $this->form_fields[$name]['options'];
        } else if ($this->form_fields[$name]['options_callback']){
            $options = call_user_func($this->form_fields[$name]['options_callback'],$this,$name);
        }
        for ($i = 0; $i < count($options); ++$i) {
            $options_name = (is_array($options[$i])) ? $options[$i]['name'] : $options[$i];
            $options_value = (is_array($options[$i])) ? $options[$i]['value'] : $options[$i];
            $options_attributes = (is_array($options[$i])) ? $options[$i]['attributes'] : [];
            $selected = false;
            if ((is_array($default) && in_array("" . $options_value, $default))
            || (!is_array($default) && ($default == "" . $options_value))){
                $selected = true;
            }
            if ($this->form_fields[$name]['max_length']){
                $options_name = my_substr($options_name,0, $this->form_fields[$name]['max_length']);
            }
            $id = $this->form_name . '_' . $name . '_' . $i;
            $ret .= "\n<div ";
            $ret .= $this->getAttributes($attributes);
            $ret .= ">";
            $ret .= "\n<label for=\"$id\" ";
            $ret .= $this->getAttributes($options_attributes);
            $ret .= "><input style=\"vertical-align:middle;\" id=\"$id\" type=\"$element\" name=\"$element_name\" value=\"".htmlReady($options_value)."\" " . (($selected) ? " checked " : "");
            $ret .= ">&nbsp;";
            $ret .= htmlReady($options_name) . "</label>";
            $ret .= "\n</div>";
        }
        $ret .= "\n</fieldset>\n</div>";
        return $ret;
    }

    function getFormFieldCombo($name, $attributes, $default , $subtype = false){
        $ret = '<div id="' . $attributes['id'] .'"><section class="hgroup">';
        unset($attributes['id']);
        $combo_text_name = $this->form_fields[$name]['text'];
        $combo_select_name = $this->form_fields[$name]['select'];
        $select_attributes = ['onChange' => "document.{$this->form_name}.{$this->form_name}_{$combo_text_name}.value="
        ."document.{$this->form_name}.{$this->form_name}_{$combo_select_name}.options[document.{$this->form_name}.{$this->form_name}_{$combo_select_name}.selectedIndex].text; "];
        if (is_array($attributes)){
            $select_attributes = array_merge((array)$select_attributes, (array)$attributes);
        }
        if (!$subtype){
            $ret .= "\n" . $this->getFormFieldSelect($combo_select_name, $select_attributes, $default);
            $ret .= "\n" . $this->form_fields[$name]['separator'];
            $ret .= $this->getFormFieldText($combo_text_name, $attributes, $default);
        } else if ($subtype == "text"){
            $ret .= "\n" . $this->getFormFieldText($combo_text_name, $attributes, $default);
        } else {
                $ret .= $this->getFormFieldSelect($combo_select_name, $select_attributes, $default);
        }
        $ret .= "</section></div>";
        return $ret;
    }

    function getFormButton($name, $attributes = []){
        if (is_array($this->form_buttons[$name]['attributes'])) {
            $attributes = array_merge((array)$attributes, (array)$this->form_buttons[$name]['attributes']);
        }
        if (!$this->form_buttons[$name]['is_picture']) {
            if (isset($this->form_buttons[$name]['info']) && !isset($attributes['title'])) {
                $attributes['title'] = $this->form_buttons[$name]['info'];
            }
            $caption = $this->form_buttons[$name]['caption'] ? $this->form_buttons[$name]['caption'] : $this->form_buttons[$name]['type'];
            if (in_array($this->form_buttons[$name]['type'], words('cancel accept'))) {
                $create = 'create' . $this->form_buttons[$name]['type'];
            } else {
                $create = 'create';
            }
            $ret = Button::$create($caption, $this->form_name . "_" . $name, $attributes);
        } else {
            // Yes, this is kinda ugly
            $ret = Assets::input($this->form_buttons[$name]['type'],
                                 tooltip2($this->form_buttons[$name]['info'])
                                 + (array)$attributes
                                 + ['name' => $this->form_name . '_' . $name]);
        }
        return $ret;
    }

    function getFormFieldCaption($name, $attributes = false){
        $_name = $name;
        if (!isset($attributes['for'])) {
            $attributes['for'] = $this->form_name . '_' . $name;
        }
        if (isset($this->form_fields[$name]['caption'])) {
            $name = $this->form_fields[$name]['caption'];
        }
        $res = '<label ' . $this->getAttributes($attributes) . '>'.htmlReady($name);

        if(!empty($this->form_fields[$_name]['info']) && $attributes['info']) {
            $res .= $this->getFormFieldInfo($_name);
        }

        $res .= '</label>';
        return $res;
    }

    function getFormFieldInfo($name){
        return tooltipIcon($this->form_fields[$name]['info']);
    }

    function getFormStart($action = false, $attributes = false){
        if (!$action){
            $action = URLHelper::getLink();
        }
        $ret = "\n<form action=\"$action\" method=\"post\" name=\"{$this->form_name}\" " . $this->getAttributes($attributes) . ">";
        $ret .= CSRFProtection::tokenTag();
        return $ret;
    }

    function getFormEnd(){
        $ret = "";
        foreach ($this->form_fields as $field_name => $field_content){
            if ($field_content['type'] == 'hidden'){
                $ret .= $this->getHiddenField($field_name);
            }
        }
        $ret .= $this->getHiddenField(md5("is_sended"),1);
        return $ret . "\n</form>";
    }

    function getFormFieldValue($name){
        if (isset($this->form_values[$name])){
            $value = $this->form_values[$name];
        } else {
            $value = $this->form_fields[$name]['default_value'];
        }
        return $value;
    }

    function getFormFieldsByName($only_editable = false){
        $ret = [];
        foreach ($this->form_fields as $name => $detail){
            if( !($only_editable && ($detail['type'] == 'noform' || $detail['disabled'])) ){
                $ret[] = $name;
            }
        }
        return $ret;
    }

    function getHiddenField($name, $value = false){
        if (!$value){
            $value = $this->getFormFieldValue($name);
        }
        return "\n<input type=\"hidden\" name=\"{$this->form_name}_{$name}\" value=\"".htmlReady($value)."\">";
    }

    function doFormReset(){
        $this->form_values = null;
        return true;
    }

    function isChanged($name){
        return isset($this->value_changed[$name]);
    }

    function getRawFieldValue($field_name) {
        return self::_GetRawFieldValue($field_name, $this->form_name);
    }

    function isSended() {
        return self::_IsSended($this->form_name);
    }

    function isClicked($button) {
        return self::_IsClicked($button, $this->form_name);
    }

    function getClickedKillButton(){
        foreach($this->form_buttons as $name => $value){
            if ($value['is_kill_button']){
                if ($this->isClicked($name)){
                    return $name;
                }
            }
        }
        return false;
    }

    function getAttributes($attributes){
        $ret = "";
        if (is_array($attributes)) {
            foreach($attributes as $key => $value){
                $ret .= " ".$key."=\"".htmlReady($value)."\"";
            }
        }
        return $ret;
    }

    function getFormFieldRequired($name){
        if ($this->form_fields[$name]['required'])
            return "\n" . '<span style="color: red; font-weigth: bold">*</span>';
        else return "";
    }


}

// test & demo
/*
function getSomeOptions(&$caller, $name){
    $options[] = md5($name);
    foreach($caller->form_fields as $key => $value){
        $options[]=$key;
    }
    return $options;
}

page_open(array("sess" => "Seminar_Session"));
$_language = DEFAULT_LANGUAGE;
$_language_path = $INSTALLED_LANGUAGES[$_language]["path"];

$form_fields = array('text1'        =>  array('type' => 'text', 'caption' => 'Testtextfeld1', 'info' => 'Hier Schwachsinn eingeben'),
                    'text2'         =>  array('type' => 'textarea','caption' => 'Testtextfeld2', 'info' => 'Hier Schwachsinn eingeben','default' => 'blablubb'),
                    'select1'       =>  array('type' => 'select', 'options' => array(   array('name' =>_("UND"),'value' => 'AND'),
                                                                                        array('name' =>_("ODER"),'value' => 'OR'))),
                    'select2'       =>  array('type' => 'select','options_callback' => 'getSomeOptions'),
                    'combo1_text'   =>  array('type' => 'text'),
                    'combo1_select' =>  array('type' => 'select', 'options' => array("",_("Eins"),_("Zwei"), _("Drei"))),
                    'combo1'        =>  array('type' => 'combo', 'text' => 'combo1_text', 'select' => 'combo1_select', 'separator' => '--'),
                    'date1'         =>  array('type' => 'date',  'separator' => '.', 'default' => 'YYYY-MM-DD'),
                    'checkbox'      => array('type' => 'checkbox', 'caption' => 'Tolle Checkbox ?', value => '1'),
                    'radio_group'   => array('type' => 'radio', 'separator' => "&nbsp;", 'options' => array(    array('name' =>_("UND"),'value' => 'AND'),
                                                                                        array('name' =>_("ODER"),'value' => 'OR'),
                                                                                        array('name' =>_("NICHT"),'value' => 'NOT')))
                    );

$form_buttons = array('send' => array('type' => 'abschicken', 'info' => _("Dieses Formular abschicken")),
                    'not_send' => array('type' => 'abbrechen', 'info' => _("Eingabe abbrechen")));

$test = new StudipForm($form_fields, $form_buttons);
echo "<table width='400'><tr><td>";
echo $test->getFormStart();
echo $test->getFormFieldCaption("text1");
echo "&nbsp;" . $test->getFormFieldInfo("text1") . "&nbsp;";
echo $test->getFormField("text1");
echo $test->getFormField("text2");
echo $test->getFormFieldCaption("select1");
echo "&nbsp;" . $test->getFormFieldInfo("select1") . "&nbsp;";
echo $test->getFormField("select1");
echo $test->getFormFieldCaption("select2");
echo "&nbsp;" . $test->getFormFieldInfo("select2") . "&nbsp;";
echo $test->getFormField("select2");
echo $test->getFormField("date1", array('style' => 'vertical-align:middle'));
echo "<br>" . $test->getFormField("combo1",array('style' => 'vertical-align:middle'));
echo $test->getFormFieldCaption("checkbox", array('style' => 'vertical-align:middle'));
echo "&nbsp;" . $test->getFormField("checkbox",array('style' => 'vertical-align:middle'));
echo "<br>" . $test->getFormField("radio_group",array('style' => 'vertical-align:middle;font-size:10pt;'));
echo $test->getFormButton("send",array('style' => 'vertical-align:middle;'));
echo $test->getFormEnd();
echo "</td></tr></table>";
echo "<pre>";
page_close();
*/
?>
