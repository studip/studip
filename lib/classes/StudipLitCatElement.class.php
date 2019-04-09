<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitCatElement.class.php
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
*
*
*
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
**/
class StudipLitCatElement {

    var $fields = [];
    var $dbv;
    var $form_obj = null;
    var $form_name = "lit_element_form";
    var $init_form;
    var $classname = "StudipLitCatElement";
    var $persistent_slots = ["fields"];

    public function __construct($catalog_id = false, $with_form = false)
    {
        $this->dbv = DbView::getView('literatur');
        $this->init_form = $with_form;
        $this->initFields();
        if ($catalog_id){
            $this->getElementData($catalog_id);
        }
    }

    function initFields(){
        $this->fields = [
                        'dc_title'  =>  [  'caption'   => _("Titel"),
                                                'info'  => _("Titel der Quelle; der vom Verfasser, Urheber oder Verleger vergebene Namen der Ressource"),
                                                'len'   => 255,
                                                'type'  => 'text',
                                                'mandatory' => true],
                        'dc_creator'=>  [  'caption'   => _("Verfasser oder Urheber"),
                                                'info'  => _("Die Person(en) oder Organisation(en), die den intellektuellen Inhalt verantworten. Z.B. Autoren bei Textdokumenten; Künstler, Photographen bzw. auch andere Bezeichnungen wie Komponist und Maler bei graphischen Dokumenten."),
                                                'len'   => 255,
                                                'type'  => 'text',
                                                'mandatory' => true],
                        'dc_subject'=>  [  'caption'   => _("Thema und Stichwörter"),
                                                'info'  => _("Thema, Schlagwort, Stichwort. Das Thema der Ressource bzw. Stichwörter oder Phrasen, die das Thema oder den Inhalt beschreiben"),
                                                'len'   => 255,
                                                'type'  => 'text'],
                        'dc_description'=>['caption'   => _("Inhaltliche Beschreibung"),
                                                'info'  => _("Kurze Inhaltswiedergabe, Beschreibung, Abstract"),
                                                'len'   => 65535,
                                                'type'  => 'text'],
                        'dc_publisher'=>[  'caption'=> _("Verleger, Herausgeber"),
                                                'info'  => _("Die Einrichtung, die verantwortet, daß diese Ressource in dieser Form zur Verfügung steht, wie z.B. ein Verleger, ein Herausgeber, eine Universität oder eine korporatives Unternehmen."),
                                                'len'   => 255,
                                                'type'  => 'text'],
                        'dc_contributor'=>['caption'=> _("Weitere beteiligten Personen und Körperschaften"),
                                                'info'  => _("Sonstige an der Erstellung und Veröffentlichung der Ressource beteiligte Personen oder Organisationen"),
                                                'len'   => 255,
                                                'type'  => 'text'],
                       'dc_date' => [ 'caption'=> _("Datum"),
                                                'info'  => _("Das Datum, an dem die Ressource in der gegenwärtigen Form zugänglich gemacht wurde."),
                                                'len'   => 11,
                                                'type'  => 'datepicker',
                                                'mandatory' => true],
                        'dc_type'   =>  [  'caption'=> _("Ressourcenart"),
                                                'info'  => _("Die Art der Ressource, z.B. Homepage, Roman, Gedicht, Arbeitsbericht, technischer Bericht, Essay, Wörterbuch\n(Die Vorgaben entsprechen den EndNote Referenz Typen)"),
                                                'len'   => 255,
                                                'type'  => 'text',
                                                'select_list' => ['','Book','Manuscript','Magazine Article','Conference Proceedings','Report','Computer Program','Audiovisual Material','Artwork','Patent','Bill','Case','Journal Article','Book Section','Edited Book','Newspaper Article','Thesis','Personal Communication','Electronic Source','Film or Broadcast','Map','Hearing','Statute']],
                        'dc_format' =>  [  'caption'=> _("Format"),
                                                'info'  => _("Hier wird das datentechnische Format der Ressource eingetragen, z.B. Text/HTML, ASCII, Postscript-Datei, ausführbare Anwendung, JPEG-Bilddatei etc. Grundsätzlich können Formate auch physische Medieneinheiten wie Bücher, Zeitschriften oder andere nichtelektronische Medien mit einschließen."),
                                                'len'   => 255,
                                                'type'  => 'text',
                                                'select_list' => ['','text/html','application/pdf']],
                        'dc_identifier' =>['caption'=> _("Ressourcen-Identifikation"),
                                                'info'  => _("Weltweit eindeutige Kennzeichnung, z.B. URL oder ISBN"),
                                                'len'   => 255,
                                                'type'  => 'text'],
                        'dc_source' =>  [  'caption'=> _("Quelle"),
                                                'info'  => _("Bei nichtoriginären Beiträgen die Quelle bzw. das Original"),
                                                'len'   => 255,
                                                'type'  => 'text'],
                        'dc_language'=> [  'caption'=> _("Sprache"),
                                                'info'  => _("Hier wird die Sprache des intellektuellen Inhalts dieser Ressource vermerkt."),
                                                'len'   => 255,
                                                'type'  => 'text',
                                                'select_list'=> ['','ger','eng','fre','ita','spa']],
                        'dc_relation'=> [  'caption'=> _("Beziehung zu anderen Ressourcen"),
                                                'info'  => _("Die Angabe in diesem Feld ermöglicht es, Verbindungen unter verschiedenen Ressourcen darzustellen, die einen formalen Bezug zu anderen Ressourcen haben, aber als eigenständige Ressourcen existieren."),
                                                'len'   => 255,
                                                'type'  => 'text'],
                        'dc_coverage'=> [  'caption'=> _("Räumliche und zeitliche Maßangaben"),
                                                'info'  => _("Hier werden Angaben zur räumlichen Bestimmung (z.B. geographische Koordinaten) und zeitlichen Gültigkeit eingetragen, die die Ressource charakterisieren."),
                                                'len'   => 255,
                                                'type'  => 'text'],
                        'dc_rights' =>  [  'caption'=> _("Rechtliche Bedingungen"),
                                                'info'  => _("Verweis auf die Nutzungsbedingungen entsprechend dem Urheberrecht"),
                                                'len'   => 255,
                                                'type'  => 'text'],
                        'accession_number'=>['caption' => _("Zugriffsnummer"),
                                                'info'  => _("Die Zugriffsnummer eines Bibliothekssystems, über die diese Quelle identifiziert werden kann. z.B. PICA Prod Nummer oder die Signatur einer Bibliothek."),
                                                'len'   => 100,
                                                'type'  => 'text'],
                        'lit_plugin'=>  [  'caption'   => _("Verweis auf externes Bibliothekssystem"),
                                                'info'  => _("Der Name des externen Bibliothekssystems, in das über einen Weblink verzweigt werden kann."),
                                                'len'   => 100,
                                                'type'  => 'select',
                                                'options'=> StudipLitSearch::GetAvailablePluginsOptions()],
                        'catalog_id' => [  'type'  => 'text'],
                        'user_id'   =>  [  'type'  =>  'text'],
                        'mkdate'    =>  [  'type'  =>  'int'],
                        'chdate'    =>  [  'type'  =>  'int'],
                        ];
    }

    function getElementData($catalog_id = false){
        if (!$catalog_id){
            $catalog_id = $this->fields['catalog_id']['value'];
        }
        if ($catalog_id != 'new_entry'){
            $this->dbv->params[0] = $catalog_id;
            $rs = $this->dbv->get_query("view:LIT_GET_ELEMENT");
            if ($rs->next_record()){
                foreach ($this->fields as $field_name => $field_detail){
                    $this->fields[$field_name]['value'] = $rs->f($field_name);
                }
                $this->dbv->params[0] = $catalog_id;
                $rs = $this->dbv->get_query("view:LIT_GET_REFERENCE_COUNT");
                $rs->next_record();
                $this->reference_count = $rs->f("anzahl");
            } else {
                $catalog_id = "new_entry";
            }
        }
        if ($catalog_id == "new_entry"){
            $this->fields['catalog_id']['value'] = "new_entry";
            $this->fields['mkdate']['value'] = time();
            $this->fields['chdate']['value'] = time();
            $this->fields['user_id']['value'] = $GLOBALS['auth']->auth['uid'];
            $this->fields['lit_plugin']['value'] = 'Studip';
            $this->reference_count = 0;
        }
        if ($this->init_form){
            $this->setFormObject();
        }
        return ($catalog_id != "new_entry");
    }

    function &getFormObject(){
        if (!is_object($this->form_obj)){
            $this->setFormObject();
        }
        return $this->form_obj;

    }

    function setFormObject(){
        $form_fields = [];
        $form_name = $this->form_name;
        if($this->isNewEntry()){
            $this->fields['default_lit_list'] = ['caption' => _("Eintrag in diese Literaturliste"),
                                                'info'  => _("Wählen Sie hier eine persönliche Literaturliste aus, in die der neue Eintrag aufgenommen werden soll."),
                                                'len'   => 255,
                                                'type'  => 'select',
                                                'options'=> array_merge(['---'], (array)StudipLitList::GetListsByRange($GLOBALS['user']->id, 'form_options'))];
        }
        foreach ($this->fields as $field_name => $field_detail){

            if ($field_detail['caption']){
                if ($field_detail['select_list']){
                    $form_fields[$field_name . "_select"] = ['type' => 'select','options' => $field_detail['select_list']];
                    $form_fields[$field_name . "_text"] = ['type' => 'text'];
                    $form_fields[$field_name] = ['type' => 'combo', 'text' => $field_name . "_text", 'select' => $field_name . "_select", 'separator' => '&nbsp;'];
                } else {
                    $form_fields[$field_name]['type'] = $field_detail['type'];
                    if ($field_detail['type'] == 'text' && $field_detail['len'] > 100){
                        $form_fields[$field_name]['type'] = 'textarea';
                    }
                    if ($field_detail['type'] == 'select'){
                        $form_fields[$field_name]['options'] = $field_detail['options'];
                    }
                }
                $form_fields[$field_name]['caption'] = $field_detail['caption'];
                $form_fields[$field_name]['info'] = $field_detail['info'];
                $form_fields[$field_name]['default_value'] = $field_detail['value'];
            }
        }
        $form_fields['catalog_id'] = ['type' => 'hidden', 'default_value' => $this->fields['catalog_id']['value']];
        $form_buttons = ['send' => ['type' => 'accept', 'caption' => _('speichern'), 'info' => _("Änderungen speichern")],
                            'reset' => ['caption' => _('zurücksetzen'), 'info' => _("Änderungen zurücksetzen")],
                            'delete' => ['caption' => _('löschen'), 'info' => _("Eintrag löschen")]
                    ];
        if (!is_object($this->form_obj)){
            $this->form_obj = new StudipForm($form_fields, $form_buttons, $form_name);
        } else {
            $this->form_obj->form_fields = $form_fields;
        }
        if ($this->form_obj->getFormFieldValue("catalog_id") != $this->getValue("catalog_id")){
            $this->form_obj->doFormReset();
        }
        return true;
    }

    function setValuesFromForm(){
        $this->getFormObject();
        if (is_array($this->form_obj->form_values)){
            foreach($this->form_obj->form_values as $name => $value){
                if (isset($this->fields[$name]) && $this->fields[$name]['type'] != 'hidden'){
                    $this->fields[$name]['value'] = $value;
                }
            }
        }
    }

    function getValues(){
        $ret = [];
        foreach ($this->fields as $name => $value){
            $ret[$name] = $value['value'];
        }
        return $ret;
    }

    function setValues($fields){
        if (is_array($fields)){
            foreach ($fields as $name => $value){

                if (isset($this->fields[$name])) $this->fields[$name]['value'] = $value;
            }
            return true;
        } else {
            return false;
        }
    }

    function insertData(){
        if ($this->isNewEntry()){
            $this->fields['catalog_id']['value'] = md5(uniqid("litblablubb",1));
            $this->fields['chdate']['value'] = $this->fields['mkdate']['value'] = time();
            $default_list_entry = $this->fields['default_lit_list']['value'];
            unset($this->fields['default_lit_list']);
            foreach($this->fields as $name => $detail){
                $field_names[] = $name;
                $field_values[] = addslashes(trim($detail['value']));
            }
            $sql = "INSERT INTO lit_catalog(" . join(",", $field_names) . ") VALUES ('" . join("','", $field_values) . "')";
            PageLayout::postSuccess(_("Ein neuer Datensatz wurde eingefügt."));
        } else {
            $this->fields['chdate']['value'] = time();
            foreach($this->fields as $name => $detail){
                $field_upd[] = $name . "='" . addslashes(trim($detail['value'])) . "'";
            }
            $sql = "UPDATE lit_catalog SET " . join(",", $field_upd) . " WHERE catalog_id='" . $this->fields['catalog_id']['value'] . "'";
            PageLayout::postSuccess(_("Die geänderten Daten wurden gespeichert."));
        }
        $rs = $this->dbv->get_query($sql);
        if ($this->init_form){
            $this->form_obj->doFormReset();
        }
        $this->getElementData();
        if ($rs->affected_rows()){
            if($default_list_entry && $default_list_entry != '---'){
                $list = TreeAbstract::GetInstance("StudipLitList", $GLOBALS['user']->id);
                $list->insertElement(['catalog_id' => $this->getValue('catalog_id'), 'list_id' => $default_list_entry,
                                            'list_element_id' => $list->getNewListElementId(),
                                            'user_id' => $GLOBALS['user']->id,
                                            'note' => '', 'priority' => ($list->getMaxPriority($default_list_entry) + 1) ]);
            }
        }
        return $rs->affected_rows();
    }

    function deleteElement(){
        $this->dbv->params[0] = $this->getValue("catalog_id");
        $rs = $this->dbv->get_query("view:LIT_DEL_ELEMENT");
        if ($rs->affected_rows()){
            PageLayout::postSuccess(_("Der Datensatz wurde gelöscht."));
            $this->initFields();
            $this->getElementData("new_entry");
            return true;
        } else {
            PageLayout::postError(_("Der Datensatz konnte nicht gelöscht werden"));
            return false;
        }
    }

    function checkElement(){
        if ($this->getValue('user_id') == 'studip' && $this->getValue('accession_number')){
            $this->dbv->params[0] = $this->getValue('accession_number');
            $rs = $this->dbv->get_query("view:LIT_CHECK_ELEMENT");
            if ($rs->next_record()){
                return $rs->f('catalog_id');
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function checkValues(){
        $missing_fields = false;
        foreach($this->fields as $name => $detail){

            if ($detail['mandatory']){
                if ($detail['type'] == 'date' || $detail['type'] == 'datepicker'){
                    $this->setValue($name, $this->checkDate($detail['value']));
                }
                if (!$this->getValue($name)){
                    $missing_fields[] = $detail['caption'];
                }
            }
        }
        if (is_array($missing_fields)){
            PageLayout::postError(sprintf(_("Bitte füllen Sie folgende Felder aus: %s"), "\"" . join("\", \"",$missing_fields)));
        }
        return is_array($missing_fields) ? false : true;
    }

    function checkDate($date){
        $date =  explode('-', $date);
        $date[1] = ($date[1] < 1 || $date[1] > 12) ? 1 : $date[1];
        $date[2] = ($date[2] < 1 || $date[2] > 31) ? 1 : $date[2];
        if ($date[0] < 1 || $date[0] > 3000){
            return false;
        } else {
            return join('-' , $date);
        }
    }

    function getValue($name){
        if (isset($this->fields[$name])){
            return trim($this->fields[$name]['value']);
        } else {
            switch ($name){
                case "year":
                $year = explode("-", $this->getValue("dc_date"));
                $ret = $year[0];
                break;
                case "authors":
                if ( ($ret = $this->getValue("dc_contributor")) ){
                    $ret = "; " . $ret;
                }
                $ret = $this->getValue("dc_creator") . $ret;
                break;
                case "published":
                $ret = $this->getValue("dc_publisher") . ", " . $this->getValue("year");
                break;
                case "external_link":
                $plugin_name = $this->getValue("lit_plugin");
                $link = StudipLitSearch::GetExternalLink($plugin_name);
                if ($link){
                    $ret = preg_replace_callback('/({[a-z0-9_]+})/', function ($m) {
                        return $this->getValue(mb_substr($m[1], 1 ,mb_strlen($m[1]) - 2));
                    }, $link);
                    if ($ret == preg_replace('/({[a-z0-9_]+})/', "", $link)) {
                        $ret = "";
                    }
                }
                break;
                case "lit_plugin_display_name":
                $plugin_name = $this->getValue("lit_plugin");
                $ret = StudipLitSearch::GetPluginDisplayName($plugin_name);
                break;
                default:
                $ret = "unknown tag: $name";
            }
        return $ret;
        }
    }

    function setValue($name, $value){
        if (isset($this->fields[$name])){
            $this->fields[$name]['value'] = $value;
            return true;
        } else {
            return false;
        }
    }

    function isChangeable(){
        return ($GLOBALS['auth']->auth['uid'] == $this->getValue("user_id") || $GLOBALS['auth']->auth['perm'] == "root");
    }

    function isNewEntry(){
        return (!$this->fields['catalog_id']['value'] || $this->fields['catalog_id']['value'] == 'new_entry');
    }

    function getShortName(){
        $autor = preg_split ("/[\s,]+/", $this->getValue("dc_creator"),-1,PREG_SPLIT_NO_EMPTY);
        $autor = $autor[0];
        $year = explode("-", $this->getValue("dc_date"));
        $year = $year[0];
        return $autor . "(" . $year . ")-" . $this->getValue("dc_title");
    }

    function CloneElement($catalog_id){
        $clone = new StudipLitCatElement($catalog_id);
        $clone->getElementData('new_entry');
        $clone->insertData();
        return ($clone->getValue('catalog_id') == $catalog_id ? false : $clone->getValue('catalog_id'));
    }

    function &GetClonedElement($catalog_id){
        $clone = new StudipLitCatElement($catalog_id);
        $clone->getElementData('new_entry');
        return $clone;
    }
}
?>
