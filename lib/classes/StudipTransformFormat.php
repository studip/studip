<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      <mlunzena@uos.de
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * Format class to transform text before it is saved into the database.
 */
class StudipTransformFormat extends TextFormat
{
    /**
     * list of global Stud.IP transform markup rules
     */
    private static $studip_rules = [
        'signature' => [
            'start'    => '(?<!~)~~~(?!~)',
            'callback' => 'StudipTransformFormat::markupSignature'
        ]
        ,'nop' => [
            'start'    => '\[nop\](.*?)\[\/nop\]',
            'callback' => 'StudipTransformFormat::markupNoFormat'
        ],        
    ];

    /**
     * Returns the list of global Stud.IP markup rules as an array.
     * Each entry has the following attributes: 'start', 'end' and
     * 'callback'. The rule name is used as the entry's array key.
     *
     * @return array list of all markup rules
     */
    public static function getStudipMarkups()
    {
        return self::$studip_rules;
    }

    /**
     * Adds a new markup rule to the global Stud.IP markup set. This can
     * also be used to replace an existing markup rule. The end regular
     * expression is optional (i.e. may be NULL) to indicate that this
     * rule has an empty content model. The callback is called whenever
     * the rule matches and is passed the following arguments:
     *
     * - $markup    the markup parser object
     * - $matches   match results of preg_match for $start
     * - $contents  (parsed) contents of this markup rule
     *
     * @param string $name      name of this rule
     * @param string $start     start regular expression
     * @param string $end       end regular expression (optional)
     * @param callback $callback function generating output of this rule
     */
    public static function addStudipMarkup($name, $start, $end, $callback)
    {
        self::$studip_rules[$name] = compact('start', 'end', 'callback');
    }

    /**
     * Removes a markup rule from the global Stud.IP markup set.
     *
     * @param string $name      name of the rule
     */
    public static function removeStudipMarkup($name)
    {
        unset(self::$studip_rules[$name]);
    }

    /**
     * Initializes a new StudipFormat instance.
     */
    public function __construct()
    {
        parent::__construct(self::getStudipMarkups());
    }

    /**
     * Stud.IP markup for signatures
     */
    protected static function markupSignature($markup, $matches)
    {
        return get_fullname();
    }

    /**
     * Stud.IP markup for unformatted text
     */
    protected static function markupNoFormat($markup, $matches)
    {
        return '[nop]' . $markup->quote($matches[1]) . '[/nop]';
    }
}
