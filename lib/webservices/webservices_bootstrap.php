<?php
/* requires everything necessary for webservices
 *
 * Copyright (c) 2011  Stud.IP CoreGroup
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

# requiring nusoap
require_once 'vendor/nusoap/nusoap.php';
require_once 'vendor/nusoap/class.delegating_soap_server.php';
require_once 'vendor/nusoap/class.soap_server_delegate.php';


# requiring soap_server_delegate
require_once 'vendor/studip_ws/studip_ws.php';
require_once 'vendor/studip_ws/soap_dispatcher.php';

# requiring phpxmlrpc
require_once 'vendor/phpxmlrpc/xmlrpc.inc';
require_once 'vendor/phpxmlrpc/xmlrpcs.inc';
require_once 'vendor/phpxmlrpc/docxmlrpcs.inc';
require_once 'vendor/phpxmlrpc/jsonrpc.inc';
require_once 'vendor/phpxmlrpc/jsonrpcs.inc';

# requiring xmlrpc_dispatcher
require_once 'vendor/studip_ws/studip_ws.php';
require_once 'vendor/studip_ws/xmlrpc_dispatcher.php';

# requiring jsonrpc_dispatcher
require_once 'vendor/studip_ws/studip_ws.php';
require_once 'vendor/studip_ws/jsonrpc_dispatcher.php';

# requiring all the webservices
require_once 'lib/webservices/services/access_controlled_webservice.php';
require_once 'lib/webservices/services/user_webservice.php';
require_once 'lib/webservices/services/session_webservice.php';
require_once 'lib/webservices/services/contentmodule_webservice.php';
require_once 'lib/webservices/services/seminar_webservice.php';
require_once 'lib/webservices/services/lecture_tree_webservice.php';
require_once 'lib/webservices/services/institute_webservice.php';

// set up dummy user environment (but no session)
$user = new Seminar_User('nobody');
$auth = new Seminar_Default_Auth();
$perm = new Seminar_Perm();

$AVAILABLE_SERVICES = ['UserService', 'SessionService', 'SeminarService', 'ContentmoduleService', 'LectureTreeService', 'InstituteService'];

$AVAILABLE_SERVICES =
    array_merge($AVAILABLE_SERVICES,
                array_flatten(PluginEngine::sendMessage("WebServicePlugin",
                                                        "getWebServices")));

if (!Config::get()->WEBSERVICES_ENABLE)
{
    throw new Exception("Webservices not available");
}
