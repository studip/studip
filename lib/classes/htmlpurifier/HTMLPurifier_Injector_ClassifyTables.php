<?php

/**
 * Ensure that tables always have their class attribute set to 'content'.
 */
class HTMLPurifier_Injector_ClassifyTables extends HTMLPurifier_Injector
{
    public $name = 'ClassifyTables';
    public $needed = array('table' => array('class'));

    public function handleElement(&$token)
    {
        if ($token->name === 'table') {
            $token->attr['class'] = 'content';
        }
    }
}
