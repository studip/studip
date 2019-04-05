<?php
# Lifter001: TEST
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitList.class.php
//
//
// Copyright (c) 2003 André Noack <noack@data-quest.de>
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

/**
* class to handle the
*
* This class provides
*
* @access   public
* @author   André Noack <noack@data-quest.de>
* @package
*/
class StudipLitList extends TreeAbstract implements PrivacyObject
{

    var $format_default = "**{authors}** - {dc_title} - %%{published}%%";
    var $cat_element;
    var $range_id;
    var $range_type;

    /**
    * constructor
    *
    * do not use directly, call TreeAbstract::GetInstance("StudipLitList", $range_id)
    * @access private
    */
    function __construct($range_id) {
        DbView::addView('literatur');

        if ($GLOBALS['LIT_LIST_FORMAT_TEMPLATE']){
            $this->format_default = $GLOBALS['LIT_LIST_FORMAT_TEMPLATE'];
        }
        $this->range_id = $range_id;
        $this->range_type = get_object_type($range_id);
        if ($this->range_type == "user"){
            $this->root_name = get_fullname($range_id);
        } else {
            $object_name = get_object_name($range_id, $this->range_type);
            $this->root_name = $object_name['type'] . ": " . $object_name['name'];
        }
        $this->cat_element = new StudipLitCatElement();
        parent::__construct(); //calling the baseclass constructor
    }

    /**
    * initializes the tree
    *
    * stores all rows from table range_tree in array $tree_data
    * @access public
    */
    function init(){
        parent::init();
        $this->view->params[0] = $this->range_id;
        $rs = $this->view->get_query("view:LIT_GET_LIST_BY_RANGE");
        while ($rs->next_record()){
            $list_ids[] =  $rs->f("list_id");
            $this->tree_data[$rs->f("list_id")] = array("user_id" => $rs->f("user_id"),
                                                    "format" => ($rs->f("format")) ? $rs->f("format") : $this->format_default,
                                                    "chdate" => $rs->f("chdate"),
                                                    "fullname" => $rs->f("fullname"),
                                                    "username" => $rs->f("username"),
                                                    "visibility" => $rs->f("visibility"),
                                                    );
            $this->storeItem($rs->f("list_id"), "root", $rs->f("name"), $rs->f("priority"));
        }
        if (is_array($list_ids)){
            $this->view->params[0] = $list_ids;
            $rs = $this->view->get_query("view:LIT_GET_LIST_CONTENT");
            while ($rs->next_record()){
                $this->tree_data[$rs->f("list_element_id")] = array("user_id" => $rs->f("user_id"),
                                                    "note" => $rs->f("note"),
                                                    "chdate" => $rs->f("chdate"),
                                                    "catalog_id" => $rs->f("catalog_id"),
                                                    "username" => $rs->f("username"),
                                                    "fullname" => $rs->f("fullname")
                                                    );
                $this->storeItem($rs->f("list_element_id"), $rs->f("list_id"), $rs->f("short_name"), $rs->f("priority"));
            }
        }


    }

    function isElement($id){
        return isset($this->tree_data[$id]['catalog_id']);
    }

    function getListIds(){
        return $this->getKids("root");
    }

    function getVisibleListIds(){
        $ret = false;
        $lists = $this->getKids('root') ?: [];
        for ($i = 0; $i < count($lists); ++$i){
            if ($this->tree_data[$lists[$i]]['visibility']){
                $ret[] = $lists[$i];
            }
        }
        return $ret;
    }

    function getListEntries($list_id){
        return $this->getKids($list_id);
    }

    function getNewListId(){
        return md5(uniqid("listbla",1));
    }

    function getNewListElementId(){
        return  md5(uniqid("elementbla",1));
    }

    function getFormattedEntry($item_id, $fields = null){
        if ($this->isElement($item_id)){
            $format = $this->tree_data[$this->tree_data[$item_id]['parent_id']]['format'];
            if (is_array($fields)){
                $this->cat_element->setValues($fields);
            } else {
                $this->cat_element->getElementData($this->tree_data[$item_id]['catalog_id']);
            }
            $this->cat_element->fields['note']['value'] = $this->tree_data[$item_id]['note'];
            $content = preg_replace_callback('/({[a-z0-9_]+})/', function ($m) {
                return $this->cat_element->getValue(mb_substr($m[1],1,mb_strlen($m[1])-2))
                    ? $this->cat_element->getValue(mb_substr($m[1],1,mb_strlen($m[1])-2))
                    : '???';
            }, $format);
            $content = preg_replace('/\|.?[^|]*\?\?\?.*?\|/', "", $content);
            $content = str_replace('|','', $content);
            return $content;
        } else {
            return false;
        }
    }

    function copyList($list_id){
        $this->view->params[] = $list_id;
        $rs = $this->view->get_query("view:LIT_GET_LIST");
        if ($rs->next_record()){
            $new_list_values['list_id'] = $this->getNewListId();
            $new_list_values['range_id'] = $this->range_id;
            $new_list_values['name'] = addslashes(_("Kopie von: ") . $rs->f("name"));
            $new_list_values['user_id'] = $rs->f("user_id");
            $new_list_values['format'] = addslashes($rs->f("format"));
            $new_list_values['priority'] = $this->getMaxPriority("root") + 1;
            if ($this->insertList($new_list_values)){
                $this->view->params[] = $this->getNewListElementId();
                $this->view->params[] = $new_list_values['list_id'];
                $this->view->params[] = $list_id;
                $rs = $this->view->get_query("view:LIT_INS_LIST_CONTENT_COPY");
                return $new_list_values['list_id'];
            }
        }
        return false;
    }

    function insertElementBulk($catalog_ids, $list_id){
        if (!is_array($catalog_ids)){
            $catalog_ids[] = $catalog_ids;
        }
        $inserted = 0;
        $priority = $this->getMaxPriority($list_id);
        foreach ($catalog_ids as $cat_id){
            if ($cat_id){
                $inserted += $this->insertElement(array('catalog_id' => $cat_id, 'list_id' => $list_id,
                                                        'list_element_id' => $this->getNewListElementId(),
                                                        'user_id' => $GLOBALS['auth']->auth['uid'],
                                                        'note' => '', 'priority' => ++$priority));
            }
        }
        return $inserted;
    }


    function updateElement($fields){
        if (isset($fields['list_element_id'])){
            $list_element_id = $fields['list_element_id'];
            $this->view->params[] = (isset($fields['list_id'])) ? $fields['list_id'] : $this->tree_data[$list_element_id]['parent_id'];
            $this->view->params[] = (isset($fields['catalog_id'])) ? $fields['catalog_id'] : $this->tree_data[$list_element_id]['catalog_id'];
            $this->view->params[] = (isset($fields['user_id'])) ? $fields['user_id'] : $this->tree_data[$list_element_id]['user_id'];
            $this->view->params[] = (isset($fields['note'])) ? $fields['note'] : addslashes($this->tree_data[$list_element_id]['note']);
            $this->view->params[] = (isset($fields['priority'])) ? $fields['priority'] : $this->tree_data[$list_element_id]['priority'];
            $this->view->params[] = $list_element_id;
            $rs = $this->view->get_query("view:LIT_UPD_LIST_CONTENT");
            if ($ar = $rs->affected_rows()){
                $list_id  = $fields['list_id'] ?: $this->tree_data[$list_element_id]['parent_id'];
                $name     = $this->tree_data[$list_element_id]['name'];
                $range_id = (isset($fields['range_id'])) ? $fields['range_id'] : $this->range_id;

                NotificationCenter::postNotification('LitListElementDidUpdate', array(
                    'list_id'  => $list_id,
                    'name'     => $name,
                    'range_id' =>  $range_id
                ));

                $this->triggerListChdate($list_id);
            }
            return $ar;
        } else {
            return false;
        }
    }

    function insertElement($fields){
        if (isset($fields['list_element_id'])){
            $list_element_id = $fields['list_element_id'];
            $this->view->params[] = (isset($fields['list_id'])) ? $fields['list_id'] : $this->tree_data[$list_element_id]['parent_id'];
            $this->view->params[] = (isset($fields['catalog_id'])) ? $fields['catalog_id'] : $this->tree_data[$list_element_id]['catalog_id'];
            $this->view->params[] = (isset($fields['user_id'])) ? $fields['user_id'] : $this->tree_data[$list_element_id]['user_id'];
            $this->view->params[] = (isset($fields['note'])) ? $fields['note'] : addslashes($this->tree_data[$list_element_id]['note']);
            $this->view->params[] = (isset($fields['priority'])) ? $fields['priority'] : $this->tree_data[$list_element_id]['priority'];
            $this->view->params[] = $list_element_id;
            $rs = $this->view->get_query("view:LIT_INS_LIST_CONTENT");
            if ($ar = $rs->affected_rows()) {
                $list_id  = $fields['list_id'] ?: $this->tree_data[$list_element_id]['parent_id'];
                $range_id = (isset($fields['range_id'])) ? $fields['range_id'] : $this->range_id;

                NotificationCenter::postNotification('LitListElementDidInsert', array(
                    'list_id'  => $list_id,
                    'name'     => '',
                    'range_id' =>  $range_id
                ));

                $this->triggerListChdate($this->tree_data[$list_element_id]['parent_id']);
            }
            return $ar;
        } else {
            return false;
        }
    }

    function deleteElement($element_id){
        $this->view->params[] = $element_id;
        $rs = $this->view->get_query("view:LIT_DEL_LIST_CONTENT");
        if ($ar = $rs->affected_rows()){
            $list_id = $this->tree_data[$element_id]['parent_id'];

            NotificationCenter::postNotification('LitListElementDidDelete', array(
                'list_id'  => $list_id,
                'name'     => $this->tree_data[$element_id]['name'],
                'range_id' =>  $this->range_id
            ));

            $this->triggerListChdate($list_id);
        }
        return $ar;
    }

    function updateList($fields){
        if (isset($fields['list_id'])){
            $list_id = $fields['list_id'];
            $this->view->params[] = (isset($fields['range_id'])) ? $fields['range_id'] : $this->range_id;
            $this->view->params[] = (isset($fields['name'])) ? $fields['name'] : addslashes($this->tree_data[$list_id]['name']);
            $this->view->params[] = (isset($fields['user_id'])) ? $fields['user_id'] : $this->tree_data[$list_id]['user_id'];
            $this->view->params[] = (isset($fields['format'])) ? $fields['format'] : addslashes($this->tree_data[$list_id]['format']);
            $this->view->params[] = (isset($fields['priority'])) ? $fields['priority'] : $this->tree_data[$list_id]['priority'];
            $this->view->params[] = (isset($fields['visibility'])) ? $fields['visibility'] : $this->tree_data[$list_id]['visibility'];
            $this->view->params[] = $list_id;
            $rs = $this->view->get_query("view:LIT_UPD_LIST");

            NotificationCenter::postNotification('LitListDidUpdate', array(
                'list_id'  => $list_id,
                'range_id' => $this->range_id,
                'name'     => $this->tree_data[$list_id]['name']
            ));

            return $rs->affected_rows();
        } else {
            return false;
        }
    }

    function insertList($fields){
        if (isset($fields['list_id'])){
            $list_id = $fields['list_id'];
            $this->view->params[] = (isset($fields['range_id'])) ? $fields['range_id'] : $this->range_id;
            $this->view->params[] = (isset($fields['name'])) ? $fields['name'] : addslashes($this->tree_data[$list_id]['name']);
            $this->view->params[] = (isset($fields['user_id'])) ? $fields['user_id'] : (string)$this->tree_data[$list_id]['user_id'];
            $this->view->params[] = (isset($fields['format'])) ? $fields['format'] : addslashes($this->tree_data[$list_id]['format']);
            $this->view->params[] = (isset($fields['priority'])) ? $fields['priority'] : (int)$this->tree_data[$list_id]['priority'];
            $this->view->params[] = (isset($fields['visibility'])) ? $fields['visibility'] : (int)$this->tree_data[$list_id]['visibility'];
            $this->view->params[] = $list_id;
            $rs = $this->view->get_query("view:LIT_INS_LIST");

            NotificationCenter::postNotification('LitListDidCreate', array(
                'list_id'  => $list_id,
                'range_id' => $this->range_id,
                'name'     => $fields['name']
            ));

            return $rs->affected_rows();
        } else {
            return false;
        }
    }

    function deleteList($list_id){
        $deleted = 0;
        $this->view->params[] = array($list_id);
        $rs = $this->view->get_query("view:LIT_DEL_LIST");
        $deleted += $rs->affected_rows();
        $this->view->params[] = array($list_id);
        $rs = $this->view->get_query("view:LIT_DEL_LIST_CONTENT_ALL");
        $deleted += $rs->affected_rows();

        NotificationCenter::postNotification('LitListDidDelete', array(
            'list_id'  => $list_id,
            'range_id' => $this->range_id,
            'name'     => $this->tree_data[$list_id]['name']
        ));

        return $deleted;
    }

    function triggerListChdate($list_id){
        $this->view->params[] = $GLOBALS['auth']->auth['uid'];
        $this->view->params[] = $list_id;
        $rs = $this->view->get_query("view:LIT_LIST_TRIGGER_UPDATE");
        return $rs->affected_rows();
    }

    function GetTabbedList($range_id, $list_id){
        $end_note_map = array(  'dc_type' => 'Reference Type', 'dc_title' => 'Title', 'dc_creator' => 'Author',
                                'year' => 'Year', 'dc_contributor' => 'Secondary Author', 'dc_publisher' => 'Publisher',
                                'dc_identifier' => 'ISBN/ISSN', 'dc_source' => 'Original Publication', 'dc_subject' => 'Keywords',
                                'dc_description' => 'Abstract', 'accession_number' => 'Accession Number', 'note' => 'Notes', 'external_link' => 'URL');
        $dbv = DbView::getView('literatur');
        $tree = TreeAbstract::GetInstance("StudipLitList", $range_id);
        $ret = "*Generic\n";
        foreach ($end_note_map as $fields){
            $ret .= $fields . "\t";
        }
        $ret .= "\n";
        if ($tree->hasKids($list_id)){
            $dbv->params[0] = $list_id;
            $rs = $dbv->get_query("view:LIT_LIST_GET_ELEMENTS");
            while ($rs->next_record()){
                $tree->cat_element->setValues($rs->Record);
                $tree->cat_element->fields['note']['value'] = $tree->tree_data[$rs->f('list_element_id')]['note'];
                foreach ($end_note_map as $studip_field => $end_note_field){
                    $value = $tree->cat_element->getValue($studip_field);
                    if ($studip_field == 'dc_type'
                    && ($value == '' || !in_array($value, $tree->cat_element->fields['dc_type']['select_list']))){
                        $value = "Book";
                    }
                    $value = str_replace(array("\n","\r","\t"),'',$value);
                    $ret .= $value . "\t";
                }
                $ret .= "\n";
            }
        }
        return $ret;
    }

    function DeleteListsByRange($range_id){
        $deleted = null;
        $view = DbView::getView('literatur');
        $view->params[] = $range_id;
        $rs = $view->get_query("view:LIT_GET_LIST_BY_RANGE");
        while ($rs->next_record()){
            $list_ids[] =  $rs->f("list_id");
        }
        if (is_array($list_ids)){
            $view->params[] = $list_ids;
            $rs = $view->get_query("view:LIT_DEL_LIST");
            $deleted['list'] = $rs->affected_rows();
            $view->params[] = $list_ids;
            $rs = $view->get_query("view:LIT_DEL_LIST_CONTENT_ALL");
            $deleted['list_content'] = $rs->affected_rows();
        }
        return $deleted;
    }

    function GetListCountByRange($range_id){
        $dbv = DbView::getView('literatur');
        $dbv->params[0] = $range_id;
        $rs = $dbv->get_query("view:LIT_GET_LIST_COUNT_BY_RANGE");
        $rs->next_record();
        return array("visible_list" => $rs->f("visible_list"),"invisible_list" => $rs->f("invisible_list"));
    }

    public static function GetListsByRange($range_id, $format = 'default')
    {
        $view = DbView::getView('literatur');
        $view->params[] = $range_id;
        $rs = $view->get_query("view:LIT_GET_LIST_BY_RANGE");

        $list_ids = [];
        while ($rs->next_record()){
            if ($format === 'default') {
                $list_ids[$rs->f('list_id')] = $rs->f('name');
            } elseif ($format === 'form_options') {
                $list_ids[] = [
                    'name' => $rs->f('name'),
                    'value' => $rs->f('list_id')
                ];
            }
        }
        return $list_ids;
    }

    public static function GetFormattedListsByRange($range_id, $last_modified_since = false, $copy_link = true)
    {
        $ret = false;
        $dbv = DbView::getView('literatur');
        $tree = TreeAbstract::GetInstance("StudipLitList", $range_id);
        if ( ($lists = $tree->getVisibleListIds()) ){
            for ($i = 0; $i < count($lists); ++$i){
                if ( ($tree->tree_data[$lists[$i]]['user_id'] != $GLOBALS['auth']->auth['uid'])
                && ($last_modified_since !== false)
                && ($tree->tree_data[$lists[$i]]['chdate'] > $last_modified_since) ){
                    $ret .= '<div align="left" style="color:red" title="' . htmlReady(sprintf(_("Letzte Änderung am %s von %s"),
                    date('d M Y H:i',$tree->tree_data[$lists[$i]]['chdate']),
                    $tree->tree_data[$lists[$i]]['fullname'])) . '">';
                    $ret .=  "<b><u>" . htmlReady($tree->tree_data[$lists[$i]]['name']) . "</u></b>\n<br>\n";
                    $ret .= '</div>';
                } else {
                    $ret .= "\n<h3>" . htmlReady($tree->tree_data[$lists[$i]]['name']) . "</h3>";
                }
                if ($copy_link){
                    $ret .= "\n<div align=\"right\" style=\"font-size:10pt\"><a href=\"".URLHelper::getLink("dispatch.php/literature/edit_list?cmd=CopyUserList&_range_id=self&user_list=".$lists[$i]."#anchor")."\">";
                    $ret .= Icon::create('literature+add', 'clickable')->asImg(16, ["class" => 'text-top']);
                    $ret .= " " . _("Literaturliste kopieren") . "</a></div>";
                } else {
                    $ret .= "\n<br>\n";
                }
                $ret .= "\n<ul>\n";
                if ($tree->hasKids($lists[$i])){
                    $dbv->params[0] = $lists[$i];
                    $rs = $dbv->get_query("view:LIT_LIST_GET_ELEMENTS");
                    while ($rs->next_record()){
                        if ( ($tree->tree_data[$rs->f('list_element_id')]['user_id'] != $GLOBALS['auth']->auth['uid'])
                        && ($last_modified_since !== false)
                        && ($tree->tree_data[$rs->f('list_element_id')]['chdate'] > $last_modified_since) ){
                            $ret .= '<li style="color:red" title="' . htmlReady(sprintf(_("Letzte Änderung am %s von %s"),
                            date('d M Y H:i',$tree->tree_data[$rs->f('list_element_id')]['chdate']),
                            $tree->tree_data[$rs->f('list_element_id')]['fullname'])) . '">';
                            $ret .=  formatReady($tree->getFormattedEntry($rs->f('list_element_id'), $rs->Record), false, true) . "\n<br>\n";
                            $ret .= '</li>';
                        } else {
                            $ret .= "<li>".formatReady($tree->getFormattedEntry($rs->f('list_element_id'), $rs->Record), false, true) . "</li>";
                        }
                    }
                }
                $ret .= "\n</ul><br>";
            }
        }
        return $ret;
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $field_data = DBManager::get()->fetchAll("SELECT * FROM lit_list WHERE user_id = ?", [$storage->user_id]);
        if ($field_data) {
            $storage->addTabularData(_('Literaturlisten'), 'lit_list', $field_data);
        }
        $field_data = DBManager::get()->fetchAll("SELECT * FROM lit_list_content WHERE user_id = ?", [$storage->user_id]);
        if ($field_data) {
            $storage->addTabularData(_('Literaturlisten Inhalte'), 'lit_list_content', $field_data);
        }
    }
}
?>
