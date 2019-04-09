<?php
# Lifter001: TEST
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitListAdmin.class.php
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

use Studip\Button, Studip\LinkButton;

require_once 'lib/classes/lit_search_plugins/StudipLitSearchPluginZ3950Abstract.class.php';

/**
 *
 *
 *
 * @access   public
 * @author   André Noack <noack@data-quest.de>
 * @package
 */
class StudipLitListViewAdmin extends TreeView
{
    var $mode;

    var $edit_item_id;

    var $clip_board;

    var $format_info;


    /**
     * constructor
     *
     * calls the base class constructor
     * @access public
     */
    function __construct($range_id){
        $this->use_aging = true;
        $this->format_info = _("Felder müssen in geschweiften Klammern (z.B. {dc_title}) angegeben werden.\n")
                             . _("Felder und Text, der zwischen senkrechten Strichen steht, wird nur angezeigt, wenn das angegebene Feld nicht leer ist. (z.B. |Anmerkung: {note}|)\n")
                             . _("Folgende Felder können angezeigt werden:\n")
                             . _("Titel - dc_title\n")
                             . _("Verfasser oder Urheber - dc_creator\n")
                             . _("Thema und Stichwörter - dc_subject\n")
                             . _("Inhaltliche Beschreibung - dc_description\n")
                             . _("Verleger, Herausgeber - dc_publisher\n")
                             . _("Weitere beteiligten Personen und Körperschaften - dc_contributor\n")
                             . _("Datum - dc_date\n")
                             . _("Ressourcenart - dc_type\n")
                             . _("Format - dc_format\n")
                             . _("Ressourcen-Identifikation - dc_identifier\n")
                             . _("Quelle - dc_source\n")
                             . _("Sprache - dc_language\n")
                             . _("Beziehung zu anderen Ressourcen - dc_relation\n")
                             . _("Räumliche und zeitliche Maßangaben - dc_coverage\n")
                             . _("Rechtliche Bedingungen - dc_rights\n")
                             . _("Zugriffsnummer - accession_number\n")
                             . _("Jahr - year\n")
                             . _("alle Autoren - authors\n")
                             . _("Herausgeber mit Jahr - published\n")
                             . _("Anmerkung - note\n")
                             . _("link in externes Bibliothekssystem - external_link\n");

        parent::__construct("StudipLitList", $range_id); //calling the baseclass constructor
        $this->clip_board = StudipLitClipBoard::GetInstance();
    }

    function parseCommand(){
        if (Request::quoted('mode'))
            $this->mode = Request::quoted('mode');
        if (Request::option('cmd')){
            $exec_func = "execCommand" . Request::option('cmd');
            if (method_exists($this,$exec_func)){
                if ($this->$exec_func()){
                    $this->tree->init();
                }
            }
        }
    }


    function execCommandEditItem(){
        $item_id = Request::option('item_id');
        $this->mode = "EditItem";
        $this->anchor = $item_id;
        $this->edit_item_id = $item_id;
        return false;
    }

    function execCommandInClipboard(){
        $item_id = Request::option('item_id');
        if (is_object($this->clip_board)){
            if ($this->tree->isElement($item_id)){
                $this->clip_board->insertElement($this->tree->tree_data[$item_id]['catalog_id']);
            } else {
                if ($this->tree->getNumKids($item_id)){
                    $kids = $this->tree->getKids($item_id);
                    for ($i = 0; $i < $this->tree->getNumKids($item_id); ++$i){
                        $cat_ids[] = $this->tree->tree_data[$kids[$i]]['catalog_id'];
                    }
                    $this->clip_board->insertElement($cat_ids);
                }
            }
        }
        return false;
    }

    function execCommandInsertItem(){
        $item_id = Request::option('item_id');
        $parent_id = Request::option('parent_id');
        $user_id = $GLOBALS['auth']->auth['uid'];
        if ($this->mode != "NewItem"){
            if (Request::get('edit_note')){
                $affected_rows = $this->tree->updateElement(['list_element_id' => $item_id, 'note' => Request::quoted('edit_note'), 'user_id' => $user_id]);
                if ($affected_rows){
                    PageLayout::postSuccess(_("Anmerkung wurde geändert."));
                } else {
                    PageLayout::postInfo(_("Keine Veränderungen vorgenommen."));
                }
            } else if ( Request::get('edit_format') ) {

                $affected_rows = $this->tree->updateList(['list_id' => $item_id,'format' => Request::quoted('edit_format'),'name' => Request::quoted('edit_name'),'visibility' => Request::quoted('edit_visibility'), 'user_id' => $user_id]);
                if ($affected_rows){
                    PageLayout::postSuccess(_("Listeneigenschaften wurden geändert."));
                } else {
                    PageLayout::postInfo(_("Keine Veränderungen vorgenommen."));
                }
            }
        } else {
            $priority = $this->tree->getMaxPriority($parent_id) + 1;
            $affected_rows = $this->tree->insertList(['list_id' => $item_id,'priority' => $priority, 'format' => Request::quoted('edit_format'),'visibility' => Request::quoted('edit_visibility'), 'name' => Request::quoted('edit_name'),'user_id' => $user_id]);
            if ($affected_rows){
                $this->mode = "";
                $this->anchor = $item_id;
                $this->open_items[$item_id] = true;
                PageLayout::postSuccess(_("Diese Liste wurde neu eingefügt."));
            }
        }
        $this->mode = "";
        $this->anchor = $item_id;
        $this->open_items[$item_id] = true;
        return true;
    }

    function execCommandCopyList(){
        $item_id = Request::option('item_id');
        if ($new_list_id = $this->tree->copyList($item_id)){
            $this->anchor = $new_list_id;
            $this->open_ranges[$new_list_id] = true;
            $this->open_items[$new_list_id] = true;
            PageLayout::postSuccess(_("Diese Liste wurde kopiert."));
        } else {
            $this->anchor = $item_id;
            PageLayout::postError(_("Die Liste konnte nicht kopiert werden."));
        }
        return true;
    }

    function execCommandCopyUserList(){
        $list_id = Request::quoted('user_list');
        if ($new_list_id = $this->tree->copyList($list_id)){
            $this->anchor = $new_list_id;
            $this->open_ranges[$new_list_id] = true;
            $this->open_items[$new_list_id] = true;
            PageLayout::postSuccess(_("Diese Liste wurde kopiert."));
        } else {
            $this->anchor = 'root';
            PageLayout::postError(_("Die Liste konnte nicht kopiert werden."));
        }
        return true;
    }

    function execCommandToggleVisibility(){
        $item_id = Request::option('item_id');
        $user_id = $GLOBALS['auth']->auth['uid'];
        $visibility = ($this->tree->tree_data[$item_id]['visibility']) ? 0 : 1;
        if ($this->tree->updateList(['list_id' => $item_id, 'visibility' => $visibility, 'user_id' => $user_id])){
            PageLayout::postSuccess(_("Die Sichtbarkeit der Liste wurde geändert."));
        } else {
            PageLayout::postError(_("Die Sichtbarkeit konnte nicht geändert werden."));
        }
        $this->anchor = $item_id;
        return true;
    }

    function execCommandOrderItem(){
        $direction = Request::quoted('direction');
        $item_id = Request::option('item_id');
        $items_to_order = $this->tree->getKids($this->tree->tree_data[$item_id]['parent_id']);
        if (!$items_to_order){
            return false;
        }
        for ($i = 0; $i < count($items_to_order); ++$i){
            if ($item_id == $items_to_order[$i])
                break;
        }
        if ($direction == "up" && isset($items_to_order[$i-1])){
            $items_to_order[$i] = $items_to_order[$i-1];
            $items_to_order[$i-1] = $item_id;
        } elseif (isset($items_to_order[$i+1])){
            $items_to_order[$i] = $items_to_order[$i+1];
            $items_to_order[$i+1] = $item_id;
        }
        for ($i = 0; $i < count($items_to_order); ++$i){
            if ($this->tree->isElement($item_id)){
                $this->tree->updateElement(['priority' => $i, 'list_element_id' => $items_to_order[$i]]);
            } else {
                $this->tree->updateList(['priority' => $i, 'list_id' => $items_to_order[$i]]);
            }
        }
        $this->mode = "";
        PageLayout::postSuccess(($direction == "up") ? _("Element wurde um eine Position nach oben verschoben.") : _("Element wurde um eine Position nach unten verschoben."));
        return true;
    }

    function execCommandSortKids(){
        $item_id = Request::option('item_id');
        $kids = $this->tree->getKids($item_id);
        usort($kids, function ($a, $b) {
            $the_tree = TreeAbstract::GetInstance('StudipLitList', $this->tree->range_id);
            return strnatcasecmp(
                StudipLitSearchPluginZ3950Abstract::ConvertUmlaute($the_tree->getValue($a, 'name')),
                StudipLitSearchPluginZ3950Abstract::ConvertUmlaute($the_tree->getValue($b, 'name'))
            );
        });
        foreach($kids as $pos => $kid_id){
            if ($this->tree->isElement($kid_id)){
                $this->tree->updateElement(['priority' => $pos, 'list_element_id' => $kid_id]);
            } else {
                $this->tree->updateList(['priority' => $pos, 'list_id' => $kid_id]);
            }
        }
        $this->mode = "";
        PageLayout::postSuccess(_("Die Unterelemente wurden alphabetisch sortiert."));
        return true;
    }

    function execCommandAssertDeleteItem(){
        $item_id = Request::option('item_id');
        $this->mode = "AssertDeleteItem";

        $question = _("Sie beabsichtigen, diese Liste inklusive aller Einträge zu löschen. ")
                    . sprintf(_("Es werden insgesamt %s Einträge gelöscht!"), count($this->tree->getKidsKids($item_id)))
                    . "\n" . _("Wollen Sie diese Liste wirklich löschen?");

        PageLayout::postQuestion(
            $question,
            URLHelper::getURL($this->getSelf("cmd=DeleteItem&item_id={$item_id}")),
            URLHelper::getURL($this->getSelf("cmd=Cancel&item_id={$item_id}"))
        );

        return false;
    }

    function execCommandDeleteItem(){
        $item_id = Request::option('item_id');
        $deleted = 0;
        $item_name = $this->tree->tree_data[$item_id]['name'];
        $this->anchor = $this->tree->tree_data[$item_id]['parent_id'];
        if (!$this->tree->isElement($item_id) && $this->mode == "AssertDeleteItem"){
            $deleted = $this->tree->deleteList($item_id);
            if ($deleted){
                PageLayout::postSuccess(sprintf(_("Die Liste <b>%s</b> und alle Einträge (insgesamt %s) wurden gelöscht. "),htmlReady($item_name),$deleted-1));
            } else {
                PageLayout::postError(_("Fehler, die Liste konnte nicht gelöscht werden!"));
            }
        } else {
            $deleted = $this->tree->deleteElement($item_id);
            if ($deleted){
                PageLayout::postSuccess(sprintf(_("Der Eintrag <b>%s</b> wurde gelöscht. "),htmlReady($item_name)));
            } else {
                PageLayout::postError(_("Fehler, der Eintrag konnte nicht gelöscht werden!"));
            }
        }
        $this->mode = "";
        $this->open_items[$this->anchor] = true;
        return true;
    }

    function execCommandNewItem(){
        $item_id = Request::option('item_id');
        $new_item_id = md5(uniqid("listblubb",1));
        $this->tree->tree_data[$new_item_id] = [
            'chdate' => time(),
            'format'=> $this->tree->format_default,
            'user_id' => $GLOBALS['auth']->auth['uid'],
            'username' => $GLOBALS['auth']->auth['uname'],
            'fullname' => get_fullname($GLOBALS['auth']->auth['uid'],'no_title_short'),
            'visibility' => 0
        ];
        $this->tree->storeItem($new_item_id, $item_id, _("Neue Liste"),$this->tree->getMaxPriority($item_id) + 1);
        $this->anchor = $new_item_id;
        $this->edit_item_id = $new_item_id;
        $this->open_ranges[$item_id] = true;
        $this->open_items[$new_item_id] = true;
        PageLayout::postInfo(_("Diese neue Liste wurde noch nicht gespeichert."));
        $this->mode = "NewItem";
        return false;
    }

    function execCommandCancel(){
        $item_id = Request::option('item_id');
        $this->mode = "";
        $this->anchor = $item_id;
        return false;
    }

    function getItemContent($item_id) {
        $edit_content = false;

        if ($item_id == $this->edit_item_id) {
            $content .= $this->getEditItemContent();
        }
        else {
            $content = "\n<table width=\"90%\" cellpadding=\"2\" cellspacing=\"0\" align=\"center\">";

            if ($item_id == "root" && $this->tree->range_type != 'user') {
                $content .= $this->getTableRowForRootInLiteratur();
            }

            if ($this->tree->isElement($item_id)) {
                $content .= $this->getTopRowForTableBox(_("Vorschau:"));
                $content .= $this->getLiteratureEntryRowForTableBox($item_id);
                $content .= $this->getBottomRowForTableBox($item_id);
            } elseif ($item_id != 'root') {
                $content .= $this->getTopRowForTableBox(_("Formatierung:"));
                $content .= $this->getFormatRowForTableBox($item_id);
                $content .= $this->getSubTitleRowForTableBox(_("Sichtbarkeit:"));
                $content .= $this->getVisibilityStatusRowForTableBox($item_id);
                $content .= $this->getBottomRowForTableBox($item_id);
            }
            $content .= '</table>';
        }



        if (!$edit_content) {
            $content .= '<div style="text-align: center;">';

            if ($item_id == "root") {
                $content .= $this->getNewLiteratureButton($item_id);
            }
            elseif ($this->mode != "NewItem") {
                if ($this->tree->isElement($item_id)) {
                    $content .= $this->getEditLiteratureEntryButton($item_id);
                    $content .= $this->getDetailsButton($item_id);
                    $content .= $this->getDeleteButton($item_id, "DeleteItem");
                } else {
                    $content .= $this->getEditFormatingButton($item_id);
                    $content .= $this->getCopyListButton($item_id);
                    $content .= $this->getSortButton($item_id);
                    $content .= $this->getExportButton($item_id);
                    $content .= $this->getDeleteButton($item_id, "AssertDeleteItem");
                }

                if ($this->tree->isElement($item_id)) {
                    if (!$this->isInClipboard($item_id)) {
                        $content .= $this->getToClipboardButton($item_id);
                    }
                }
            }

            $content .= "</div></form>";
        }

        return $content;
    }

    public function getTableRowForRootInLiteratur()
    {
        $user_lists = StudipLitList::GetListsByRange($GLOBALS['user']->id);
        $content = '';
        $content .= "\n<tr><td class=\"table_row_even\" align=\"left\">";
        $content .= "\n<form class=\"default\" name=\"userlist_form\" action=\"" . URLHelper::getLink($this->getSelf("cmd=CopyUserList")) . "\" method=\"POST\">";
        $content .= CSRFProtection::tokenTag();
        $content .= "<fieldset><legend>" . _("Persönliche Literaturlisten") .'</legend>'
                    . "<label><select name=\"user_list\" style=\"vertical-align:middle;width:70%;\">";
        if (is_array($user_lists)) {
            foreach ($user_lists as $list_id => $list_name) {
                $content .= "\n<option value=\"$list_id\">" . htmlReady($list_name) . "</option>";
            }
        }
        $content .= "\n</select></label></fieldset><footer>"
                 . Button::create(_('Kopie erstellen'), ['title' => _('Eine Kopie der ausgewähkten Liste erstellen')])
                 . "</footer></form></td></tr>";

        return $content;
    }


    function getTopRowForTableBox($title){
        $content = '';
        $content .= "\n<tr><td class=\"table_row_odd\" align=\"left\" style=\"border-top: 1px solid black;border-left: 1px solid black;border-right: 1px solid black; font-weight: bold;\">";
        $content .= $title;
        $content .= " </td></tr>";

        return $content;
    }


    function getLiteratureEntryRowForTableBox($item_id){
        $content = '';
        $content .= "\n<tr><td class=\"table_row_even\" align=\"left\" style=\"border-left: 1px solid black;border-right: 1px solid black;\">";
        $content .= formatReady($this->tree->getFormattedEntry($item_id), false, true);
        $content .= " </td></tr>";

        return $content;
    }


    function getFormatRowForTableBox($item_id){
        $content = '';
        $content .= "\n<tr><td class=\"table_row_even\" align=\"left\" style=\"border-left: 1px solid black;border-right: 1px solid black;\">";
        $content .= htmlReady($this->tree->tree_data[$item_id]['format'], false, true);
        $content .= " &nbsp;</td></tr>";

        return $content;
    }

    function getVisibilityStatusRowForTableBox($item_id){
        $content = '';
        $content .= "\n<tr><td class=\"table_row_even\" align=\"left\" style=\"border-left: 1px solid black;border-right: 1px solid black;\">";

        if ($this->tree->tree_data[$item_id]['visibility']){
            $content .= Icon::create('visibility-visible', 'info')->asImg(16, ["style" => 'vertical-align: bottom']);
            $content .= "&nbsp;" . _("Sichtbar");
        }
        else{
            $content .= Icon::create('visibility-invisible', 'info')->asImg(16, ["style" => 'vertical-align: bottom']);
            $content .= "&nbsp;" . _("Unsichtbar");
        }

        $content .=  " </td></tr>";

        return $content;
    }


    function getSubTitleRowForTableBox($title){
        $content = '';
        $content .= "\n<tr><td class=\"table_row_odd\" align=\"left\" style=\"border-left: 1px solid black;border-right: 1px solid black; font-weight: bold;\">";
        $content .= $title;
        $content .= "</td></tr>";

        return $content;
    }


    function getBottomRowForTableBox($item_id){
        $content = '';
        $content .= "\n<tr><td class=\"table_row_odd\" align=\"right\" style=\"border-bottom: 1px solid black;border-left: 1px solid black;border-right: 1px solid black;\">";
        $content .= _("Letzte Änderung") . ':';
        $content .= strftime(" %d.%m.%Y ", $this->tree->tree_data[$item_id]['chdate']);
        $content .= "(<a href=\"dispatch.php/profile?username=";
        $content .= $this->tree->tree_data[$item_id]['username'];
        $content .= "\">" . htmlReady($this->tree->tree_data[$item_id]['fullname']) . "</a>) </td></tr>";

        return $content;
    }

    function getNewLiteratureButton($item_id){
        $content = LinkButton::create(_('Neue Literaturliste'),
            URLHelper::getURL($this->getSelf('cmd=NewItem&item_id='.$item_id)),
            ['title' => _('Eine neue Literaturliste anlegen')]);

        return $content;
    }

    function getEditFormatingButton($item_id){
        $content = LinkButton::create(_('Bearbeiten'),
            URLHelper::getURL($this->getSelf('cmd=EditItem&item_id='.$item_id)),
            ['title' => _("Dieses Element bearbeiten")]);
        $content .= "&nbsp;";

        return $content;
    }

    function getEditLiteratureEntryButton($item_id){
        $content = LinkButton::create(_('Anmerkung'),
            URLHelper::getURL($this->getSelf('cmd=EditItem&item_id='. $item_id)),
            ['title' => _('Dieses Element bearbeiten')]);
        $content .= "&nbsp;";

        return $content;
    }

    function getDetailsButton($item_id){
        $content = LinkButton::create(_('Details'),
            URLHelper::getURL('dispatch.php/literature/edit_element?_catalog_id='.$this->tree->tree_data[$item_id]['catalog_id']),
            ['title' => _('Detailansicht dieses Eintrages ansehen.'), 'data-dialog' => '']);
        $content .= "&nbsp;";

        return $content;
    }

    function getCopyListButton($item_id){
        $content = LinkButton::create(_('Kopie erstellen'),
            URLHelper::getURL($this->getSelf('cmd=CopyList&item_id='.$item_id)),
            ['title' => _('Eine Kopie dieser Liste erstellen')]);
        $content .= "&nbsp;";

        return $content;
    }

    function getSortButton($item_id){
        $content = LinkButton::create(_('Sortieren'),
            URLHelper::getURL($this->getSelf('cmd=SortKids&item_id='.$item_id)),
            ['title' => _('Elemente dieser Liste alphabetisch sortieren')]);
        $content .= "&nbsp;";

        return $content;
    }

    function getExportButton($item_id){
        global $perm, $TMP_PATH;

        $temporary_file_name = md5(uniqid('StudipLitListViewAdmin::getExportButton', true));

        //build a temporary file containing the data (if the user is permitted to do so):
        if ($this->tree->range_id == $GLOBALS['user']->id || $perm->have_studip_perm('tutor', $this->tree->range_id)) {

            $data = StudipLitList::GetTabbedList($this->tree->range_id, $item_id);

            file_put_contents($TMP_PATH . '/' . $temporary_file_name, $data);
        }

        //output the link to the file via a link button:
        $content = LinkButton::create(_('Export'),
            FileManager::getDownloadURLForTemporaryFile(
                $temporary_file_name,
                $this->tree->tree_data[$item_id]['name'] . '.txt'
            ),
            ['title' => _('Export der Liste in EndNote-kompatiblem Format')]
        );
        $content .= '&nbsp;';

        return $content;
    }

    function getDeleteButton($item_id, $cmd){
        $content = LinkButton::create(_('Löschen'),
            URLHelper::getURL($this->getSelf('cmd='.$cmd.'&item_id='.$item_id)),
            ['title' => _('Dieses Element löschen')]);
        $content .= '&nbsp;';

        return $content;
    }

    function getToClipboardButton($item_id){
        $content = LinkButton::create(_('Merkliste'),
            URLHelper::getURL($this->getSelf('cmd=InClipboard&item_id='.$item_id)),
            ['title' => _('Eintrag in Merkliste aufnehmen')]);
        $content .= '&nbsp;';

        return $content;
    }

    function isInClipboard($item_id){
        return $this->clip_board->isInClipboard($this->tree->tree_data[$item_id]["catalog_id"]);
    }

    function getItemHead($item_id)
    {
        $head = "";
        $head .= parent::getItemHead($item_id);
        if ($this->tree->tree_data[$item_id]['parent_id'] == $this->start_item_id){
            $anzahl = " (" . $this->tree->getNumKids($item_id) . ")";
            $head .= ($this->open_items[$item_id]) ? "<b>" . $anzahl . "</b>" : $anzahl;
        }
        if ($item_id != $this->start_item_id && $item_id != $this->edit_item_id){
            $head .= "</td><td align=\"right\" valign=\"bottom\" nowrap class=\"printhead\">";
            if (!$this->tree->isFirstKid($item_id)){
                $head .= " <a href=\"". URLHelper::getLink($this->getSelf("cmd=OrderItem&direction=up&item_id=$item_id")) .
                         "\">".Icon::create('arr_2up', 'sort', ['title' => _("Element nach oben verschieben")])->asImg(16, ["alt" => _("Element nach oben verschieben")])."</a>";
            }
            if (!$this->tree->isLastKid($item_id)){
                $head .= " <a href=\"". URLHelper::getLink($this->getSelf("cmd=OrderItem&direction=down&item_id=$item_id")) .
                         "\">".Icon::create('arr_2down', 'sort', ['title' => _("Element nach unten verschieben")])->asImg(16, ["alt" => _("Element nach unten verschieben")])."</a>";
            }
            if ($this->tree->isElement($item_id)){
                $head .= ($this->clip_board->isInClipboard($this->tree->tree_data[$item_id]["catalog_id"]))
                    ? Icon::create('exclaim', 'attention', ['title' => _('Dieser Eintrag ist bereits in Ihrer Merkliste')])->asImg()
                    : "<a href=\"". URLHelper::getLink($this->getSelf("cmd=InClipboard&item_id=$item_id")) ."\">"
                      . Icon::create('exclaim', 'clickable', ['title' => _('Eintrag in Merkliste aufnehmen')])->asImg()
                      . "</a>";
            } else {
                $head .= " <a href=\"". URLHelper::getLink($this->getSelf("cmd=InClipboard&item_id=$item_id")) . "\">";
                $head .= Icon::create('exclaim', 'clickable', ['title' => _('Komplette Liste in Merkliste aufnehmen')])->asImg();
                $head .= "</a>";
            }
            $head .= "";
        }
        return $head;
    }

    function getItemHeadPics($item_id)
    {
        $head = $this->getItemHeadFrontPic($item_id);
        $head .= "\n<td  class=\"printhead\" nowrap  align=\"left\" valign=\"bottom\">";
        if (!$this->tree->isElement($item_id)){
            if ($this->tree->hasKids($item_id)){
                $head .= "<a href=\"";
                $head .= ($this->open_ranges[$item_id]) ? URLHelper::getLink($this->getSelf("close_range={$item_id}")) : URLHelper::getLink($this->getSelf("open_range={$item_id}"));
                $head .= "\"> ";
                $head .= Icon::create('folder-full', 'clickable', ['title' => $this->open_ranges[$item_id]?_('Alle Unterelemente schließen'):_('Alle Unterelemente öffnen')])->asImg(16);
                $head .= "</a>";
            } else {
                $head .= Icon::create('folder-full', 'clickable', ['title' => _('Dieses Element hat keine Unterelemente')])->asImg();
            }
            if ($item_id != "root"){
                $head .= " <a href=\"" . URLHelper::getLink($this->getSelf("cmd=ToggleVisibility&item_id={$item_id}")) . "\">";
                $head .= Icon::create($this->tree->tree_data[$item_id]['visibility']
                    ? 'visibility-visible'
                    : 'visibility-invisible',
                    'clickable',
                    ['title' => _('Sichtbarkeit ändern')])
                             ->asImg();
                $head .= "</a>";
            }
        } else {
            $head .= Icon::create('literature', 'clickable')->asImg();
        }
        return $head . "</td>";
    }

    function getEditItemContent(){
        $content = "\n<form style=\"width: 98%; margin: auto;\" class=\"default\" name=\"item_form\" action=\"" . URLHelper::getLink($this->getSelf("cmd=InsertItem&item_id={$this->edit_item_id}")) . "\" method=\"POST\">";
        $content .= CSRFProtection::tokenTag();
        $content .= '<fieldset><legend>' ._('Liste') . '</legend>';
        $content .= "\n<input type=\"HIDDEN\" name=\"parent_id\" value=\"{$this->tree->tree_data[$this->edit_item_id]['parent_id']}\">";
        if ($this->tree->isElement($this->edit_item_id)){
            $content .= "\n<b>". _("Anmerkung zu einem Eintrag bearbeiten:") . "</b>";
            $edit_name = "note";
            $rows = 5;
            $content .= "<textarea name=\"edit_{$edit_name}\" rows=\"$rows\">" . htmlReady($this->tree->tree_data[$this->edit_item_id][$edit_name])
                        . "</textarea></td></tr>";
        } else {
            $content .= '<label>'. _("Name") . "";
            $content .= "<input type=\"text\" name=\"edit_name\" style=\"width:99%\" value=\""
                     . htmlReady($this->tree->tree_data[$this->edit_item_id]['name']) . "\">"
                     . '</label>';

            $edit_name = "format";
            $rows = 2;
            $content .= '<label>'. _("Formatierung");
            $content .= Icon::create('info-circle', 'inactive', ['title' => $this->format_info])->asImg(['class' => 'text-top']);
            $content .= "<textarea name=\"edit_{$edit_name}\" style=\"width:99%\" rows=\"$rows\">" . htmlReady($this->tree->tree_data[$this->edit_item_id][$edit_name])
                        . "</textarea></label>";
            $content .= '<div>'. _("Sichtbarkeit") .'</div>';
            $content .= '<section class="hgroup">'
                     . '<label><input type="radio" name="edit_visibility" value="1" '
                     . (($this->tree->tree_data[$this->edit_item_id]['visibility']) ? "checked" : "")
                     . '>' . _("Ja") . '</label>'
                     . '<label><input type="radio" name="edit_visibility" value="0" '
                     . ((!$this->tree->tree_data[$this->edit_item_id]['visibility']) ? "checked" : "") . ">" . _("Nein")
                     . '</section></label>';
        }

        $content .= '</fieldset>';
        $content .= '<footer><div class="button-group">'
                    . Button::createAccept(_('Speichern'),[
                        'title' => _("Einstellungen speichern")])
                    . LinkButton::createCancel(_('Abbrechen'),
                        URLHelper::getURL($this->getSelf("cmd=Cancel&item_id=".$this->edit_item_id)),
                        ['Aktion abbrechen' => _('Aktion abbrechen')])
                    . '</div></footer>';
        $content .= "\n</form>";

        return $content;
    }

    function getItemMessage($item_id,$colspan = 1){
        $content = "";
        if ($this->msg[$item_id]){
            $msg = explode("§",$this->msg[$item_id]);
            $pics = [
                'error' => Icon::create('decline', 'attention'),
                'info'  => Icon::create('info', 'info'),
                'msg'   => Icon::create('accept', 'accept')];
            $content = "\n<tr><td colspan=\"{$colspan}\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" width=\"100%\">
                        <tr><td align=\"center\" width=\"25\">" . $pics[$msg[0]]->asImg() . "</td>
                        <td align=\"left\">" . $msg[1] . "</td></tr>
                        </table></td></tr><tr>";
        }
        return $content;
    }

    function getSelf($param = false){
        $url_params = "foo=" . DbView::get_uniqid();
        if ($this->mode) $url_params .= "&mode=" . $this->mode;
        if ($param) $url_params .= '&' . $param;
        return parent::getSelf($url_params);
    }
}
?>
