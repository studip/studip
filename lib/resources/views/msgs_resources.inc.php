<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* msgs_resources.inc.php
*
* library for the messages (error, info and other)
*
*
* @author       Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup  resources
* @module       msgs_resources.inc.php
* @package      resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// msgs_resources.inc.php
// Alle Meldungen, die in der Ressourcenverwaltung ausgegeben werden
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

$this->msg[1] =  [
        "mode" => "error",
        "titel" => _("Fehlende Berechtigung"),
        "msg"=> _("Sie haben leider keine Berechtigung, das Objekt zu bearbeiten!")];
$this->msg[2] =  [
        "mode" => "error",
        "titel" => _("NutzerIn hat keine Berechtigung") ,
        "msg"=> _("Sie versuchen, eineN NutzerIn einzutragen, der/die nicht selbst in der Lage ist, die Belegung zu bearbeiten oder zu löschen. Sie können dieseN NutzerIn leider nicht eintragen")];
$this->msg[3] =  [
        "mode" => 'success',
        "titel" => _("Belegung eingetragen"),
        "msg"=> _("Die Belegung wurde eingetragen")];
$this->msg[4] =  [
        "mode" => 'success',
        "titel" => _("Belegung verändert"),
        "msg"=> _("Die Belegung wurde verändert")];
$this->msg[5] =  [
        "mode" => 'success',
        "titel" => _("Belegung gelöscht"),
        "msg"=> _("Die Belegung wurde gelöscht")];
$this->msg[6] =  [
        "mode" => 'success',
        "titel" => _("Eigenschaften verändert"),
        "msg"=> _("Die Eigenschaften der Ressource wurden verändert")];
$this->msg[7] =  [
        "mode" => 'success',
        "titel" => _("Ressource gelöscht"),
        "msg"=> _("Die Ressource wurde gelöscht")];
$this->msg[8] =  [
        "mode" => 'success',
        "titel" => _("Berechtigungen verändert"),
        "msg"=> _("Die Berechtigungseinstellungen der Ressource wurden verändert")];
$this->msg[9] =  [
        "mode" => 'success',
        "titel" => _("Ressource verschoben"),
        "msg"=> _("Die Ressource wurde verschoben")];
$this->msg[10] =  [
        "mode" => "error",
        "msg"=> _("Bitte geben Sie eineN NutzerIn für die Belegung an, um diese Belegung zu speichern!")];
$this->msg[11] =  [
        "mode" => "error",
        "msg"=> _("Die Belegung konnte nicht gespeichert werden, da Sie sich mit einer anderen Belegung überschneidet!")];
$this->msg[12] =  [
        "mode" => "info",
        "msg"=> _("Es existieren keine Ressourcen oder Ebenen, auf die Sie Zugriff haben.")."<br>"._("Um Ressourcen anzulegen, erzeugen Sie zunächst eine neue Ebene, indem Sie &raquo;Neue Hierarchie erzeugen&laquo; anwählen.")." <br>"._("Anschließend können auf dieser Ebene Ressourcen anlegen")];
$this->msg[13] =  [
        "mode" => "info",
        "msg"=> _("Es existieren keine Ressourcen, die Sie im Rahmen dieser Veranstaltung belegen dürfen.")];
$this->msg[14] =  [
        "mode" => "info",
        "msg"=> _("Sie haben keine Ebene ausgewählt. Daher kann keine Liste erzeugt werden.")." <br>"._("Benutzen Sie die Suchfunktion oder wählen Sie unter &raquo;Übersicht&laquo; eine Ebene bzw. Ressource in der Hierarchie aus.")];
$this->msg[15] =  [
        "mode" => "info",
        "msg"=> _("Sie haben kein Objekt zum Bearbeiten ausgewählt.")." <br>"._("Bitte wählen Sie zunächst ein Objekt aus.")];
$this->msg[16] =  [
        "mode" => "info",
        "msg"=> _("Sie haben kein Objekt zum Anzeigen ausgewählt.")." <br>"._("Bitte wählen Sie zunächst ein Objekt aus.")];
$this->msg[17] =  [
        "mode" => "error",
        "msg"=> _("Bitte geben Sie gültige Werte für Datum, Beginn und Ende der Belegung an!")];
$this->msg[18] =  [
        "mode" => "error",
        "msg"=> _("Bitte geben Sie einen gültigen Wert für das Ende der Wiederholung an!")];
$this->msg[19] =  [
        "mode" => "error",
        "msg"=> _("Das Ende der Wiederholung darf nicht vor dem ersten Termin der Wiederholung liegen!")];
$this->msg[20] =  [
        "mode" => "error",
        "msg"=> _("Die Endzeit darf nicht vor der Startzeit liegen!")];
$this->msg[21] =  [
        "mode" => "error",
        "msg"=> _("Die jährliche Wiederholung darf maximal 10 Jahre dauern!")];
$this->msg[22] =  [
        "mode" => "error",
        "msg"=> _("Die monatliche Wiederholung darf maximal 10 Jahre dauern!")];
$this->msg[23] =  [
        "mode" => "error",
        "msg"=> _("Die wöchentliche Wiederholung darf maximal 500 Mal wiederholt stattfinden!")];
$this->msg[24] =  [
        "mode" => "error",
        "msg"=> _("Die tägliche Wiederholung darf maximal 1000 Mal wiederholt stattfinden!")];
$this->msg[25] =  [
        "mode" => "error",
        "titel" => _("Fehlende Berechtigung"),
        "msg"=> _("Sie haben leider keine Berechtigung, diese Funktion zu benutzen!")];
$this->msg[26] =  [
        "mode" => "error",
        "msg"=> _("Fehler in den Sperrzeiten, bitte korrigieren Sie die Zeiten!")];
$this->msg[27] =  [
        "mode" => 'success',
        "msg"=> _("Die Sperrzeiten wurden geändert.")];
$this->msg[28] =  [
        "mode" => 'success',
        "msg"=> _("Die Sperrzeit wurde gelöscht. Falls die Blockierung aktiviert war, und der gelöschte Zeitraum bereits läuft, können berechtigte Nutzer wieder Belegungen erstellen.")];
$this->msg[29] =  [
        "mode" => "info",
        "msg"=> _("Sie haben den Raum als <u>blockierbar</u> markiert. Wollen Sie auch alle untergeordneten Räume ebenfalls als <u>blockierbar</u> markieren?")
            . "<br>". LinkButton::createAccept(_('JA!'), '%s')
            . ' ' . LinkButton::createCancel(_('NEIN!'), '%s')];
$this->msg[30] =  [
        "mode" => "info",
        "msg"=> _("Sie haben den Raum als <u>nicht</u> mehr blockierbar markiert. Wollen Sie auch alle untergeordneten Räume ebenfalls als <u>nicht</u> blockierbar markieren?")
            . "<br>". LinkButton::createAccept(_('JA!'), '%s')
            . ' ' . LinkButton::createCancel(_('NEIN!'), '%s')];
$this->msg[31] =  [
        "mode" => "info",
        "msg"=> "<font size=\"-1\">"._("<b>Ressourcenblockierung vom %s bis zum %s.</b>")."</font><br>".
            _("Sie versuchen, ein Objekt zu bearbeiten, das zur Zeit für eine Bearbeitung gesperrt ist. Nur der globale Ressourcenadministrator hat Zugriff auf dieses Objekt! <br>(Wenn Sie normalerweise Zugriff auf dieses Objekt haben, wird Ihnen der Zugriff nach Aufhebung der Blockierung wieder gewährt.)")];
$this->msg[32] =  [
        "mode" => 'success',
        "msg"=> _("Die ausgewählten Einträge wurden in die aktuelle Anfrage übernommen.")];
$this->msg[33] =  [
        "mode" => 'success',
        "msg"=> _("Folgende Räume wurden gebucht und der Veranstaltung &raquo;%s&laquo; zugewiesen: <font size=\"-1\" color=\"black\">%s</font>")];
$this->msg[34] =  [
        "mode" => "error",
        "msg"=> _("Folgende Räume konnten wegen Überschneidungen nicht gebucht werden: <font size=\"-1\" color=\"black\">%s</font>")];
$this->msg[35] =  [
        "mode" => "error",
        "msg"=> _("Die folgende Räume konnten wegen Überschneidungen nicht gebucht werden:")."<br>"._("Eine neue Anfrage, die einzeln bearbeitet werden muß, wurde für jede Belegungszeit erstellt.  <font size=\"-1\" color=\"black\">%s</font>")];
// message 36 is not used anymore
$this->msg[37] =  [
        "mode" => 'success',
        "msg"=> _("Die regelmäßige Belegung wurde in Einzeltermine umgewandelt.")];
$this->msg[38] =  [
        "mode" => 'success',
        "msg"=> _("Belegung wurde in die Ressource &raquo;%s&laquo; verschoben.")];
$this->msg[39] =  [
        "mode" => "error",
        "msg"=> _("Die Belegung konnte nicht verschoben werden, da Sie sich in der gewünschten Ressource mit einer anderen Belegung überschneidet!")];
$this->msg[40] =  [
        "mode" => "info",
        "msg"=> _("Sie haben bereits Anfragen, die Sie ausgewählt haben, bearbeitet. Klicken Sie auf &raquo;absenden&laquo;, um Nachrichten zu aufgelösten Anfragen versenden.")
            . " ". LinkButton::createAccept(_('Absenden'), '%s')];
$this->msg[41] =  [
        "mode" => "info",
        "msg"=> _("Mit den von ihnen ausgewählten Einstellungen sind keine Anfragen, die Sie auflösen könnten, vorhanden.")];
$this->msg[42] =  [
        "mode" => "info",
        "msg"=> _("Folgenden Einzelterminen wurde kein Raum zugewiesen, da gesonderte Anfragen zu diesem Termine vorliegen, die einzeln bearbeitet werden müssen:")."<font size=\"-1\" color=\"black\">%s</font>"];
$this->msg[43] =  [
        "mode" => "info",
        "msg"=> "<form action=\"%s\" method=\"post\">"
            . CSRFProtection::tokenTag()
            . "<table class=\"default\"><tr><td style=\"vertical-align:top\">"
            . _("Wollen Sie die Anfrage wirklich ablehnen?")
            ."<br><br>" . Button::createAccept('JA!')
            . " ". LinkButton::createCancel(_('NEIN!'), '%s') .'<br>'
            .'<input type="hidden" name="decline_request" value="1"></td><td style="vertical-align:top">'
            ._("Grund der Ablehnung:").'<br>'
            .'<textarea cols="30" rows="3" name="decline_message" style="width:90%%"></textarea></td></tr></table>'
            .'</form>'];
$this->msg[44] =  [
        "mode" => "error",
        "msg"=> _("Die Belegungen konnte nicht gespeichert werden, da sie mit folgenden Sperrzeiten kollidiert:")
            ."<br><font size=\"-1\" color=\"black\">%s</font>"];
$this->msg[45] =  [
        "mode" => 'success',
        "msg"=> _("Es wurden %s Raumanfragen gelöscht.")];
$this->msg[46] =  [
    'mode' => 'error',
    'msg'  => _("Sie müssen eineN NutzerIn eintragen oder eine freie Eingabe zur Belegung eintragen, um diese Belegung speichern zu können!")
];
$this->msg[47] =  [
        "mode" => "success",
        "msg"=> _("Belegung wurde in die Ressource &raquo;%s&laquo; kopiert.")];
$this->msg[48] =  [
        "mode" => "error",
        "msg"=> _("Die Belegung konnte nicht in die Ressource &raquo;%s&laquo; kopiert werden, da sie sich mit einer anderen Belegung überschneidet:") . "<br><font size=\"-1\" color=\"black\">%s</font>"];
$this->msg[49] = [
    'mode' => 'info',
    'msg'  => _('Sie können den Anfragenplan nur anzeigen, wenn Sie Anfragen für einen bestimmten Raum auflösen.')
];

$this->msg[50] = [
    'mode' => 'success',
    'msg'  => _('Der Kommentar für diese Belegung wurde gespeichert')
];
