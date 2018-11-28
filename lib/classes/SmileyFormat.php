<?
require_once 'app/models/smiley.php';

/**
 * SmileyFormat.php
 * 
 * Provides a formatting object for smileys.
 *
 * @author   Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @category Stud.IP
 * @package  smiley
 * @since    2.3
 * @uses     Smiley
 */
class SmileyFormat extends TextFormat
{
    const REGEXP = '(\>|^|\s):([_a-zA-Z][_a-z0-9A-Z-]*):(?=$|\<|\s)';
    
    function __construct()
    {
        $rules = array();

        // Smiley rule
        $rules['smileys'] = array(
            'start'    => self::REGEXP,
            'callback' => 'SmileyFormat::smiley'
        );

        // Smiley short notation rule
        $needles = array_keys(Smiley::getShort());
        $needles = array_map('preg_quote', $needles);
        $rules['smileys_short'] = array(
            'start'    => '(>|^|\s)(' . implode('|', $needles) . ')(?=$|<|\s)',
            'callback' => 'SmileyFormat::short'
        );

        parent::__construct($rules);
    }

    /**
     * Smiley notation defined by name (:name:)
     */
    static function smiley($markup, $matches)
    {
        return $matches[1] . Smiley::img($matches[2]) . $matches[3];
    }

    /**
     * Smiley short notation as defined in database
     */
    static function short($markup, $matches)
    {
        $smileys = Smiley::getShort();
        $name    = $smileys[$matches[2]];
        return isset($name)
            ? $matches[1] . Smiley::img($name) . $matches[3]
            : $matches[0];
    }
}