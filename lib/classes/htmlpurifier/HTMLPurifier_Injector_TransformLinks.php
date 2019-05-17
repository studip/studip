<?php

/**
 * Transform internal links if multiple domain names are configured.
 */
class HTMLPurifier_Injector_TransformLinks extends HTMLPurifier_Injector
{
    public $name = 'TransformLinks';
    public $needed = ['a' => ['href', 'class']];

    public function handleElement(&$token)
    {
        if ($token->name === 'a' && $token->attr['class'] === 'link-intern') {
            $token->attr['href'] = TransformInternalLinks($token->attr['href']);
        }
    }
}
