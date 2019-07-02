<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * soap.php - SOAP Backend for Stud.IP web services
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

$delegate = new Studip_Ws_SoapDispatcher($AVAILABLE_SERVICES);
$server   = new DelegatingSoapServer($delegate);

# use UTF-8 encoding
$server->soap_defencoding = 'UTF-8';
$server->decode_utf8 = false;

# creating WSDL
$namespace = 'urn:studip_wsd';
$server->configureWSDL('Stud.IP Webservice', $namespace);
$server->wsdl->schemaTargetNamespace = $namespace;

# register operations
$delegate->register_operations($server);

# start server
$fp = fopen('php://input', 'rb');
stream_filter_append($fp, 'dechunk', STREAM_FILTER_READ);
$server->service(stream_get_contents($fp));

