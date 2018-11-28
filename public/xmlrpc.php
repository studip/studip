<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * xmlrpc.php - XML-RPC Backend for Stud.IP web services
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require '../lib/bootstrap.php';
require '../lib/webservices/webservices_bootstrap.php';

# create server
$dispatcher = new Studip_Ws_XmlrpcDispatcher($AVAILABLE_SERVICES);

$server = new documenting_xmlrpc_server($dispatcher->get_dispatch_map(), 0);
$server->debug = false;

# start server
$server->service();
