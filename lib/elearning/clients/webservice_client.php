<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

class WebserviceClient
{
    public static function instance($webservice_url, $classname)
    {
        static $instances = [];

        if (!$instances[$classname . $webservice_url]) {
            $instances[$classname . $webservice_url] = new $classname($webservice_url);
        }

        return $instances[$classname . $webservice_url];
    }

    public function __construct()
    {
        trigger_error("this class can't be instantiated");
    }

    public function &call($method_name, &$args)
    {
        trigger_error("WebserviceCaller::WebserviceCaller::  call not defined");
    }
}
