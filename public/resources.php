<?
# Lifter002: TODO
# Lifter010: TODO
/**
 * resources.php - The startscript for the resources module
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Cornelis Kater <ckater@gwdg.de>
 * @author      Suchi & Berg GmbH <info@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
*/


require '../lib/bootstrap.php';

page_open(["sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"]);
$auth->login_if($auth->auth['uid'] == 'nobody');

$perm->check("autor");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session
PageLayout::setHelpKeyword("Basis.Ressourcen");// META:in resourcesControl.inc.php verlagern,wenn detaillierter vorhanden

if (Config::get()->RESOURCES_ENABLE) {
    //Steuerung der Ressourcenverwaltung einbinden
    include "lib/resources/resourcesControl.inc.php";
} else {
    ob_start();
    PageLayout::postError(_("Die Ressourcenverwaltung ist nicht eingebunden. Bitte aktivieren Sie sie in den Systemeinstellungen, oder wenden Sie sich an die Systemadministratoren."), [_("Ressourcenverwaltung nicht eingebunden")]);
    
    $template = $GLOBALS['template_factory']->open('layouts/base.php');
    $template->content_for_layout = ob_get_clean();
    $template->infobox = $infobox ? ['content' => $infobox] : null;
    echo $template->render();
}
