<?php 
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

require_once 'webservice_client.php';
require_once 'vendor/nusoap/nusoap.php';

class Soap_WebserviceClient extends WebserviceClient
{
    public function __construct($webservice_url)
    {
        $this->client = new soap_client($webservice_url);
        $this->client->response_timeout = 7600;
    }

    public function &call($method_name, &$args)
    {
        return $this->client->call($method_name, $args);
    }
}

