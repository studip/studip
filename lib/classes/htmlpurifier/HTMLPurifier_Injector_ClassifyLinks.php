<?php

/**
 * Classify links as internal or external and set the class attribute 
 * accordingly.
 */
class HTMLPurifier_Injector_ClassifyLinks extends HTMLPurifier_Injector
{
    public $name = 'ClassifyLinks';
    public $needed = ['a' => ['href', 'class', 'target']];

    public function handleElement(&$token)
    {
        if ($token->name === 'a' && isset($token->attr['href'])) {
            $is_link_intern = isLinkIntern($token->attr['href']);
            $token->attr['class'] = $is_link_intern ? 'link-intern' : 'link-extern';
            if (!$is_link_intern) {
                $token->attr['target'] = '_blank';
            }
        }
    }
}
