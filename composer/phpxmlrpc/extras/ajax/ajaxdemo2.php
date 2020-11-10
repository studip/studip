<?php
/**
 * Demo of a single-php-file webservice + frontend.
 * Uses the phpxmlrpc class and the jsxmlrpc library
 *
 * @author Gaetano Giunta
 * @copyright (c) 2007-2017 G. Giunta
 * @license code licensed under the BSD License: see license.txt
 */

// import required libs
require_once('..\..\xmlrpc\xmlrpc.inc');
require_once('..\..\xmlrpc\xmlrpcs.inc');
require_once('ajaxmlrpc.inc');

// php functions to be exposed as webservices
function sumintegers ($msg)
{
  $v = $msg->getParam(0);
  $n = $v->arraySize();
  $tot = 0;
  for ($i = 0; $i < $n; $i++)
  {
    $val = $v->arrayMem($i);
    $tot = $tot + $val->scalarval();
  }

  return new xmlrpcresp(new xmlrpcval($tot, 'int'));
}

// webservices signature
// NB: do not use dots in method names
$dmap = array(
    'sumintegers' => array(
        'function' => 'sumintegers',
        'signature' => array(array('integer', 'array'))
    )
);

// create server object and set its desired properties
$server = new js_rpc_server($dmap);
$server->jsLibsType = 'jsxmlrpc';
$server->jsLibsPath = '../../javascript';

// here starts the front-end code
?>
<html>
<head>
<?php
// import all webservices from the server into the javascript namespace
$server->importMethods2JS();
?>
</head>
<body>
Click
<a href="#" onclick="alert(sumintegers([10,11,12])); return false;">here</a>
to execute a webservice call and display results in a popup message...
</body>
</html>
