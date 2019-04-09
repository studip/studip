#!/usr/bin/env php
<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * client.php - PHP client implementation.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

# set include path
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . '../..');

require_once 'vendor/soap/nusoap.php';

define('WSDL_URL', "http://pomona/studip/mlunzena/trunk/webservices/soap.php?wsdl");

$client = new soap_client(WSDL_URL, TRUE);
$proxy = $client->getProxy();

# studip unstable is quite slow..
$proxy->response_timeout = 300;

echo "Creating Karl May.\n";
$user = $proxy->create_user('secret',
  ['user_name'  => 'kmay', 
        'first_name' => 'Karl', 
        'last_name'  => 'May', 
        'email'      => 'marcus.lunzenauer@uos.de', 
        'permission' => 'user']);
printf("  success: %s\n", var_export($user, TRUE));


echo "Retrieving Karl May.\n";
$user = $proxy->find_user_by_user_name('secret', 'kmay');
printf("  success: %s\n", var_export($user, TRUE));


echo "Updating Karl May.\n";
$user['email'] = "mlunzena@uos.com";
$result = $proxy->update_user('secret', $user);
printf("  success: %s\n", var_export($result, TRUE));


echo "Retrieving Karl May.\n";
$user = $proxy->find_user_by_user_name('secret', 'kmay');
printf("  success: %s\n", var_export($user, TRUE));


echo "Deleting Karl May.\n";
$result = $proxy->delete_user('secret', 'kmay');
printf("  success: %s\n", var_export($result, TRUE));

# var_export($proxy);
