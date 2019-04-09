<?php

/**
 * Injector that converts e-mails to actual links.
 */
class HTMLPurifier_Injector_LinkifyEmail extends HTMLPurifier_Injector
{
    public $name = 'LinkifyEmail';
    public $needed = ['a' => ['href']];

    public function handleText(&$token) {
        if (!$this->allowsElement('a')) return;

        if (strpos($token->data, '@') === false) {
            // our really quick heuristic failed, abort
            return;
        }

        // e-mail regex comes from Drupal 7, see http://api.drupal.org/_filter_url, adapted to match only mails with tld part
        $bits = preg_split('#([A-Za-z0-9._-]+@(?:[A-Za-z0-9._+-]+\.[A-Za-z]{2,64}\b))#S', $token->data, -1, PREG_SPLIT_DELIM_CAPTURE);

        $token = [];

        // $i = index
        // $c = count
        // $l = is link
        for ($i = 0, $c = count($bits), $l = false; $i < $c; $i++, $l = !$l) {
            if (!$l) {
                if ($bits[$i] === '') continue;
                $token[] = new HTMLPurifier_Token_Text($bits[$i]);
            } else {
                $token[] = new HTMLPurifier_Token_Start('a', ['href' => 'mailto:' . $bits[$i]]);
                $token[] = new HTMLPurifier_Token_Text($bits[$i]);
                $token[] = new HTMLPurifier_Token_End('a');
            }
        }

    }
}
