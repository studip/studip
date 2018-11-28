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

class WikiFormat extends StudipFormat
{
    private static $wiki_rules = array(
        'wiki-comments' => array(
            'start'    => '\[comment(=.*?)\](.*?)\[\/comment\]',
            'callback' => 'WikiFormat::markupWikiComments'
        ),
        'wiki-links' => array(
            'start'    => '\[\[(.*?)(?:\|(.*?))?\]\]',
            'callback' => 'WikiFormat::markupWikiLinks',
            'before'   => 'links'
        ),
    );

    /**
     * Adds a new markup rule to the wiki markup set. This can
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
    public static function addWikiMarkup($name, $start, $end, $callback, $before = null)
    {
        $inserted = false;
        foreach (self::$wiki_rules as $rule_name => $rule) {
            if ($rule_name === $before) {
                self::$wiki_rules[$name] = compact('start', 'end', 'callback');
                $inserted = true;
            }
            if ($inserted) {
                unset(self::$wiki_rules[$rule_name]);
                self::$wiki_rules[$rule_name] = $rule;
            }
        }
        if (!$inserted) {
            self::$wiki_rules[$name] = compact('start', 'end', 'callback');
        }
    }

    /**
     * Returns a single markup-rule if it exists.
     * @return array: array('start' => "...", 'end' => "...", 'callback' => "...")
     */
    public static function getWikiMarkup($name) {
        return self::$wiki_rules[$name];
    }

    /**
     * Removes a markup rule from the wiki markup set.
     *
     * @param string $name      name of the rule
     */
    public static function removeWikiMarkup($name)
    {
        unset(self::$wiki_rules[$name]);
    }

    /**
     * Initializes a new WikiFormat instance.
     */
    public function __construct()
    {
        parent::__construct();
        foreach (self::$wiki_rules as $name => $rule) {
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
     * Stud.IP markup for wiki-comments
     */
    protected static function markupWikiComments($markup, $matches)
    {
        $from = mb_substr($matches[1], 1);
        $comment = $matches[2];

        if (Request::get("wiki_comments") === "all") {
            $commenttmpl = "<table style=\"border:thin solid;margin: 5px;\" bgcolor=\"#ffff88\"><tr><td><font size=-1><b>"._("Kommentar von")." %1\$s:</b>&nbsp;</font></td></tr><tr class=steelgrau><td class=steelgrau><font size=-1>%2\$s</font></td></tr></table>";
            return sprintf($commenttmpl,
                $from,
                $comment
            );
        } elseif(Request::get("wiki_comments") !== "none") {
            $from = decodeHTML($from);
            $comment = decodeHTML($comment); //because tooltip already escapes
            return sprintf(
                    '<a href="javascript:void(0);" %s>'.
                        Icon::create('chat2', 'status-yellow').
                    '</a>',
                tooltip(sprintf("%s %s: %s", _("Kommentar von"), $from, $comment), TRUE, TRUE)
            );
        } else {
            return "";
        }

    }

    protected static function markupWikiLinks($markup, $matches) {
        $page = decodeHTML($matches[1]);
        $display_page = $matches[2] ? $markup->format($matches[2]) : htmlReady($page);

        if (keywordExists($page, Context::getId())) {
            return sprintf('<a href="%s">%s</a>',
                URLHelper::getLink("wiki.php", array('keyword' => $page)),
                $display_page
            );
        } else {
            return sprintf('<a href="%s">%s(?)</a>',
                URLHelper::getLink("wiki.php", array(
                    'keyword' => $page,
                    'view' => 'editnew'
                )),
                $display_page
            );
        }
    }

}
