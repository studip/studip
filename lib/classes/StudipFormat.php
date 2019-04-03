<?php
/**
 * StudipFormat.php - simple Stud.IP text markup parser
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class StudipFormat extends TextFormat
{
    /**
     * list of global Stud.IP markup rules
     */
    private static $studip_rules = array();

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
     * Sometimes you may want your rule to apply before another specific rule
     * will apply. For this case the parameter $before defines a rulename of
     * existing markup, before which your rule should apply.
     *
     * @param string $name      name of this rule
     * @param string $start     start regular expression
     * @param string $end       end regular expression (optional)
     * @param callback $callback function generating output of this rule
     * @param string $before mark before which rule this rule should be appended
     */
    public static function addStudipMarkup($name, $start, $end, $callback, $before = null)
    {
        // Assume null for empty string
        if ($end === '') {
            $end = null;
        }

        $inserted = false;
        foreach (self::$studip_rules as $rule_name => $rule) {
            if ($rule_name === $before) {
                self::$studip_rules[$name] = compact('start', 'end', 'callback');
                $inserted = true;
            }
            if ($inserted) {
                unset(self::$studip_rules[$rule_name]);
                self::$studip_rules[$rule_name] = $rule;
            }
        }
        if (!$inserted) {
            self::$studip_rules[$name] = compact('start', 'end', 'callback');
        }
    }

    /**
     * Returns a single markup-rule if it exists.
     * @return array: array('start' => "...", 'end' => "...", 'callback' => "...")
     */
    public static function getStudipMarkup($name) {
        return self::$studip_rules[$name];
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

    private $opengraph_collection;

    /**
     * Initializes a new StudipFormat instance.
     */
    public function __construct()
    {
        parent::__construct(self::getStudipMarkups());
    }
}
