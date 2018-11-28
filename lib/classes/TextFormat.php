<?php
/**
 * TextFormat.php - simple generic text markup parser
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

/**
 * This class implements a somewhat generic text markup parser. It is
 * optimized for rules of the form:
 *
 * markup_rule : START_TAG | START_TAG markup END_TAG
 * markup : TEXT | TEXT markup_rule markup
 *
 * where START_TAG and END_TAG are defined using regular expressions.
 * All rules are applied simultaneously, i.e. the output of a markup
 * rule is not processed again by the parser. The order of the rules
 * matters, however, because the first matching expression determines
 * which markup rule is applied (in case multiple rules match at the
 * same position in the input string).
 *
 * This example adds a new markup rule for 'smile' that replaces each
 * occurrence of the string ':-)' with a corresponding image tag:
 *
 * $markup->addMarkup('smile', ':-\)', NULL,
 *     function($markup) {
 *         return '<img src="smile.png">';
 *     }
 * );
 *
 * This example adds markup for the BBCode '[b]...[/b]' construct:
 *
 * $markup->addMarkup('bold', '\[b\]', '\[\/b\]',
 *     function($markup, $matches, $contents) {
 *         return '<b>' . $contents . '</b>';
 *     }
 * );
*/
class TextFormat
{
    private $markup_rules;
    private $start_regexp;
    private $rule_stack;

    /**
     * Initializes a new TextFormat instance with an initial set of
     * markup rules.
     *
     * @param array $markup_rules   list of markup rules
     */
    public function __construct($markup_rules = array())
    {
        $this->markup_rules = $markup_rules;
        $this->start_regexp = NULL;
        $this->rule_stack = array();
    }

    /**
     * Adds a new markup rule to this TextFormat instance. This can
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
    public function addMarkup($name, $start, $end, $callback, $before = null)
    {
        $inserted = false;
        foreach ($this->markup_rules as $rule_name => $rule) {
            if ($rule_name === $before) {
                $this->markup_rules[$name] = compact('start', 'end', 'callback');
                $inserted = true;
            }
            if ($inserted) {
                unset($this->markup_rules[$rule_name]);
                $this->markup_rules[$rule_name] = $rule;
            }
        }
        if (!$inserted) {
            $this->markup_rules[$name] = compact('start', 'end', 'callback');
        }
        $this->start_regexp = NULL;
    }

    /**
     * Returns a single markup-rule if it exists.
     * @return array: array('start' => "...", 'end' => "...", 'callback' => "...")
     */
    public function getMarkup($name) {
        return $this->markup_rules[$name];
    }

    /**
     * Removes a markup rule from this TextFormat instance.
     *
     * @param string $name      name of the rule
     */
    public function removeMarkup($name)
    {
        unset($this->markup_rules[$name]);
        $this->start_regexp = NULL;
    }

    /**
     * Returns the regular expression used to split the input text
     * into individual tokens. This expression is constructed from
     * the start and end expressions of all markup rules.
     *
     * @return string   regular expression for use by the tokenizer
     */
    private function getTokenRegexp()
    {
        if ($this->start_regexp === NULL && count($this->markup_rules)) {
            foreach ($this->markup_rules as $rule) {
                $tags[] = $rule['start'];

                if (isset($rule['end'])) {
                    $tags[] = $rule['end'];
                }
            }

            $tags = array_unique($tags);
            $regexp = preg_replace('/(?<!\\\\)(\\\\\\\\)*\((?!\?)/', '$0?:', join('|', $tags));
            $this->start_regexp = '/(' . $regexp . ')/ms';
        }

        return $this->start_regexp;
    }

    /**
     * Applies the markup rules to the input text and returns the result.
     *
     * @param string $text      string to format
     *
     * @return string   formatted text
     */
    public function format($text)
    {
        $pattern = $this->getTokenRegexp();
        $options = PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;

        if (isset($pattern)) {
            $parts = preg_split($pattern, $text, -1, $options);
            if (!is_array($parts)) {
                $last_error = error_get_last();
                throw new Exception(__CLASS__ . ': ' . $last_error['message']);
            }
            array_unshift($parts, NULL);
        } else {
            $parts = array(NULL, array($text, 0));
        }

        return $this->formatParts($text, $parts);
    }

    /**
     * Quotes the input text in a way appropriate for the output format,
     * but does not apply any markup rules. This could involve escaping
     * special characters (similar to htmlentities) or other processing.
     *
     * The default implementation in this class does nothing.
     *
     * @param string $text      string to quote
     *
     * @return string   quoted text
     */
    public function quote($text)
    {
        return $text;
    }

    /**
     * Internal method used by format() to apply markup rules to the
     * individual tokens of the input string. $open_rule indicates
     * whether a closing element (and which one) is expected.
     *
     * @param string $text      string to format
     * @param array $pars       token list of input string
     * @param array $open_rule  open markup rule, if any (may be NULL)
     *
     * @return string   formatted text
     */
    protected function formatParts($text, &$parts, $open_rule = NULL)
    {
        $part = next($parts);
        $result = $this->quote($part[0]);

        while (($part = next($parts)) !== false) {
            if (isset($open_rule)) {
                if (self::matchPart($open_rule['end'], $text, $matches, $part[1])) {
                    return $result;
                }
            }

            $matched = false;

            foreach ($this->markup_rules as $ruleKey => $rule) {
                if (self::matchPart($rule['start'], $text, $matches, $part[1])) {
                    if (isset($rule['end'])) {
                        $saved_parts = $parts;

                        array_push($this->rule_stack, $ruleKey);
                        $contents = $this->formatParts($text, $parts, $rule);
                        array_pop($this->rule_stack);

                        // skip this markup rule in case of missing closing tag
                        if (current($parts) === false) {
                            $parts = $saved_parts;
                            continue;
                        }
                    } else {
                        $contents = NULL;
                    }

                    $result .= call_user_func($rule['callback'], $this, $matches, $contents);
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                $result .= $this->quote($part[0]);
            }

            $part = next($parts);
            $result .= $this->quote($part[0]);
        }

        return $result;
    }

    /**
     * Tries to match the given pattern against the text at a specified
     * offset. If a match is found at this position, $matches is filled
     * with the results of the search.
     *
     * @param string $pattern   regular expression
     * @param string $text      string to match against
     * @param array  $matches   result will be stored here
     * @param int    $offset    offset into $text
     *
     * @return boolen   true if the pattern matches at this offset
     */
    private static function matchPart($pattern, $text, &$matches, $offset)
    {
        $pattern = '/' . $pattern . '/ms';
        $result = preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, $offset);

        if ($result) {
            $match_offset = $matches[0][1];

            foreach ($matches as &$match) {
                $match = $match[0];
            }
        }

        return $result && $match_offset === $offset;
    }

    /**
     * Return true if the current markup is surrounded by another markup.
     * @param string $rule Name of the rule (it's key in markup_rules).
     * @return boolean  True if inside of $rule-markup.
     */
    public function isInsideOf($rule)
    {
        return in_array($rule, $this->rule_stack);
    }
}
