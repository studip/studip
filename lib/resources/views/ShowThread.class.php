<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
/**
* ShowThread.class.php
*
* creates a threaded view
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      resources
* @module       ShowThread.class.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ShowThread.class.php
// erzeugt einen threaded-view
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

use Studip\Button,
    Studip\LinkButton;

require_once 'lib/resources/views/ShowTreeRow.class.php';


/*****************************************************************************
ShowThread, stellt Struktur mit Hilfe von printThread dar
/*****************************************************************************/

class ShowThread extends ShowTreeRow {
    var $lines;     //Uebersichtsarray der Struktur;

    function __construct() {
    }

    function showThreadLevel ($root_id, $level=0, $lines='')
    {
        global $edit_structure_object, $ActualObjectPerms;

        // Prepare statement that obtains all children of a given resource
        $query = "SELECT resource_id
                  FROM resources_objects
                  WHERE parent_id = ?
                  ORDER BY name";
        $children_statement = DBManager::get()->prepare($query);

        //Daten des Objects holen
        $query = "SELECT resource_id
                  FROM resources_objects
                  WHERE resource_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$root_id]);
        $resource_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        foreach ($resource_ids as $resource_id) {
            //Untergeordnete Objekte laden
            $children_statement->execute([$resource_id]);
            $children = $children_statement->fetchAll(PDO::FETCH_COLUMN);
            $children_statement->closeCursor();

            //Struktur merken
            $weitere = count($children);
            $this->lines[$level + 1] = $weitere;

            //Object erstellen
            $resObject = ResourceObject::Factory($resource_id);

            //Daten vorbereiten
            if (!$resObject->getCategoryIconnr())
                $icon = Icon::create('folder-full', 'inactive')->asImg(['class' => 'text-top']);
            else
                $icon = Assets::img('cont_res' . $resObject->getCategoryIconnr() . '.gif');

            if ($_SESSION['resources_data']["move_object"]) {
                $temp  = "&nbsp;<a href=\"".URLHelper::getLink('?target_object='.$resObject->id)."#a\">";
                $temp .= Icon::create('arr_2right', 'sort', ['title' => _('Objekt in diese Ebene verschieben')])->asImg();
                $temp .= "</a>";
                $icon = $temp . $icon;
            }

            if ($_SESSION['resources_data']["structure_opens"][$resObject->id]) {
                $link = URLHelper::getLink('?structure_close=' . $resObject->id . '#a');
                $open = 'open';
                if ($_SESSION['resources_data']["actual_object"] == $resObject->id)
                    echo '<a name="a"></a>';
            } else {
                $link = URLHelper::getLink('?structure_open=' . $resObject->id . '#a');
                $open = 'close';
            }

            if ($resObject->getCategoryName())
                $titel=$resObject->getCategoryName().": ";
            if ($edit_structure_object==$resObject->id) {
                echo "<a name=\"a\"></a>";
                $titel.="<input style=\"font-size: 8pt; width: 100%;\" type=\"text\" size=20 maxlength=255 name=\"change_name\" value=\"".htmlReady($resObject->getName())."\">";
            } else {
                $titel.=htmlReady($resObject->getName());
            }

            //create a link on the titel, too
            if (($link) && ($edit_structure_object != $resObject->id))
                $titel = "<a href=\"$link\" class=\"tree\" >$titel</a>";

            if ($resObject->getOwnerLink()) {
                $zusatz=sprintf (_("verantwortlich:") . " <a href=\"%s\"><font color=\"#333399\">%s</font></a>", $resObject->getOwnerLink(), htmlReady($resObject->getOwnerName()));
            } else {
                $zusatz=sprintf (_("verantwortlich:") . " %s", htmlReady($resObject->getOwnerName()));
            }

            $new = true;

            $edit .= '<div style="text-align: center"><div class="button-group">';

            if ($open === 'open') {
                //load the perms
                if (($ActualObjectPerms) && ($ActualObjectPerms->getId() == $resObject->getId())) {
                    $perms = $ActualObjectPerms->getUserPerm();
                } else {
                    $ThisObjectPerms = ResourceObjectPerms::Factory($resObject->getId());
                    $perms = $ThisObjectPerms->getUserPerm();
                }

                if ($edit_structure_object==$resObject->id) {
                    $content.= "<br><textarea name=\"change_description\" rows=3 cols=40>".htmlReady($resObject->getDescription())."</textarea><br>";
                    $content .= Button::create(_('Übernehmen'), 'send', ['value' => _('Änderungen speichern')]);
                    $content .= LinkButton::createCancel(_('Abbrechen'), URLHelper::getURL('?cancel_edit=' . $resObject->id));
                    $content.= "<input type=\"hidden\" name=\"change_structure_object\" value=\"".$resObject->getId()."\">";
                    $open="open";
                } else {
                    $content=formatReady($resObject->getDescription());
                }
                if ($_SESSION['resources_data']["move_object"] == $resObject->id) {
                    $content .= '<br>';
                    $content .= sprintf(_('Dieses Objekt wurde zum Verschieben markiert. '
                                         .'Bitte wählen Sie das Einfügen-Symbol %s, um es in die gewünschte Ebene zu verschieben.'),
                                        Icon::create('arr_2right', 'sort', ['title' => _('Klicken Sie auf dieses Symbol, um dieses Objekt in eine andere Ebene zu verschieben')])->asImg(16));
                }

                if ($resObject->getCategoryId()) {
                    $edit .= LinkButton::create(_('Belegung'), URLHelper::getURL('?view=view_schedule&show_object=' . $resObject->id));
                }
                $edit .= LinkButton::create(_('Eigenschaften'), URLHelper::getURL('?view=view_details&show_object=' . $resObject->id));


                if ($perms === 'admin') {
                    if ($resObject->isRoom()) {
                        $edit .= LinkButton::create(_('Benachrichtigung'), UrlHelper::getScriptURL('dispatch.php/resources/helpers/resource_message/' . $resObject->id), ['data-dialog' => '']);
                    }
                    $edit .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                    $edit .= LinkButton::create(_('Neues Objekt'), URLHelper::getURL('?create_object=' . $resObject->id));
                    $edit .= LinkButton::create(_('Neue Ebene'), URLHelper::getURL('?create_hierachie_level=' . $resObject->id));
                }

                $edit.= "&nbsp;&nbsp;&nbsp;&nbsp;";

                if ($weitere) {
                    $edit .= LinkButton::create(_('Liste öffnen'), URLHelper::getURL('?open_list=' . $resObject->id));
                }

                if ($_SESSION['resources_data']["move_object"] == $resObject->id) {
                    $edit .= LinkButton::createCancel(_('Abbrechen'), URLHelper::getURL('?cancel_move=TRUE'));
                } else if ($perms === 'admin') {
                    $edit .= LinkButton::create(_('Verschieben'), URLHelper::getURL('?pre_move_object=' . $resObject->id));
                }

                if ($perms === 'admin' && $resObject->isDeletable()) {
                    $edit .= LinkButton::create(_('Löschen'), '?kill_object=' . $resObject->id);
                }
            }

            $edit .= '</div></div>';

            //Daten an Ausgabemodul senden (aus resourcesVisual)
            $this->showRow($icon, $link, $titel, $zusatz, $level, $lines, $weitere, $new, $open, $content, $edit);

            //in weitere Ebene abtauchen &nbsp;
            foreach ($children as $child_id) {
                if ($_SESSION['resources_data']['structure_opens'][$resource_id])
                    $this->showThreadLevel($child_id, $level + 1, $lines);
            }
        }
    }
}
