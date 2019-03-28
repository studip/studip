<?php
/**
 * StudipCoreFormat.php - simple Stud.IP text markup parser
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

class StudipCoreFormat extends TextFormat
{
    /**
     * list of global Stud.IP markup rules
     */
    private static $studip_rules = array(

        // heading level 1-4
        'heading' => array(
            'start'    => '^(!{1,4})([^\n]+)\n*',
            'callback' => 'StudipCoreFormat::markupHeading'
        ),

        // horizontal rule
        'hrule' => array(
            'start'    => '^--(\d?)$',
            'callback' => 'StudipCoreFormat::markupHorizontalRule'
        ),

        // list and table
        'list' => array(
            'start'    => '(^[=-]+ [^\n]+\n?)+',
            'callback' => 'StudipCoreFormat::markupList'
        ),
        'table' => array(
            'start'    => '(^\|[^\n]*\|[^\n]*\n?)+',
            'callback' => 'StudipCoreFormat::markupTable'
        ),

        // block indent
        'indent' => array(
            'start'    => '(^  [^\n]+\n?)+',
            'callback' => 'StudipCoreFormat::markupIndent'
        ),

        // basic text formatting
        'bold' => array(
            'start'    => '\*\*',
            'end'      => '\*\*',
            'callback' => 'StudipCoreFormat::markupText'
        ),
        'italics' => array(
            'start'    => '%%',
            'end'      => '%%',
            'callback' => 'StudipCoreFormat::markupText'
        ),
        'underline' => array(
            'start'    => '__',
            'end'      => '__',
            'callback' => 'StudipCoreFormat::markupText'
        ),
        'verb' => array(
            'start'    => '##',
            'end'      => '##',
            'callback' => 'StudipCoreFormat::markupText'
        ),
        'big' => array(
            'start'    => '(\+\+)+',
            'end'      => '(\+\+)+',
            'callback' => 'StudipCoreFormat::markupTextSize'
        ),
        'small' => array(
            'start'    => '(--)+',
            'end'      => '(--)+',
            'callback' => 'StudipCoreFormat::markupTextSize'
        ),
        'super' => array(
            'start'    => '&gt;&gt;',
            'end'      => '&gt;&gt;',
            'callback' => 'StudipCoreFormat::markupText'
        ),
        'sub' => array(
            'start'    => '&lt;&lt;',
            'end'      => '&lt;&lt;',
            'callback' => 'StudipCoreFormat::markupText'
        ),
        'strike' => array(
            'start'    => '\{-',
            'end'      => '-\}',
            'callback' => 'StudipCoreFormat::markupText'
        ),

        // basic text formatting (simple form)
        'simple_bold' => array(
            'start'    => '(?<=\s|^)\*(\S+)\*(?=\s|$)',
            'callback' => 'StudipCoreFormat::markupTextSimple'
        ),
        'simple_italics' => array(
            'start'    => '(?<=\s|^)%(\S+)%(?=\s|$)',
            'callback' => 'StudipCoreFormat::markupTextSimple'
        ),
        'simple_underline' => array(
            'start'    => '(?<=\s|^)_(\S+)_(?=\s|$)',
            'callback' => 'StudipCoreFormat::markupTextSimple'
        ),
        'simple_verb' => array(
            'start'    => '(?<=\s|^)#(\S+)#(?=\s|$)',
            'callback' => 'StudipCoreFormat::markupTextSimple'
        ),
        'simple_big' => array(
            'start'    => '(?<=\s|^)\+(\S+)\+(?=\s|$)',
            'callback' => 'StudipCoreFormat::markupTextSimple'
        ),
        'simple_small' => array(
            'start'    => '(?<=\s|^)-(\S+)-(?=\s|$)',
            'callback' => 'StudipCoreFormat::markupTextSimple'
        ),
        'simple_super' => array(
            'start'    => '(?<=\s|^)&gt;(\S+)&gt;(?=\s|$)',
            'callback' => 'StudipCoreFormat::markupTextSimple'
        ),
        'simple_sub' => array(
            'start'    => '(?<=\s|^)&lt;(\S+)&lt;(?=\s|$)',
            'callback' => 'StudipCoreFormat::markupTextSimple'
        ),

        // preformatted text, quote, nop and code
        'pre' => array(
            'start'    => '\[pre\]',
            'end'      => '\[\/pre\]',
            'callback' => 'StudipCoreFormat::markupPreformat'
        ),
        'quote' => array(
            'start'    => '\[quote(=.*?)?\]',
            'end'      => '\[\/quote\]',
            'callback' => 'StudipCoreFormat::markupQuote'
        ),
        'nop' => array(
            'start'    => '\[nop\](.*?)\[\/nop\]',
            'callback' => 'StudipCoreFormat::markupNoFormat'
        ),
        'code' => array(
            'start'    => '\[code(=.*?)?\](.*?)\[\/code\]',
            'callback' => 'StudipCoreFormat::markupCode'
        ),
        'media' => array(
            'start'    => '\[(img|flash|audio|video)(.*?)\](.*?)(?=\s|$)',
            'callback' => 'StudipCoreFormat::markupMedia'
        ),
        'emails' => array(
            'start'    => '(?xi:
                # ensure block is preceded by whitspace, line start or closing
                # tag
                (?<=\s|^|\>)

                # capture displayed text
                (?:\[([^\n\f\]]+?)\])?

                # capture actual email address
                ([\w.!#%+-]+@([[:alnum:]-.]+\.[[:alnum:]-.]{2,}))

                # ensure block is succeeded by white space or line end
                (?=\s|$)
            )',
            'callback' => 'StudipCoreFormat::markupEmails'
        ),
        'htmlAnchor' => array(
            # avoid replacing html links by studip's links-markup
            # match <a ...="..."> ... </a>
            'start' => '(?xi: <\s* a (?:\s(?:\s* \w+ \s*=\s* "[^"]*" )*)? \s*>)',
            'end' => '(?xi: <\s* \/\s* a \s*>)',
            'callback' => 'StudipCoreFormat::htmlAnchor'
        ),
        'htmlImg' => array(
            # avoid replacing img links by studip's links-markup
            # match <img ...="..." />
            'start' => '(?xi:
                # match tag start: <img
                <\s*img

                # match optional attributes
                (\s(\s*
                    \w+                              # attribute name
                    (\s*=\s*
                        ("[^\"]*"|\'[^\']*\'|[^\s]*) # attribute value
                    )?
                )+)?

                # match tag end: \/>
                \s*\/?\s*>
            )',
            'callback' => 'StudipCoreFormat::htmlImg'
        ),
        'links' => array(
            // markup: [text]url
            //
            // To set URLs apart from normal text, scheme and host are
            // obligatory. URLs are based on, but allow more than RFC3986 in
            // order to simplify the regular expression.
            //
            // http://tools.ietf.org/html/rfc3986
            'start' => '(?xi:
                # capture 1: displayed text
                (?:\[( [^\n\f\]]+ )\])?

                # capture 2: URL
                \b(
                    # scheme
                    [a-z][a-z0-9\+\-\.]*:\/\/

                    # user:password@host:port\/path?query#fragment
                    [a-zA-Z0-9\x{00a0}-\x{d7ff}\x{f900}-\x{fdcf}\x{fdf0}-\x{ffef}_\-\.~!$&\'\(\)\[\]*+,;=%:@\/?#]+
                )
            )',
            'callback' => 'StudipCoreFormat::markupLinks'
        ),
        'tex' => [
            'start'    => '\[tex\]',
            'end'      => '\[\/tex\]',
            'callback' => 'StudipCoreFormat::markupTexFormat'
        ],
    );

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
            self::$studip_rules[$name] = $end
                    ? compact('start', 'end', 'callback')
                    : compact('start', 'callback');
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

    /**
     * Initializes a new StudipCoreFormat instance.
     */
    public function __construct()
    {
        parent::__construct(self::getStudipMarkups());
    }

    /**
     * Stud.IP markup for headings
     */
    protected static function markupHeading($markup, $matches)
    {
        $level = max(1, 5 - mb_strlen($matches[1]));
        $text = $markup->format($matches[2]);

        return sprintf('<h%d class="content">%s</h%d>', $level, $text, $level);
    }

    /**
     * Stud.IP markup for horizontal rule
     */
    protected static function markupHorizontalRule($markup, $matches)
    {
        return sprintf('<hr class="content"%s>',
            $matches[1] ? ' style="height: '.((int) $matches[1]).'px"' : ""
        );
    }

    /**
     * Basic text formatting: bold, italics, underline, sup, sub etc.
     */
    protected static function markupText($markup, $matches, $contents)
    {
        static $tag = array(
            '**' => 'strong',
            '%%' => 'em',
            '__' => 'u',
            '##' => 'tt',
            '&gt;&gt;' => 'sup',
            '&lt;&lt;' => 'sub',
            '{-' => 's'
        );

        $key = $matches[0];

        return sprintf('<%s>%s</%s>', $tag[$key], $contents, $tag[$key]);
    }

    /**
     * Text size formatting: small and small
     */
    protected static function markupTextSize($markup, $matches, $contents)
    {
        static $tag = array(
            '++' => 'big',
            '--' => 'small',
        );

        $key = $matches[1];
        $level = mb_strlen($matches[0]) / 2;
        $open = str_repeat('<' . $tag[$key] . '>', $level);
        $close = str_repeat('</' . $tag[$key] . '>', $level);

        return $open . $contents . $close;
    }

    /**
     * Basic text formatting: bold, italics, underline etc. (simple form)
     */
    protected static function markupTextSimple($markup, $matches)
    {
        static $tag = array(
            '*' => 'strong',
            '%' => 'em',
            '_' => 'u',
            '#' => 'tt',
            '+' => 'big',
            '-' => 'small',
            '>' => 'sup',
            '<' => 'sub'
        );

        $key = $matches[0][0];
        $text = str_replace($key, ' ', $matches[1]);

        return sprintf('<%s>%s</%s>', $tag[$key], $markup->quote($text), $tag[$key]);
    }

    /**
     * Stud.IP markup for lists (may be nested)
     */
    protected static function markupList($markup, $matches)
    {
        $rows = explode("\n", rtrim($matches[0]));
        $indent = 0;

        foreach ($rows as $row) {
            list($level, $text) = explode(' ', $row, 2);
            $level = mb_strlen($level);

            if ($indent < $level) {
                for (; $indent < $level; ++$indent) {
                    $type = $row[$indent] == '=' ? 'ol' : 'ul';
                    $result .= sprintf('<%s><li>', $type);
                    $types[] = $type;
                }
            } else {
                for (; $indent > $level; --$indent) {
                    $result .= sprintf('</li></%s>', array_pop($types));
                }

                $result .= '</li><li>';
            }

            $result .= $markup->format($text);
        }

        for (; $indent > 0; --$indent) {
            $result .= sprintf('</li></%s>', array_pop($types));
        }

        return $result;
    }

    /**
     * Stud.IP markup for tables
     */
    protected static function markupTable($markup, $matches)
    {
        $rows = explode("\n", rtrim($matches[0]));
        $result = '<table class="content">';

        foreach ($rows as $row) {
            $cells = explode('|', trim(trim($row), '|'));
            $result .= '<tr>';

            foreach ($cells as $cell) {
                $result .= '<td>';
                $result .= $markup->format(trim($cell));
                $result .= '</td>';
            }

            $result .= '</tr>';
        }

        $result .= '</table>';

        return $result;
    }

    /**
     * Stud.IP markup for indented paragraphs
     */
    protected static function markupIndent($markup, $matches)
    {
        $text = preg_replace('/^ +/', '', $matches[0]);
        $text = preg_replace('/^  /m', '', $text);

        return sprintf('<div class="indent">%s</div>', $markup->format($text));
    }

    /**
     * Stud.IP markup for preformatted text
     */
    protected static function markupPreformat($markup, $matches, $contents)
    {
        return sprintf('<pre>%s</pre>', trim($contents));
    }

    /**
     * Stud.IP markup for quoted text
     */
    protected static function markupQuote($markup, $matches, $contents)
    {
        if (mb_strlen($matches[1]) > 1) {
            $title = sprintf(_('%s hat geschrieben:'), $markup->format(mb_substr($matches[1], 1)));
            return sprintf('<blockquote><div class="author">%s</div>%s</blockquote>',
                       $title, trim($contents));
        } else {
            return sprintf('<blockquote>%s</blockquote>',
                       trim($contents));
        }
    }

    /**
     * Stud.IP markup for unformatted text
     */
    protected static function markupNoFormat($markup, $matches)
    {
        return $markup->quote($matches[1]);
    }

    /**
     * Stud.IP markup for (PHP) source code
     */
    protected static function markupCode($markup, $matches)
    {
        $codetype = "";
        if (mb_strlen($matches[1])) {
            $codetype = " ".decodeHTML(trim(mb_substr($matches[1], 1)), ENT_QUOTES);
        }
        $code = decodeHTML(trim($matches[2]), ENT_QUOTES);
        return sprintf('<pre class="usercode %1$s"><code class="%1$s">%2$s</code></pre>',
                       htmlReady($codetype),
                       htmlReady($code));
    }

    /**
     * Stud.IP markup for email-adresses
     */
    protected static function markupEmails($markup, $matches)
    {
        $link_text = $matches[1] ?: $matches[2];
        $email = $matches[2];
        $domain = $matches[3];

        $intern = $domain === $_SERVER['HTTP_HOST'];

        return sprintf('<a class="%s" href="mailto:%s">%s</a>',
            $intern ? "link-intern" : "link-extern",
            $email,
            $link_text
        );
    }

    /**
     * Stud.IP markup for images, audio, video and flash-films
     */
    protected static function markupMedia($markup, $matches)
    {
        $tag = $matches[1];
        $params = explode(":",$matches[2]);
        $url = $matches[3];
        $whitespace = $matches[4];

        foreach ($params as $key => $param) {
            if ($param) {
                if (is_numeric($param)) {
                    $width = $param;
                } elseif(in_array($param, words("left center right"))) {
                    $position = $param;
                } elseif($key === 0 && $param[0] === "=") {
                    $title = mb_substr($param, 1);
                } elseif($key < count($params) - 1) {
                    $virtual_url = $param.":".$params[$key + 1];
                    if (isURL($virtual_url)) {
                        $link = $virtual_url;
                    }
                }
            }
        }

        $format_strings = array(
            'img' => '<img src="%s" style="%s" title="%s" alt="%s">',
            'audio' => '<audio src="%s" style="%s" title="%s" alt="%s" controls></audio>',
            'video' => '<video src="%s" style="%s" title="%s" alt="%s" controls></video>'
        );

        $url = TransformInternalLinks($url);
        $pu = @parse_url($url);
        if (($pu['scheme'] == 'http' || $pu['scheme'] == 'https')
                && ($pu['host'] == $_SERVER['HTTP_HOST'] || $pu['host'].':'.$pu['port'] == $_SERVER['HTTP_HOST'])
                && mb_strpos($pu['path'], $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']) === 0) {
            $intern = true;
            $checkpath = urldecode(mb_substr($pu['path'], mb_strlen($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'])));
            if (mb_strpos($checkpath, '../') === false) {
                list($pu['first_target']) = explode('/', $checkpath);
            } else {
                $pu['first_target'] = false;
            }
        }
        $LOAD_EXTERNAL_MEDIA = Config::GetInstance()->getValue('LOAD_EXTERNAL_MEDIA');
        if ($intern
            && !in_array($pu['first_target'], array('sendfile.php','download','assets','pictures'))
            && !($pu['first_target'] === 'dispatch.php' && mb_strpos($pu['path'], 'dispatch.php/document/download') !== false))
        {
            return $matches[0];
        } elseif ((!$LOAD_EXTERNAL_MEDIA || $LOAD_EXTERNAL_MEDIA === 'deny') && !$intern) {
            return $matches[0];
        }

        //Mediaproxy?
        if (!$intern && $LOAD_EXTERNAL_MEDIA === "proxy" && Seminar_Session::is_current_session_authenticated()) {
            $media_url = $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'dispatch.php/media_proxy?url=' . urlencode(decodeHTML(idna_link($url)));
        } else {
            $media_url = idna_link($url);
        }

        if ($tag === "flash") {
            $width = $width ? $width : 200;
            $height = round($width * 0.75);
            $flash_config = $width > 200 ? $GLOBALS['FLASHPLAYER_DEFAULT_CONFIG_MAX'] : $GLOBALS['FLASHPLAYER_DEFAULT_CONFIG_MIN'];
            $media = '<object type="application/x-shockwave-flash" id="FlashPlayer" data="'.Assets::url().'flash/player_flv.swf" width="'.$width.'" height="'.$height.'">
                        <param name="movie" value="'.Assets::url().'flash/player_flv.swf">
                        <param name="allowFullScreen" value="true">
                        <param name="FlashVars" value="flv='.urlencode(decodeHTML($media_url)).'&amp;startimage='.$link.$flash_config.'">
                        <embed src="'.Assets::url().'flash/player_flv.swf" movie="$media_url" type="application/x-shockwave-flash" FlashVars="flv='.urlencode(decodeHTML($media_url)).'&amp;startimage='.$link.$flash_config.'">
                        </object>';
        } else {
            $media = sprintf($format_strings[$tag],
                $media_url,
                isset($width) ? "width: ".$width."px;" : "",
                $title,
                $title
            );
        }

        if ($tag === 'audio') {
            $random_id = 'audio-' . mb_substr(md5(uniqid('audio', true)), -8);
            $media = str_replace('<audio ', '<audio id="' . $random_id . '" onerror="STUDIP.Audio.handle(this);" ', $media);
        }

        if ($link && $tag === "img") {
            $media = sprintf('<a href="%s"%s>%s</a>',
                $link,
                !isLinkIntern($link) ? ' target="_blank" rel="noreferrer noopener"' : "",
                $media
            );
        }
        if ($position) {
            $media = '<div style="text-align: '.$position.'">'.$media.'</div>';
        }
        $media .= $whitespace;
        return $media;
    }

    /**
     * Stud.IP markup for hyperlinks (intern, extern).
     * Has lower priority than [code], [img], etc
     */
    protected static function markupLinks($markup, $matches)
    {
        if ($markup->isInsideOf('htmlAnchor') || $markup->isInsideOf('htmlImg')) {
            return $matches[0];
        }

        $url = $matches[2];
        $title = $matches[1] ?: $url;

        // Remove closing bracket if not part of url
        $postfix = '';
        if ($title === $url
            && preg_match('/\)[\.,]?$/', $url, $match)
            && substr_count($url, '(') !== substr_count($url, ')'))
        {
            $title = $url = mb_substr($url, 0, -mb_strlen($match[0]));
            $postfix = $match[0];
        }


        $intern = isLinkIntern($url);

        $url = TransformInternalLinks($url);

        $linkmarkup = clone $markup;
        $linkmarkup->removeMarkup('links');
        $linkmarkup->removeMarkup('wiki-links');

        return sprintf('<a class="%s" href="%s"%s>%s</a>%s',
            $intern ? 'link-intern' : 'link-extern',
            $url,
            $intern ? '' : ' target="_blank" rel="noreferrer noopener"',
            $linkmarkup->format($title),
            $postfix
        );
    }

    protected static function htmlAnchor($markup, $matches, $contents)
    {
        return $matches[0] . $contents . '</a>';
    }

    protected static function htmlImg($markup, $matches, $contents)
    {
        return $matches[0];
    }

    /**
     * Stud.IP markup for tex tags
     */
    protected static function markupTexformat($markup, $matches, $contents)
    {
        return sprintf('<span class="math-tex">\\(%s\\)</span>', trim($contents));
    }

}
