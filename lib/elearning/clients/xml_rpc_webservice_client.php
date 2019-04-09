<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

require_once("webservice_client.php");
require_once("vendor/phpxmlrpc/xmlrpc.inc");

class XML_RPC_WebserviceClient extends WebserviceClient
{
    public function __construct($webservice_url)
    {
        $this->client = new xmlrpc_client($webservice_url);
        #$this->client->verifyhost = true;
        $this->client->debug = false;
        $this->client->verifypeer = false;
        $this->client->response_timeout = 7600;
        $this->client->return_type = 'phpvals';

    }

    public function &call($method_name, &$args)
    {
        $xmlrpc_args = [];
        foreach ($args as $arg)
        {
                $xmlrpc_args[] = php_xmlrpc_encode($arg);
        }

        $xmlrpc_return = $this->client->send(new xmlrpcmsg($method_name, $xmlrpc_args), 300);
        return $xmlrpc_return->value();
    }
}

