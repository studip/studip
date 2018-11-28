<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipSemSearchForm.class.php
// Class to build search formular and execute search
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
* Class to build search formular and execute search
*
*
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package  DBTools
**/
class StudipSemSearch {

    var $form;

    var $search_result;

    var $form_name;

    var $num_sem;

    var $sem_tree;

    var $range_tree;

    var $search_done = false;

    var $found_rows = false;

    var $search_button_clicked = false;

    var $new_search_button_clicked = false;

    var $sem_change_button_clicked = false;

    var $override_sem = false;

    var $attributes_default = array('style' => 'width:100%;');

    var $search_scopes = array();
    var $search_ranges = array();
    var $search_sem_class = 'all';

    var $visible_only = false;

    function __construct($form_name = "search_sem", $auto_search = true, $visible_only = false, $sem_class = 'all'){

        $search_fields = array('title' => array('type' => 'text'),
                                'sub_title' => array('type' => 'text'),
                                'number' => array('type' => 'text'),
                                'comment' => array('type' => 'text'),
                                'lecturer' => array('type' => 'text'),
                                'scope' => array('type' => 'text'),
                                'quick_search' => array('type' => 'text'),
                                'type' => array('type' => 'select', 'default_value' => 'all', 'max_length' => 35,'options_callback' => array($this, 'getSelectOptions')),
                                'sem' => array('type' => 'select', 'default_value' => 'all','options_callback' => array($this, 'getSelectOptions')),
                                'category' => array('type' => 'select', 'default_value' => 'all', 'max_length' => 50,'options_callback' => array($this, 'getSelectOptions')),
                                'combination' => array('type' => 'select', 'default_value' => 'AND','options_callback' => array($this, 'getSelectOptions')),
                                'scope_choose' => array('type' => 'select', 'default_value' => 'root', 'max_length' => 45,'options_callback' => array($this, 'getSelectOptions')),
                                'range_choose' => array('type' => 'select', 'default_value' => 'root', 'max_length' => 45,'options_callback' => array($this, 'getSelectOptions')),
                                'qs_choose' => array('type' => 'select',
                                                    'default_value' => 'title_lecturer_number',
                                                    'options_callback' => array($this, 'getSelectOptions')
                                                    )
                                );
        $search_buttons = array('do_search' => array('caption' => _("Suchen"), 'info' => _("Suche starten")),
                                'sem_change' => array('caption' => _('Auswählen'), 'info' => _("anderes Semester auswählen")),
                                'new_search' => array('caption' => _('Neue Suche'), 'info' =>_("Neue Suche starten")));
        //workaround: Qicksearch ändert den Namen des Eingabefeldes
        if (Request::get("search_sem_quick_search_parameter")) {
            Request::set('search_sem_quick_search', Request::get("search_sem_quick_search_parameter"));
        }
        $this->form = new StudipForm($search_fields, $search_buttons, $form_name , false);
        $this->form_name = $form_name;
        $this->sem_dates = SemesterData::GetSemesterArray();
        $this->visible_only = $visible_only;
        $this->search_sem_class = $sem_class;

        if($this->form->isClicked('do_search') || ($this->form->isSended() && (!$this->form->isClicked('sem_change') || mb_strlen($this->form->getFormFieldValue('quick_search')) > 2))){
            $this->search_button_clicked = true;
            if ($auto_search){
                $this->doSearch();
                $this->search_done = true;
            }
        }

        $this->new_search_button_clicked = $this->form->isClicked('new_search');
        $this->sem_change_button_clicked = $this->form->isClicked('do_search');

    }

    function getSearchField($name,$attributes = false,$default = false){
        if (!$attributes){
            $attributes = $this->attributes_default;
        }
        return $this->form->getFormField($name,$attributes,$default);
    }

    function getSelectOptions($caller, $name){
        $options = array();
        if ($name == "combination"){
            $options = array(array('name' =>_("UND"),'value' => 'AND'),array('name' => _("ODER"), 'value' => 'OR'));
        } elseif ($name == "sem"){
            $options = array(array('name' =>_("alle"),'value' => 'all'));
            for ($i = count($this->sem_dates) -1 ; $i >= 0; --$i){
                $options[] = array('name' => $this->sem_dates[$i]['name'], 'value' => $i);
            }
        } elseif ($name == "type"){
            $options = array(array('name' =>_("alle"),'value' => 'all'));
            foreach($GLOBALS['SEM_TYPE'] as $type_key => $type_value){
                if($this->search_sem_class == 'all' || $type_value['class'] == $this->search_sem_class){
                    $options[] = array('name' => $type_value['name'] . " (". $GLOBALS['SEM_CLASS'][$type_value['class']]['name'] .")",
                                        'value' => $type_key);
                }
            }
        } elseif ($name == "category"){
            $options = array(array('name' =>_("alle"),'value' => 'all'));
            foreach($GLOBALS['SEM_CLASS'] as $class_key => $class_value){
                $options[] = array('name' => $class_value['name'],
                                        'value' => $class_key);
                }
        } elseif ($name == "scope_choose"){
            if(!is_object($this->sem_tree)){
                $this->sem_tree = TreeAbstract::GetInstance("StudipSemTree", false);
            }
            $options = array(array('name' => $this->sem_tree->root_name, 'value' => 'root'));
            for($i = 0; $i < count($this->search_scopes); ++$i){
                $options[] = array('name' => $this->sem_tree->tree_data[$this->search_scopes[$i]]['name'], 'value' => $this->search_scopes[$i]);
            }
        } elseif ($name == "range_choose"){
            if(!is_object($this->range_tree)){
                $this->range_tree = TreeAbstract::GetInstance("StudipRangeTree", false);
            }
            $options = array(array('name' => $this->range_tree->root_name, 'value' => 'root'));
            for($i = 0; $i < count($this->search_ranges); ++$i){
                $options[] = array('name' => $this->range_tree->tree_data[$this->search_ranges[$i]]['name'], 'value' => $this->search_ranges[$i]);
            }
        } elseif ($name == "qs_choose"){
            foreach(StudipSemSearchHelper::GetQuickSearchFields() as $key => $value){
                $options[] = array('name' => $value, 'value' => $key);
            }
        }
        return $options;
    }

    function getFormStart($action = "", $attributes = [])
    {
        return $this->form->getFormStart($action, $attributes);
    }

    function getFormEnd(){
        if ($this->search_sem_class != 'all'){
            $ret = $this->form->getHiddenField('category',$this->search_sem_class);
        }
        return $ret . $this->form->getFormEnd();
    }

    function getHiddenField($name, $value = false){
        return  $this->form->getHiddenField($name, $value);
    }

    function getSearchButton($attributes = false, $tooltip = false){
        return $this->form->getFormButton('do_search', $attributes);
    }
    function getNewSearchButton($attributes = false, $tooltip = false){
        return $this->form->getFormButton('new_search', $attributes);
    }
    function getSemChangeButton($attributes = false, $tooltip = false){
        return $this->form->getFormButton('sem_change', $attributes);
    }

    function doSearch(){
        $search_helper = new StudipSemSearchHelper($this->form, $this->visible_only);
        $this->found_rows = $search_helper->doSearch();
        $this->search_result = $search_helper->getSearchResultAsSnapshot();
        return $this->found_rows;
    }
}
?>
