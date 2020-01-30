<?php

namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Credentials extends \Codeception\Module
{
    public function getCredentialsForTestAutor()
    {
        return [
            'id' => 'e7a0a84b161f3e8c09b4a0a2e8a58147',
            'username' => 'test_autor',
            'password' => 'testing',
        ];
    }

    public function getCredentialsForTestDozent()
    {
        return [
            'id' => '205f3efb7997a0fc9755da2b535038da',
            'username' => 'test_dozent',
            'password' => 'testing',
        ];
    }

    public function getCredentialsForTestAdmin()
    {
        return [
            'id' => '6235c46eb9e962866ebdceece739ace5',
            'username' => 'test_admin',
            'password' => 'testing',
        ];
    }

    public function getCredentialsForRoot()
    {
        return [
            'id' => '76ed43ef286fb55cf9e41beadb484a9f',
            'username' => 'root@studip',
            'password' => 'testing',
        ];
    }
}
