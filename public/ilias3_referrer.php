<?
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ilias3_referrer.php
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

require '../lib/bootstrap.php';

ob_start();
page_open(["sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"]);
$perm->check("autor");
include 'lib/seminar_open.php'; // initialise Stud.IP-Session

if (Config::get()->ELEARNING_INTERFACE_ENABLE) {
    ELearningUtils::bench("start");
    
    $cms_select = Request::quoted('cms_select');
    if (isset($ELEARNING_INTERFACE_MODULES[$cms_select]["name"])) {
        ELearningUtils::loadClass($cms_select);
        // init session now
        $sess_id = $connected_cms[$cms_select]->user->getSessionId();
        $connected_cms[$cms_select]->terminate();
        ob_end_clean();
        if (!$sess_id){
            $message =  _("Login nicht möglich");
            $details = [];
            $details[]  = sprintf(_("Automatischer Login für das System <b>%s</b> (Nutzername:%s) fehlgeschlagen."),
                htmlReady($connected_cms[$cms_select]->getName()),
                $connected_cms[$cms_select]->user->getUsername());
            $details[] = _("Dieser Fehler kann dadurch hervorgerufen werden, dass Sie Ihr Passwort geändert haben. In diesem Fall versuchen Sie bitte Ihren Account erneut zu verknüpfen.");
            $details[] = sprintf(_("%sZurück%s zu Meine Lernmodule"), '<a href="'.URLHelper::getLink("dispatch.php/elearning/my_accounts").'"><b>', '</b></a>');

            PageLayout::postError($message, $details);
            $template = $GLOBALS['template_factory']->open('layouts/base.php');
            $template->content_for_layout = ob_get_clean();
            $template->infobox = $infobox ? ['content' => $infobox] : null;
            echo $template->render();
            page_close();
            die;
        }
        $parameters = "?sess_id=$sess_id";
        $client_id = Request::get('client_id');
        if (!empty($client_id))
            $parameters .= "&client_id=$client_id";
        if (Request::get('target'))
            $parameters .= "&target=".Request::option('target');
        if (Request::get('ref_id'))
            $parameters .= "&ref_id=".Request::option('ref_id');
        if (Request::get('type'))
            $parameters .= "&type=".Request::option('type');

        // refer to studip_referrer.php
        header("Location: ".$ELEARNING_INTERFACE_MODULES[$cms_select]["ABSOLUTE_PATH_ELEARNINGMODULES"] . $ELEARNING_INTERFACE_MODULES[$cms_select]["target_file"] . $parameters);
        page_close();
        die;
    }
}
?>
