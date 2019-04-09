<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_elearning_interface.php
//
// Copyright (c) 2005 Arne Schroeder <schroeder@data-quest.de>
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

page_open(["sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"]);
$perm->check("root");

include 'lib/seminar_open.php'; // initialise Stud.IP-Session
// -- here you have to put initialisations for the current page

PageLayout::setHelpKeyword("Basis.Ilias");
PageLayout::setTitle(_("Verwaltung der Lernmodul-Schnittstelle"));
Navigation::activateItem('/admin/config/elearning');

ob_start();

$cms_select = Request::get('cms_select');

if (Config::get()->ELEARNING_INTERFACE_ENABLE)
{

    if ($cms_select != "" && isset($ELEARNING_INTERFACE_MODULES[$cms_select]))
    {
        $connected_cms[$cms_select] = new ConnectedCMS();
        $connection_status = $connected_cms[$cms_select]->getConnectionStatus($cms_select);
        if (Request::submitted('activate'))
        {
            ELearningUtils::setConfigValue("ACTIVE", "1", $cms_select);
        }
        if (Request::submitted('deactivate'))
        {
            ELearningUtils::setConfigValue("ACTIVE", "0", $cms_select);
        }
        if (!count(array_column($connection_status, 'error')))
        {
            require_once ("lib/elearning/" . $ELEARNING_INTERFACE_MODULES[$cms_select]["CLASS_PREFIX"] . "ConnectedCMS.class.php");
            $classname = $ELEARNING_INTERFACE_MODULES[$cms_select]["CLASS_PREFIX"] . "ConnectedCMS";
            $connected_cms[$cms_select] = new $classname($cms_select);
            $connected_cms[$cms_select]->initSubclasses();
        }
    } else {
        unset($cms_select);
    }

    if ($messages["error"] != "")
    {
        PageLayout::postError($messages["error"]);
    }
    if ($messages["info"] != "")
    {
       PageLayout::postInfo($messages["info"]);
    }

    if ($cms_select == "")
        echo ELearningUtils::getCMSSelectbox(_("Bitte wählen Sie ein angebundenes System für die Schnittstelle: "), false) . "\n\n<br><br>";
    else
        echo ELearningUtils::getCMSSelectbox(_("Bitte wählen Sie ein angebundenes System für die Schnittstelle: "), false) . "\n\n<br><br>";

    if ($cms_select != "")
    {
        echo "<table>";
        $error_count = 0;
        foreach ($connection_status as $type => $msg)
        {
            if ($msg["error"] != "")
            {
                echo "<tr><td valign=\"middle\">" . Icon::create('decline', 'attention')->asImg(['class' => 'text-top', 'title' => _('Fehler')]) . $msg["error"] . "</td></tr>";
                $error_count++;
            }
            else
                echo "<tr><td valign=\"middle\">" . Icon::create('accept', 'accept')->asImg(['class' => 'text-top', 'title' => _('OK')]) . $msg["info"] . "</td></tr>";
        }
        echo "<tr><td><br></td></tr>";
        if ($error_count > 0)
        {
            $status_info = "error";
            echo "<tr><td valign=\"middle\">" . Icon::create('decline', 'attention')->asImg(['class' => 'text-top', 'title' => _('Fehler')]) . "<b>";
            echo _("Beim Laden der Schnittstelle sind Fehler aufgetreten. ");
            if (ELearningUtils::isCMSActive($cms_select))
            {
                ELearningUtils::setConfigValue("ACTIVE", "0", $cms_select);
                echo _("Die Schnittstelle wurde automatisch deaktiviert!");
            }
            echo "</b></td></tr>";
        }
        else
            echo "<tr><td valign=\"middle\">" . Icon::create('accept', 'accept', ['title' =>  _('OK')])->asImg(['class' => 'text-top']) . "<b>" .sprintf( _("Die Schnittstelle zum %s-System ist korrekt konfiguriert."), $connected_cms[$cms_select]->getName()) . "</b></td></tr>";
        echo "</table>";
        echo "<br>\n";
        echo ELearningUtils::getCMSHeader($connected_cms[$cms_select]->getName());
        echo "<form method=\"POST\" action=\"" . URLHelper::getLink() . "\" class=\"default\">\n";
        echo CSRFProtection::tokenTag();
        echo '<fieldset>';
        if (ELearningUtils::isCMSActive($cms_select))
        {
            $status_info = "active";
            echo ELearningUtils::getHeader(_("Status"));
            echo "<br>\n";
            echo _("Die Schnittstelle ist <b>aktiv</b>.");
            echo "<br><br>\n";
            echo _("Hier können Sie die Schnittstelle deaktivieren.");
            echo "<br><br>\n";
            echo Button::create(_('Deaktivieren'), 'deactivate');
        }
        else
        {
            echo ELearningUtils::getHeader(_("Status"));
            echo "<br>\n";
            echo _("Die Schnittstelle ist nicht aktiv.");
            echo "<br><br>\n";
            if ($error_count == 0)
            {
                $status_info = "not active";
                echo _("Hier können Sie die Schnittstelle aktivieren.");
                echo "<br><br>\n";
                echo Button::create(_('Aktivieren'), 'activate');
            }
        }
        echo '</fieldset>';
        echo "<input type=\"HIDDEN\" name=\"cms_select\" value=\"" . $cms_select . "\">\n";
        echo "</form>";
        echo "<br>\n";

        echo "<form method=\"POST\" action=\"" . URLHelper::getURL() . "\" class=\"default\">\n";
        echo CSRFProtection::tokenTag();
        echo '<fieldset>';
        if ($error_count == 0)
        {
            echo ELearningUtils::getHeader(_("Einstellungen"));
            echo "<br>\n";
            $connected_cms[$cms_select]->getPreferences();
        }
        echo '</fieldset>';
        echo "<input type=\"hidden\" name=\"cms_select\" value=\"" . $cms_select . "\">\n";
        echo "</form>";

        echo ELearningUtils::getCMSFooter($connected_cms[$cms_select]->getLogo());
    }

    Helpbar::Get()->addPlainText(_('Information'), _('Hier können Sie angebundene Systeme verwalten.'), Icon::create('info'));
    Helpbar::Get()->addPlainText(_('Aktionen'), _('Nachdem Sie ein angebundenes System ausgewählt haben wird die Verbindung zum System geprüft.'), Icon::create('info'));
    // Anzeige, wenn noch keine Account-Zuordnung besteht

        switch($status_info)
        {
            case "active":
                PageLayout::postSuccess(sprintf(_("Die Verbindung zum System \"%s\" ist <b>aktiv</b>. Sie können die Einbindung des Systems in Stud.IP jederzeit deaktivieren."), $connected_cms[$cms_select]->getName()));
            break;
            case "not active":
                PageLayout::postWarning(sprintf(_("Die Verbindung zum System \"%s\" steht, das System ist jedoch nicht aktiviert. Sie können die Einbindung des Systems in Stud.IP jederzeit aktivieren. Solange die Verbindung nicht aktiviert wurde, werden die Module des Systems \"%s\" in Stud.IP nicht angezeigt."), $connected_cms[$cms_select]->getName(), $connected_cms[$cms_select]->getName()));
            break;
            case "error":
                PageLayout::postError(sprintf(_("Bei der Prüfung der Verbindung sind Fehler aufgetreten. Sie müssen zunächst die Einträge in der Konfigurationsdatei korrigieren, bevor das System angebunden werden kann."), $connected_cms[$cms_select]->getName()));
            break;
        }

// terminate objects
    if (is_array($connected_cms))
        foreach($connected_cms as $system)
            $system->terminate();

}
else
{
    PageLayout::postError(_("Die Schnittstelle für die Integration von Lernmodulen ist nicht aktiviert.
    Damit Lernmodule verwendet werden können, muss die Verbindung zu einem LCM-System in der Konfigurationsdatei von Stud.IP hergestellt werden.
    Wenden Sie sich bitte an den/die AdministratorIn."), [_("E-Learning-Schnittstelle nicht eingebunden")]);

}


$template = $GLOBALS['template_factory']->open('layouts/base.php');
$template->content_for_layout = ob_get_clean();
echo $template->render();

page_close();
