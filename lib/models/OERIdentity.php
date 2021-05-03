<?php

abstract class OERIdentity extends SimpleORMap
{
    /**
     * configures this class
     * @param array $config
     */
    protected static function configure($config = [])
    {
        $config['registered_callbacks']['before_store'][] = "cbCreateKeysIfNecessary";
        parent::configure($config);
    }

    public function createSignature($text)
    {
        $rsa = new \phpseclib\Crypt\RSA();
        $rsa->loadKey($this['private_key']);
        return $rsa->sign($text);
    }

    public function verifySignature($text, $signature)
    {
        $rsa = new \phpseclib\Crypt\RSA();
        $rsa->loadKey($this['public_key']);
        return $rsa->verify($text, $signature);
    }

    public function cbCreateKeysIfNecessary()
    {
        if (!$this['public_key']) {
            $this->createKeys();
        }
    }

    protected function createKeys() {
        $rsa = new \phpseclib\Crypt\RSA();
        $keypair = $rsa->createKey(4096);
        $this['private_key'] = preg_replace("/\r/", "", $keypair['privatekey']);
        $this['public_key'] = preg_replace("/\r/", "", $keypair['publickey']);
    }
}
