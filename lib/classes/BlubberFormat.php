<?php

class BlubberFormat extends StudipFormat
{
    const REGEXP_HASHTAG = '(?<=^|\s)#([\w\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]+)';

    private static $blubber_rules = [
        'hashtags' => [
            'start'    => self::REGEXP_HASHTAG,
            'callback' => 'BlubberFormat::markupHashtags'
        ]
    ];

    /**
     * Adds a new markup rule to the blubber markup set. This can
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
    public static function addBlubberMarkup($name, $start, $end, $callback, $before = null)
    {
        $inserted = false;
        foreach (self::$blubber_rules as $rule_name => $rule) {
            if ($rule_name === $before) {
                self::$blubber_rules[$name] = compact('start', 'end', 'callback');
                $inserted = true;
            }
            if ($inserted) {
                unset(self::$blubber_rules[$rule_name]);
                self::$blubber_rules[$rule_name] = $rule;
            }
        }
        if (!$inserted) {
            self::$blubber_rules[$name] = compact('start', 'end', 'callback');
        }
    }

    /**
     * Returns a single markup-rule if it exists.
     * @return array: array('start' => "...", 'end' => "...", 'callback' => "...")
     */
    public static function getBlubberMarkup($name)
    {
        return self::$blubber_rules[$name];
    }

    /**
     * Removes a markup rule from the blubber markup set.
     *
     * @param string $name Name of the rule
     */
    public static function removeBlubberMarkup($name)
    {
        unset(self::$blubber_rules[$name]);
    }

    /**
     * Initializes a new BlubberFormat instance.
     */
    public function __construct()
    {
        parent::__construct();
        foreach (self::$blubber_rules as $name => $rule) {
            $this->addMarkup(
                $name,
                $rule['start'],
                $rule['end'],
                $rule['callback'],
                $rule['before'] ?: null
            );
        }
    }

    /**
     * Stud.IP markup for hashtags
     *
     * @param  StudipFormat $markup  Markup object
     * @param  array        $matches Found matches
     * @return string
     */
    protected static function markupHashtags($markup, $matches)
    {
        $tag = $matches[1];
        return '<a href="'.URLHelper::getLink("dispatch.php/blubber", ['search' => "#".$tag]).'" class="blubber_hashtag" data-tag="'.htmlReady($tag).'">#'.htmlReady($tag).'</a>';

    }

}
