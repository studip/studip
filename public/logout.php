<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
logout.php - Ausloggen aus Stud.IP und aufräumen
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, André Noack <andre.noack@gmx.net>,
Cornelis Kater <ckater@gwdg.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/


require '../lib/bootstrap.php';

page_open(["sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"]);

require_once 'lib/messaging.inc.php';

//nur wenn wir angemeldet sind sollten wir dies tun!
if ($auth->auth["uid"]!="nobody") {
    $my_messaging_settings = $GLOBALS['user']->cfg->MESSAGING_SETTINGS;

    //Wenn Option dafuer gewaehlt, alle ungelsesenen Nachrichten als gelesen speichern
    if ($my_messaging_settings["logout_markreaded"]) {
        Message::markAllAs();
    }

    $logout_user=$user->id;
    $_language = $_SESSION['_language'];

    // TODO this needs to be generalized or removed
    //erweiterung cas
    if ($auth->auth["auth_plugin"] == "cas"){
        $casauth = StudipAuthAbstract::GetInstance('cas');
        $docaslogout = true;
    }
    //Logout aus dem Sessionmanagement
    $auth->logout();
    $sess->delete();

    page_close();

    //Session changed zuruecksetzen
    $timeout=(time()-(15 * 60));
    $user->set_last_action($timeout);

    //der logout() Aufruf fuer CAS (dadurch wird das Cookie (Ticket) im Browser zerstoert)
    if ($docaslogout){
        $casauth->logout();
    }
} else {
    $sess->delete();
    page_close();
}

header("Location:" . URLHelper::getURL("index.php?logout=true&set_language=$_language"));

?>
