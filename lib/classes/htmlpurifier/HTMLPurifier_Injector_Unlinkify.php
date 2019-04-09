<?php

/**
 * Display the URL of an anchor or image instead of linking to it.
 */
class HTMLPurifier_Injector_Unlinkify extends HTMLPurifier_Injector
{
    public $name = 'Unlinkify';
    public $needed = ['a' => 'href', 'img' => 'src'];

    private $sanitizer;
    private $config;
    private $context;

    public function prepare($config, $context)
    {
        $this->sanitizer = new HTMLPurifier_AttrDef_URI();
        $this->config = $config;
        $this->context = $context;
        return parent::prepare($config, $context);
    }

    public function handleElement(&$token)
    {
        if (isset($this->needed[$token->name])) {
            $attribute = $this->needed[$token->name];
            $url = '';
            if (isset($token->attr[$attribute])) {
                $url = $this->sanitizer->validate(
                    $token->attr[$attribute],
                    $this->config,
                    $this->context
                );
            }
            $token = $url ? new HTMLPurifier_Token_Text('[ ' . $url . ' ]') : false;
        }
    }
}
