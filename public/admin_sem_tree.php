<?php
# Lifter001: TEST
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_sem_tree.php
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

require '../lib/bootstrap.php';

page_open(["sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"]);
$perm->check(Config::get()->SEM_TREE_ADMIN_PERM ?: 'admin');
if (!$perm->is_fak_admin()){
    $perm->perm_invalid(0,0);
    page_close();
    die;
}

include 'lib/seminar_open.php'; // initialise Stud.IP-Session

PageLayout::setTitle(Config::get()->UNI_NAME_CLEAN . " - " . _("Veranstaltungshierachie bearbeiten"));
Navigation::activateItem('/admin/locations/sem_tree');

// Start of Output
ob_start();

$view = DbView::getView('sem_tree');
$the_tree = new StudipSemTreeViewAdmin(Request::option('start_item_id'));
$search_obj = new StudipSemSearch();

$_open_items =& $the_tree->open_items;
$_open_ranges =& $the_tree->open_ranges;
$_possible_open_items = [];

if (!Config::GetInstance()->getValue('SEM_TREE_ALLOW_BRANCH_ASSIGN')){
    if(is_array($_open_items)){
        foreach($_open_items as $item_id => $value){
            if(!$the_tree->tree->getNumKids($item_id)) $_possible_open_items[$item_id] = $value;
        }
    }
} else {
    $_possible_open_items = $_open_items;
}

// allow add only for items where user has admin permission and which are not hidden
if (is_array($_possible_open_items)) {
    foreach ($_possible_open_items as $item_id => $value) {
        if (!$the_tree->isItemAdmin($item_id) || $the_tree->tree->isHiddenItem($item_id)) {
            unset($_possible_open_items[$item_id]);
        }
    }
}

if ($search_obj->search_done){
    if ($search_obj->search_result->numRows > 50){
        PageLayout::postError(_("Es wurden mehr als 50 Veranstaltungen gefunden! Bitte schränken Sie Ihre Suche weiter ein."));
    } elseif ($search_obj->search_result->numRows > 0){
        PageLayout::postSuccess(sprintf(
            _("Es wurden %s Veranstaltungen gefunden, und in Ihre Merkliste eingefügt"),
            $search_obj->search_result->numRows
        ));
        if (is_array($_SESSION['_marked_sem']) && count($_SESSION['_marked_sem'])){
            $_SESSION['_marked_sem'] = array_merge(
                (array)$_SESSION['_marked_sem'],
                (array)$search_obj->search_result->getDistinctRows("seminar_id")
            );
        } else {
            $_SESSION['_marked_sem'] = $search_obj->search_result->getDistinctRows("seminar_id");
        }
    } else {
        PageLayout::postInfo(_("Es wurden keine Veranstaltungen gefunden, auf die Ihre Suchkriterien zutreffen."));
    }
}

if (Request::option('cmd') === "MarkList"){
    $sem_mark_list = Request::quotedArray('sem_mark_list');
    if ($sem_mark_list){
        if (Request::quoted('mark_list_aktion') == "del"){
            $count_del = 0;
            for ($i = 0; $i < count($sem_mark_list); ++$i){
                if (isset($_SESSION['_marked_sem'][$sem_mark_list[$i]])){
                    ++$count_del;
                    unset($_SESSION['_marked_sem'][$sem_mark_list[$i]]);
                }
            }
            PageLayout::postSuccess(sprintf(
                _("%s Veranstaltung(en) wurde(n) aus Ihrer Merkliste entfernt."),
                $count_del
            ));
        } else {
            $tmp = explode("_",Request::quoted('mark_list_aktion'));
            $item_ids[0] = $tmp[1];
            if ($item_ids[0] === "all"){
                $item_ids = [];
                foreach ($_possible_open_items as $key => $value){
                    if($key !== 'root')
                        $item_ids[] = $key;
                }
            }
            for ($i = 0; $i < count($item_ids); ++$i){
                $count_ins = 0;
                for ($j = 0; $j < count($sem_mark_list); ++$j){
                    if ($sem_mark_list[$j]){
                        $count_ins += StudipSemTree::InsertSemEntry($item_ids[$i], $sem_mark_list[$j]);
                    }
                }
                $_msg .= sprintf(
                    _("%s Veranstaltung(en) in <b>" .htmlReady($the_tree->tree->tree_data[$item_ids[$i]]['name']) . "</b> eingetragen.<br>"),
                    $count_ins
                );
            }
            if ($_msg) {
                PageLayout::postSuccess($_msg);
            }
            $the_tree->tree->init();
        }
    }
}
if ($the_tree->mode === "MoveItem" || $the_tree->mode === "CopyItem"){
    if ($_msg){
        $_msg .= "§";
    }
    if ($the_tree->mode === "MoveItem"){
        $text = _("Der Verschiebemodus ist aktiviert. Bitte wählen Sie ein Einfügesymbol %s aus, um das Element <b>%s</b> an diese Stelle zu verschieben.%s");
    } else {
        $text = _("Der Kopiermodus ist aktiviert. Bitte wählen Sie ein Einfügesymbol %s aus, um das Element <b>%s</b> an diese Stelle zu kopieren.%s");
    }
    PageLayout::postInfo(sprintf(
        $text ,
        Icon::create('arr_2right', 'sort', ['title' => _('Einfügesymbol')])->asImg(),
        htmlReady($the_tree->tree->tree_data[$the_tree->move_item_id]['name']),
        "<div align=\"right\">"
        . LinkButton::createCancel(
                _('Abbrechen'),
                $the_tree->getSelf("cmd=Cancel&item_id=$the_tree->move_item_id"),
                ['title' => _("Verschieben / Kopieren abbrechen")]
        )
        ."</div>"
    ));
}

?>
    <?
    $search_obj->attributes_default = ['style' => ''];
    // $search_obj->search_fields['type']['size'] = 30 ;
    echo $search_obj->getFormStart(URLHelper::getLink($the_tree->getSelf()), ['class' => 'default narrow']);
    ?>
    <fieldset>
        <legend><?= _("Veranstaltungssuche") ?></legend>

        <label class="col-3">
            <?=_("Titel")?>
            <?=$search_obj->getSearchField("title")?>
        </label>

        <label class="col-3">
            <?=_("Untertitel")?>
            <?=$search_obj->getSearchField("sub_title")?>
        </label>

        <label class="col-3">
            <?=_("Nummer")?>
            <?=$search_obj->getSearchField("number")?>
        </label>

        <label class="col-3">
            <?=_("Kommentar")?>
            <?=$search_obj->getSearchField("comment")?>
        </label>

        <label class="col-3">
            <?=_("Lehrende")?>
            <?=$search_obj->getSearchField("lecturer")?>
        </label>

        <label class="col-3">
            <?=_("Bereich")?>
            <?=$search_obj->getSearchField("scope")?>
        </label>

        <label>
            <?=_("Kombination")?>
            <?=$search_obj->getSearchField('combination')?>
        </label>

        <label class="col-3">
            <?=_("Typ")?>
            <?=$search_obj->getSearchField("type", ['class' => 'size-s'])?>
        </label>

        <label class="col-3">
            <?=_("Semester")?>
            <?=$search_obj->getSearchField("sem", ['class' => 'size-s'])?>
        </label>
    </fieldset>

    <footer>
        <?=$search_obj->getSearchButton();?>
        <?=$search_obj->getNewSearchButton();?>
    </footer>

    <?=$search_obj->getFormEnd();?>
<br>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td class="blank" width="75%" align="left" valign="top" colspan="2">
            <? $the_tree->showSemTree(); ?>
        </td>
    </tr>
</table>

<?
// Create Clipboard (use a second output buffer)
ob_start();
?>
    <form action="<?=URLHelper::getLink($the_tree->getSelf("cmd=MarkList"))?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
        <select multiple size="10" name="sem_mark_list[]" style="font-size:8pt;width:100%" class="nested-select">
            <?
            $cols = 50;
            if (is_array($_SESSION['_marked_sem']) && count($_SESSION['_marked_sem'])){
                $view->params[0] = array_keys($_SESSION['_marked_sem']);
                $entries = new DbSnapshot($view->get_query("view:SEMINAR_GET_SEMDATA"));
                $sem_data = $entries->getGroupedResult("seminar_id");
                $sem_number = -1;
                foreach ($sem_data as $seminar_id => $data) {
                    if ((int)key($data['sem_number']) !== $sem_number){
                        if ($sem_number !== -1) {
                            echo '</optgroup>';
                        }
                        $sem_number = key($data['sem_number']);
                        echo "\n<optgroup label=\"" . $the_tree->tree->sem_dates[$sem_number]['name'] . "\">";
                    }
                    $sem_name = key($data["Name"]);
                    $sem_number_end = (int)key($data["sem_number_end"]);
                    if ($sem_number !== $sem_number_end){
                        $sem_name .= " (" . $the_tree->tree->sem_dates[$sem_number]['name'] . " - ";
                        $sem_name .= (($sem_number_end === -1) ? _("unbegrenzt") : $the_tree->tree->sem_dates[$sem_number_end]['name']) . ")";
                    }
                    $line = htmlReady(my_substr($sem_name,0,$cols));
                    $tooltip = $sem_name . " (" . join(",",array_keys($data["doz_name"])) . ")";
                    echo "\n<option value=\"$seminar_id\" " . tooltip($tooltip,false) . ">$line</option>";
                }
                echo '</optgroup>';
            }
            ?>
        </select>
        <select name="mark_list_aktion" style="font-size:8pt;width:100%;margin-top:5px;">
            <?
            if (is_array($_possible_open_items) && count($_possible_open_items) && !(count($_possible_open_items) === 1 && $_possible_open_items['root'])){
                echo "\n<option  value=\"insert_all\">" . _("Markierte in alle geöffneten Bereiche eintragen") . "</option>";
                foreach ($_possible_open_items as $item_id => $value){
                    echo "\n<option value=\"insert_{$item_id}\">"
                   . sprintf(
                       _('Markierte in "%s" eintragen'),
                       htmlReady(my_substr($the_tree->tree->tree_data[$item_id]['name'],0,floor($cols * .8))
                   ))
                    . "</option>";
                }
            }
            ?>
            <option value="del"><?=_("Markierte aus der Merkliste löschen")?></option>
        </select>
        <div align="center">
            <?= Button::create(
                _('OK'),
                [
                    'title' => _("Gewählte Aktion starten"),
                    'style' => 'vertical-align:middle;margin:3px;',
                    'class' => 'accept button'
                ]
            ); ?>
        </div>
    </form> 
<?

// Add Clipboard to Sidebar (get the inner/second output buffer)
$content = ob_get_clean();
$widget = new SidebarWidget();
$widget->setTitle(_('Merkliste'));
$widget->addElement(new WidgetElement($content));
Sidebar::get()->addWidget($widget);

$template = $GLOBALS['template_factory']->open('layouts/base.php');
$template->content_for_layout = ob_get_clean();
echo $template->render();

page_close();
