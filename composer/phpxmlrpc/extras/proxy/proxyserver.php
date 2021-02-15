<?php
/**
 * Very simple xmlrpc proxy server. Forwards all requests to sf.net server
 *
 * @author Gaetano Giunta
 * @copyright (c) 2006-2017 G. Giunta
 * @license code licensed under the BSD License: see license.txt
 */

include('xmlrpc.inc');
include('xmlrpcs.inc');
include('proxyxmlrpcs.inc');

$server = new proxy_xmlrpc_server(new xmlrpc_client('http://phpxmlrpc.sourceforge.net/server.php'), false);
$server->setDebug(2);
$server->service();
