<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO


include_once 'vendor/idna_convert/idna_convert.class.php';
require_once 'lib/wiki.inc.php';

// Wrapper for formatted content (defined as a constant since it is used
// in the unit test defined in tests/unit/lib/VisualTest.php as well).
define('FORMATTED_CONTENT_WRAPPER', '<div class="formatted-content">%s</div>');

//// Functions for processing marked-up text (Stud.IP markup, HTML, JS).

use Studip\Markup;

function htmlReady($what, $trim=TRUE, $br=FALSE, $double_encode=true) {
    return Markup::htmlReady($what, $trim, $br, $double_encode);
}

/**
 * Prepare text for wysiwyg (if enabled), otherwise convert special
 * characters using htmlReady.
 *
 * @param  string  $text  The text.
 * @param  boolean $trim  Trim text before applying markup rules, if TRUE.
 * @param  boolean $br    Replace newlines by <br>, if TRUE and wysiwyg editor disabled.
 * @param  boolean $double_encode  Encode existing HTML entities, if TRUE and wysiwyg editor disabled.
 * @return string         The converted string.
 */
function wysiwygReady($what, $trim=TRUE, $br=FALSE, $double_encode=true) {
    return Markup::wysiwygReady($what, $trim, $br, $double_encode);
}

function jsReady ($what, $target) {
    switch ($target) {

    case "script-single" :
        return addcslashes($what, "\\'\n\r");
    break;

    case "script-double" :
        return addcslashes($what, "\\\"\n\r");
    break;

    case "inline-single" :
        return htmlReady(addcslashes($what, "\\'\n\r"), false, false, true);
    break;

    case "inline-double" :
        return htmlReady(addcslashes($what, "\\\"\n\r"), false, false, true);
    break;

    }
    return addslashes($what);
}

/**
 * Quote a piece of text, optionally include the author's name.
 *
 * Applies Stud.IP-Markup if WYSIWYG/HTML is disabled and HTML
 * if it is enabled.
 *
 * @param string $text Text that is to be quoted.
 * @param string $author Name of the text's author (optional).
 *
 * @return string The quoted text.
 */
function quotes_encode($text, $author = '')
{
    // If quoting is changed update these functions:
    // - StudipFormat::markupQuote
    //   lib/classes/StudipFormat.php
    // - quotes_encode lib/visual.inc.php
    // - STUDIP.Forum.citeEntry > quote
    //   public/plugins_packages/core/Forum/javascript/forum.js
    // - studipQuotePlugin > insertStudipQuote
    //   public/assets/javascripts/ckeditor/plugins/studip-quote/plugin.js

    if (Markup::editorEnabled()) {
        // quote with HTML markup
        $text = Markup::markupToHtml($text);

        if ($author) {
            $title = sprintf(_('%s hat geschrieben:'), htmlReady($author));
            $text = '<div class="author">' . $title . '</div>' . $text;
        }
        $text = sprintf('<blockquote>%s</blockquote><p>&nbsp;</p>', $text);
        return Markup::markAsHtml($text);
    }

    if (Markup::isHtml($text)) {
        // remove HTML before quoting
        $text = Markup::removeHtml($text);
    }

    // quote with Stud.IP markup
    if ($author) {
        return "[quote=" . $author . "]\n" . $text . "\n[/quote]\n";
    }
    return "[quote]\n" . $text . "\n[/quote]\n";
}

/**
 * Common function to get all special Stud.IP formattings.
 *
 * @access public
 * @param string  $text  Marked-up text.
 * @param boolean $trim  Trim leading and trailing whitespace, if TRUE.
 * @param boolean $extern         (deprecated, has no effect)
 * @param boolean $wiki           (deprecated, has no effect)
 * @param string  $show_comments  (deprecated, has no effect)
 * @return string        HTML code computed by applying markup-rules.
 */
// TODO remove unused function arguments
function formatReady($text, $trim = true, $extern = false, $wiki = false, $show_comments = 'icon')
{
    $formatted = Markup::apply(new StudipFormat(), $text, $trim);

    return $formatted !== '' ? sprintf(FORMATTED_CONTENT_WRAPPER, $formatted) : '';
}

/**
 * Simplified version of formatReady that handles link formatting only.
 *
 * @param  string $text   Marked-up text.
 * @param  bool   $nl2br  Convert newlines to <br>.
 * @return string         Marked-up text with markup-links converted to
 *                        HTML-links.
 */
function formatLinks($text, $nl2br=TRUE){
    $link_markup_rule = StudipCoreFormat::getStudipMarkup('links');
    $markup = new TextFormat();
    $markup->addMarkup(
        'links',
        $link_markup_rule['start'],
        $link_markup_rule['end'],
        $link_markup_rule['callback']
    );
    return $markup->format(htmlReady($text, true, $nl2br));
}

/**
 * Special version of formatReady for wiki-webs.
 *
 * @access public
 * @param  string $what  Marked-up text.
 * @param  string $trim  Trim leading and trailing whitespace, if TRUE.
 * @return string        HTML code computed by applying markup-rules.
 */
function wikiReady($text, $trim=TRUE) {
    $formatted = Markup::apply(new WikiFormat(), $text, $trim);

    return $formatted !== '' ? sprintf(FORMATTED_CONTENT_WRAPPER, $formatted) : '';
}

/**
 * Apply StudipTransformFormat rules to marked-up text.
 *
 * @param  string $text  Marked-up text.
 * @return string        HTML code computed by applying markup-rules.
 */
function transformBeforeSave($text){
    $markup = new StudipTransformFormat();
    return $markup->format($text);
}

////////////////////////////////////////////////////////////////////////////////

/**
* decodes html entities to normal characters
*
* @access   public
* @param    string
* @return   string
*/
function decodeHTML ($string) {
    return html_entity_decode($string, ENT_QUOTES);
}

/**
* formats a ~~~~ wiki signature with username and timestamp
* @param string
* @param unix timestamp
*/
function preg_call_format_signature($username, $timestamp) {
    $fullname = get_fullname_from_uname($username);
    $date = strftime('%x, %X', $timestamp);
    return '<span style="font-size: 75%">-- <a href="'.URLHelper::getLink('dispatch.php/profile', ['username' => $username]).'">'.htmlReady($fullname).'</a> '.htmlReady($date).'</span>';
}


/**
* removes all characters used by quick-format-syntax
*
* @access   public
* @param    string
* @return   string
*/
function kill_format ($text) {
    if (Markup::isHtml($text)) {
        $is_fallback = Markup::isHtmlFallback($text);
        $text = Markup::removeHtml($text);

        if (!$is_fallback) {
            // pure HTML - no Stud.IP markup to remove
            return $text;
        }
    }

    // remove Stud.IP markup
    $text = preg_replace("'\n?\r\n?'", "\n", $text);
    // wir wandeln [code] einfach in [pre][nop] um und sind ein Problem los ... :-)
    $text = preg_replace_callback("|(\[/?code\])|isU", function ($a) {
        return $a[0] === '[code]' ? '[pre][nop]' : '[/nop][/pre]';
    }, $text);

    $pattern = [
                    "'(^|\n)\!{1,4}(.+)$'m",      // Ueberschriften
                    "'(\n|\A)(-|=)+ (.+)$'m",     // Aufzaehlungslisten
                    "'%%(\S|\S.*?\S)%%'s",        // ML-kursiv
                    "'\*\*(\S|\S.*?\S)\*\*'s",    // ML-fett
                    "'__(\S|\S.*?\S)__'s",        // ML-unterstrichen
                    "'##(\S|\S.*?\S)##'s",        // ML-diktengleich
                    "'\+\+(((\+\+)*)(\S|\S.*?\S)?\\2)\+\+'s",  // ML-groesser
                    "'--(((--)*)(\S|\S.*?\S)?\\2)--'s",        // ML-kleiner
                    "'>>(\S|\S.*?\S)>>'is",  // ML-hochgestellt
                    "'<<(\S|\S.*?\S)<<'is",  // ML-tiefgestellt
                    "'{-(.+?)-}'is" ,        // durchgestrichen
                    "'\n\n  (((\n\n)  )*(.+?))(\Z|\n\n(?! ))'s",  // Absatz eingerueckt
                    "'(?<=\n|^)--+(\d?)(\n|$|(?=<))'m", // Trennlinie
                    "'\[pre\](.+?)\[/pre\]'is" ,        // praeformatierter Text
                    "'\[nop\].+\[/nop\]'isU",
                    //"'\[.+?\](((http\://|https\://|ftp\://)?([^/\s]+)(.[^/\s]+){2,})|([-a-z0-9_]+(\.[_a-z0-9-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)+)))'i",
                    "'\[(.+?)\](((http\://|https\://|ftp\://)?([^/\s]+)(\.[^/\s]+){2,}(/[^\s]*)?)|([-a-z0-9_]+(\.[_a-z0-9-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)+)))'i",
            //      "'\[quote=.+?quote\]'is",    // quoting
                    "'(\s):[^\s]+?:(\s)'s"              // smileys

                    ];
    $replace = [
                    "\\1\\2", "\\1\\3",
                    "\\1", "\\1", "\\1", "\\1", "\\1", "\\1",
                    "\\1", "\\1", "\\1", "\n\\1\n", "", "\\1",'[nop] [/nop]',
                    //"\\2",
                    '$1 ($2)',
                     //"",
                      '$1$2'];
    $callback = function ($c) {
        return function ($m) use ($c) {
            return $m[1] . mb_substr(str_replace($c, ' ', $m[2]), 0, -1);
        };
    };
    $pattern_callback = [
        "'(^|\s)%(?!%)(\S+%)+'" => $callback('%'),     // SL-kursiv
        "'(^|\s)\*(?!\*)(\S+\*)+'" => $callback('*') ,  // SL-fett
        "'(^|\s)_(?!_)(\S+_)+'" => $callback('_'),     // SL-unterstrichen
        "'(^|\s)#(?!#)(\S+#)+'" => $callback('#'),     // SL-diktengleich
        "'(^|\s)\+(?!\+)(\S+\+)+'" => $callback('+'),  // SL-groesser
        "'(^|\s)-(?!-)(\S+-)+'" => $callback('-'),     // SL-kleiner
        "'(^|\s)>(?!>)(\S+>)+'" => $callback('>'),     // SL-hochgestellt
        "'(^|\s)<(?!<)(\S+<)+'" => $callback('<'),     // SL-tiefgestellt);
    ];

    if (preg_match_all("'\[nop\](.+)\[/nop\]'isU", $text, $matches)) {
        $text = preg_replace($pattern, $replace, $text);
        $text = preg_replace_callback_array($pattern_callback, $text);
        $text = explode("[nop] [/nop]", $text);
        $i = 0;
        $all = '';
        foreach ($text as $w)
            $all .= $w . $matches[1][$i++];

        return $all;
    }
    $text = preg_replace($pattern, $replace, $text);
    $text = preg_replace_callback_array($pattern_callback, $text);
    return $text;
}

function isURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function isLinkIntern($url) {
    $pum = @parse_url(TransformInternalLinks($url));
    return in_array($pum['scheme'], ['https', 'http', NULL], true)
        && in_array($pum['host'], [$_SERVER['SERVER_NAME'], NULL], true)
        && in_array($pum['port'], [$_SERVER['SERVER_PORT'], NULL], true)
        && mb_strpos($pum['path'], $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']) === 0;
}

/**
* convert links with 'umlauten' to punycode
*
* @access   public
* @param    string  link to convert
* @param    boolean  for mailadr = true and for other link = false
* @return   string  link in punycode
*/
function idna_link($link, $mail = false){
    if (!Config::get()->CONVERT_IDNA_URL) return $link;
    $pu = @parse_url($link);
    if (preg_match('/&\w+;/i',$pu['host'])) { //umlaute?  (html-coded)
        $IDN = new idna_convert();
        $out = false;
        if ($mail){
            if (preg_match('#^([^@]*)@(.*)$#i',$link, $matches)) {
                $out = $IDN->encode(decodeHTML($matches[2], ENT_NOQUOTES)); // false by error
                $out = ($out)? $matches[1].'@'.htmlReady($out) : $link;
            }
        }elseif (preg_match('#^([^/]*)//([^/?]*)(((/|\?).*$)|$)#i',$link, $matches)) {
            $out = $IDN->encode(decodeHTML($matches[2], ENT_NOQUOTES)); // false by error
            $out = ($out)? $matches[1].'//'.htmlReady($out).$matches[3] : $link;
        }
        return ($out)? $out:$link;
    }
    return $link;
}


/**
 * Create smileys
 *
 * This functions converts the smiley codes notation (:name:) as well as the
 * available short notation.
 *
 * @access public
 * @param  string $text The text to convert
 * @return string Converted text
 */
function smile($text = '') {
    $markup = new SmileyFormat();
    return $markup->format($text);
}


/**
* create symbols from the shorts
*
* This functions converts the short, locatet in the config.inc
* into the assigned pictures. It uses a different directory
* as the smile-function, becauso symbols should not be shown in
* the smiley and so, no link is given onto the picture. A tooltip which
* shows the symbol code is given, too.
*
* @access   public
* @param        string  the text to convert
* @return       string  convertet text
*/
function symbol ($text = '')
{
    if (!$text) {
        return $text;
    }

    $patterns = [];
    $replaces = [];
    //symbols in short notation
    foreach ($GLOBALS['SYMBOL_SHORT'] as $key => $value) {
        $patterns[] = "'" . preg_quote($key) . "'m";
        $replaces[] = $value;
    }

    return preg_replace($patterns, $replaces, $text);
}

//Beschneidungsfunktion fuer alle printhead Ausgaben
function mila ($titel, $size = 60) {
    global $auth;

    if ($auth->auth["jscript"] AND $size == 60) {
        //hier wird die maximale Laenge berechnet, nach der Abgeschnitten wird (JS dynamisch)
        if (mb_strlen ($titel) >$auth->auth["xres"] / 13)
            $titel=mb_substr($titel, 0, $auth->auth["xres"] / 13)."... ";
    }
    else {
        if (mb_strlen ($titel) >$size)
            $titel=mb_substr($titel, 0, $size)."... ";
    }
    return $titel;
}

/**
 * Ausgabe der Aufklapp-Kopfzeile
 *
 * @param $breite
 * @param $left
 * @param $link
 * @param $open
 * @param $new
 * @param $icon
 * @param $titel
 * @param $zusatz
 * @param $timestmp
 * @param $printout
 * @param $index
 * @param $indikator
 * @param $css_class
 */
function printhead($breite, $left, $link, $open, $new, $icon, $titel, $zusatz,
                   $timestmp = 0, $printout = TRUE, $index = "", $indikator = "age",
                   $css_class = NULL)
{
    global $user;

    // Verzweigung was der Pfeil anzeigen soll
    if ($indikator == "viewcount") {
        if ($index == "0") {
            $timecolor = "#BBBBBB";
        } else {
            $tmp = $index;
            if ($tmp > 68)
                $tmp = 68;
            $tmp = 68-$tmp;
            $green = dechex(255 - $tmp);
            $other = dechex(119 + ($tmp/1.5));
            $timecolor= "#" . $other . $green . $other;
        }
    } elseif ($indikator == "rating") {
        if ($index == "?") {
            $timecolor = "#BBBBBB";
        } else {
            $tmp = (ABS(1-$index))*10*3;
            $green = dechex(255 - $tmp);
            $other = dechex(0);
            $red = dechex(255);
            $timecolor= "#" . $red . $green . $other;
        }
    } elseif ($indikator == "score") {
        if ($index == "0") {
            $timecolor = "#BBBBBB";
        } else {
            if ($index > 68)
                $tmp = 68;
            else
                $tmp = $index;
            $tmpb = 68-$tmp;
            $blue = dechex(255 - $tmpb);
            $other = dechex(119 + ($tmpb/1.5));
            $timecolor= "#" . $other . $other . $blue;
        }
    } else {
        if ($timestmp == 0)
            $timecolor = "#BBBBBB";
        else {
            if ($new == TRUE)
                $timecolor = "#FF0000";
            else {
                $timediff = (int) log((time() - $timestmp) / 86400 + 1) * 15;
                if ($timediff >= 68)
                    $timediff = 68;
                $red = dechex(255 - $timediff);
                $other = dechex(119 + $timediff);
                $timecolor= "#" . $red . $other . $other;
            }
        }
    }

    //TODO: überarbeiten -> valides html und/oder template draus machen...
    $class = "printhead";
    $class2 = "printhead2";
    $class3 = "printhead3";

    if ($css_class) {
        $class = $class2 = $class3 = $css_class;
    }

    if ($open == "close") {
        $print = "<td bgcolor=\"".$timecolor."\" class=\"".$class2."\" nowrap=\"nowrap\" width=\"1%\"";
        $print .= " align=\"left\" valign=\"top\">";
    }
    else {
        $print = "<td bgcolor=\"".$timecolor."\" class=\"".$class3."\" nowrap=\"nowrap\" width=\"1%\"";
        $print .= " align=\"left\" valign=\"top\">";
    }

    if ($link)
        $print .= "<a href=\"".$link."\">";

    if ($open == "open")
        $titel = "<b>" . $titel . "</b>";

    $img = $open === 'close'
         ? 'forumgrau2.png'
         : 'forumgraurunt2.png';
    $attr = [];

    if ($link) {
        // TODO [tlx] What is addon used for? This seems to lead to invalid html
        //      so i will ditch it for now in the output
        $addon = $index
               ? " ($indikator: $index)"
               : '';

        $attr = $open === 'close'
              ? tooltip2(_('Objekt aufklappen'))
              : tooltip2(_('Objekt zuklappen'));
    }

    $print .= Assets::img($img, $attr) . " ";
    if ($link) {
        $print .= "</a> ";
    }
    $print .= "</td><td class=\"".$class."\" nowrap=\"nowrap\" width=\"1%\" valign=\"bottom\"> $icon &nbsp; </td>";
    $print .= "<td class=\"".$class."\" align=\"left\" width=\"20%\" nowrap=\"nowrap\" valign=\"bottom\"> ";
    $print .= $titel."</td><td align=\"right\" nowrap=\"nowrap\" class=\"".$class."\" width=\"99%\" valign=\"bottom\">";
    $print .= $zusatz."</td>";


    if ($printout)
        echo $print;
    else
        return $print;
}

//Ausgabe des Contents einer aufgeklappten Kopfzeile
function printcontent ($breite, $write = FALSE, $inhalt, $edit, $printout = TRUE, $addon="", $noTdTag = false) {

    $print = "";
    if ($noTdTag == false)
    {
        $print .= "<td class=\"printcontent\" width=\"22\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $print .= "</td><td class=\"printcontent\" width=\"$breite\" valign=\"bottom\"><br>";
    }

    $print .= $inhalt;

    if ($edit) {
        $print .= "<br><br><div align=\"center\">$edit</div>";
        if ($addon!="") {
            if (mb_substr($addon,0,5)=="open:") { // es wird der öffnen-Pfeil mit Link ausgegeben
                $print .= "</td><td valign=\"middle\" class=\"table_row_even\" nowrap><a href=\"".mb_substr($addon,5)."\">";
                $print .= Icon::create('arr_1left', 'clickable', ['title' => _('Bewertungsbereich öffnen')])->asImg();
                $print .= "</a>&nbsp;";
            } else {              // es wird erweiterter Inhalt ausgegeben
                $print .= "</td><td class=\"content_body_panel\" nowrap>";
                $print .= "<font size=\"-2\" color=\"#444444\">$addon";
            }
        }
    } else {
        $print .= "<br>";
    }

    if ($noTdTag == false)
    {
        $print .= "</td>";
    }

    if ($printout)
        echo $print;
    else
        return $print;
}

/**
 * Returns a given text as html tooltip
 *
 * title and alt attribute is default, with_popup means a JS alert box
 * activated on click
 *
 * @param        string  $text
 * @param        boolean $with_alt    return text with alt attribute
 * @param        boolean $with_popup  return text with JS alert box on click
 * @return       string
 */
function tooltip ($text, $with_alt = TRUE, $with_popup = FALSE) {
    return arrayToHtmlAttributes(tooltip2($text, $with_alt, $with_popup));
}

/**
 * Returns a given text as an array of html attributes used as tooltip
 *
 * title and alt attribute is default, with_popup means a JS alert box
 * activated on click
 *
 * @param        string  $text
 * @param        boolean $with_alt    return text with alt attribute
 * @param        boolean $with_popup  return text with JS alert box on click
 * @return       string
 */
function tooltip2($text, $with_alt = TRUE, $with_popup = FALSE) {

    $ret = [];

    if ($with_popup) {
        $ret['onClick'] = "alert('".JSReady($text, "alert")."');";
    }

    $text = preg_replace("/(\n\r|\r\n|\n|\r)/", " ", $text);
    $text = htmlReady($text);

    if ($with_alt) {
        $ret['alt'] = $text;
    }
    $ret['title'] = $text;

    return $ret;
}

/**
 * returns a html-snippet with an icon and a tooltip on it
 *
 * @param string $text tooltip text, html gets encoded
 * @param bool $important render icon in "important" style
 * @param bool $html tooltip text is HTML content
 */
function tooltipIcon($text, $important = false, $html = false)
{
    if (!trim($text)) {
        return;
    }

    // render tooltip
    $template = $GLOBALS['template_factory']->open('shared/tooltip');
    return $template->render(compact('text', 'important', 'html'));
}

/**
 * returns a html-snippet with an icon and a tooltip on it
 *
 * @param string $text tooltip text, html is rendered as is
 * @param bool $important render icon in "important" style
 */
function tooltipHtmlIcon($text, $important = false)
{
    // render tooltip
    $html = true;
    $template = $GLOBALS['template_factory']->open('shared/tooltip');
    return $template->render(compact('text', 'important', 'html'));
}

/**
 * detects internal links in a given string and convert used domain to the domain
 * actually used (only necessary if more than one domain exists), relative URLs are
 * converted to absolute URLs
 *
 * @param    string  $str URL/Link to convert
 * @return   string  converted URL/Link
*/
function TransformInternalLinks($str){
    $str = trim($str);
    if (mb_strpos($str, 'http') !== 0) {
        if ($str[0] === '/') {
            $str = mb_substr($str, mb_strlen($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']));
        }
        $str = $GLOBALS['ABSOLUTE_URI_STUDIP'] . $str;
    }
    if (is_array($GLOBALS['STUDIP_DOMAINS']) && count($GLOBALS['STUDIP_DOMAINS']) > 1) {
        if (!isset($GLOBALS['TransformInternalLinks_domainData'])){
            $domain_data['domains'] = '';
            foreach ($GLOBALS['STUDIP_DOMAINS'] as $studip_domain) $domain_data['domains'] .= '|' . preg_quote($studip_domain);
            $domain_data['domains'] = preg_replace("'\|[^/|]*'", '$0[^/]*?', $domain_data['domains']);
            $domain_data['domains'] = mb_substr($domain_data['domains'], 1);
            $domain_data['user_domain'] = preg_replace("'^({$domain_data['domains']})(.*)$'i", "\\1", $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            $domain_data['user_domain_scheme'] = 'http' . (($_SERVER['HTTPS'] || $_SERVER['SERVER_PORT'] == 443) ? 's' : '') . '://';
            $GLOBALS['TransformInternalLinks_domainData'] = $domain_data;
        }
        $domain_data = $GLOBALS['TransformInternalLinks_domainData'];
        return preg_replace("'https?\://({$domain_data['domains']})((/[^<\s]*[^\.\s<])*)'i", "{$domain_data['user_domain_scheme']}{$domain_data['user_domain']}\\2", $str);
    } else {
        return $str;
    }
}

/**
* creates a modal dialog ensuring that the user is really aware about the action to perform
*
* @param   string $question          question of the modal dialog
* @param   array  $approveParams     an array of params for a link to be used on approval
* @param   array  $disapproveParams  an array of params for a link to be used on disapproval
* @param   string $baseUrl           if set, this url is used, PHP_SELF otherwise
*
* @return  string $dialog            text which contains the dialog
*
* @deprecated since Stud.IP 4.2, use QuestionBox oder PageLayout::postQuestion()
*/
function createQuestion($question, $approveParams, $disapproveParams = [], $baseUrl = '') {
    return (string) QuestionBox::create(
        $question,
        URLHelper::getURL($baseUrl, $approveParams),
        URLHelper::getURL($baseUrl, $disapproveParams)
    );
}

/**
* creates a modal dialog ensuring that the user is really aware about the action to perform with formulars
*
* @param   string $question          question of the modal dialog
* @param   array  $approveParams     an array of params for a link to be used on approval
* @param   array  $disapproveParams  an array of params for a link to be used on disapproval
* @param   string $baseUrl           if set, this url is used, PHP_SELF otherwise
*
* @return  string $dialog            text which contains the dialog
*
* @deprecated since Stud.IP 4.2, use QuestionBox or PageLayout::postQuestion()
*/
function createQuestion2($question, $approveParams, $disapproveParams = [], $baseUrl = '') {
    return createQuestion($question, $approveParams, $disapproveParams, $baseUrl);
}

/**
 * Displays the provided exception in a more readable fashion.
 *
 * @param Exception $exception The exception to be displayed
 * @param bool $as_html Indicates whether the exception shall be displayed as
 *                      plain text or html (optional, defaults to plain text)
 * @param bool $deep    Indicates whether any previous exception should be
 *                      included in the output (optional, defaults to false)
 * @return String The exception display either as plain text or html
 */
function display_exception($exception, $as_html = false, $deep = false) {
    $result  = '';
    $result .= sprintf("%s: %s\n", _('Typ'), get_class($exception));
    $result .= sprintf("%s: %s\n", _('Nachricht'), $exception->getMessage());
    $result .= sprintf("%s: %d\n", _('Code'), $exception->getCode());

    $trace = sprintf("  #$ %s(%u)\n", $exception->getFile(), $exception->getLine())
           . '  '  . str_replace("\n", "\n  ", $exception->getTraceAsString());
    $trace = str_replace($GLOBALS['STUDIP_BASE_PATH'] . '/', '', $trace);
    $result .= sprintf("%s:\n%s\n", _('Stack trace'), $trace);

    if ($deep && $exception->getPrevious()) {
        $result .= "\n";
        $result .= _('Vorherige Exception:') . "\n";
        $result .= display_exception($exception->getPrevious(), false, $deep);
    }

    return $as_html ? nl2br(htmlReady($result)) : $result;
}

/**
 * Returns the appropriate stud.ip icon for a given mime type.
 *
 * @param String $mime_type Mime type to get the icon for
 * @return String Icon path for the mime type
 */
//DEPRECATED: replaced by FileManager::getIconNameForMimeType
//TODO: test: lib/extern/modules/ExternModuleDownload.class.php
//TODO: test: lib/extern/modules/ExternModuleTemplateDownload.class.php
/*
function get_icon_for_mimetype($mime_type)
{

    $icons_application = [
        'file-pdf' => ['pdf'],
        'file-ppt' => ['powerpoint','presentation'],
        'file-excel' => ['excel', 'spreadsheet', 'csv'],
        'file-word' => ['word', 'wordprocessingml', 'opendocument.text', 'rtf'],
        'file-archive' => ['zip', 'rar', 'arj', '7z' ]
        ];
    list($type, $subtype) = explode('/', $mime_type);
    switch ($type) {
        case 'image':
            $ret = 'file-pic';
        break;
        case 'audio':
            $ret = 'file-audio';
            break;
        case 'video':
            $ret = 'file-video';
            break;
        case 'text':
            $ret = 'file-text';
            if (preg_match('/csv|comma-separated-values/i', $subtype)) {
                $ret = 'file-excel';
            }
            break;
        case 'application':
            $ret = 'file-generic';
            foreach($icons_application as $icon => $marker) {
                if (preg_match('/' . join('|', array_map('preg_quote', $marker)) . '/i', $subtype)) {
                    $ret = $icon;
                    break;
                }
            }
            break;
        default:
            $ret = 'file-generic';
    }

    return $ret;
}
*/


if (!function_exists('preg_replace_callback_array')) {
    /**
     * Perform a regular expression search and replace using callbacks
     * @link http://php.net/manual/en/function.preg-replace-callback-array.php
     * @param array $patterns_and_callbacks
     * @param mixed $subject
     * @param int $limit [optional]
     * @param int $count [optional]
     * @return array|NUll  <p>preg_replace_callback_array() returns an array if the subject parameter is an array, or a string otherwise. On errors the return value is NULL</p>
     * <p>If matches are found, the new subject will be returned, otherwise subject will be returned unchanged.</p>
     */
    function preg_replace_callback_array(array $patterns_and_callbacks, $subject, $limit = -1, &$count = null)
    {
        $count = 0;
        foreach ($patterns_and_callbacks as $pattern => &$callback) {
            $subject = preg_replace_callback($pattern, $callback, $subject, $limit, $partial_count);
            $count += $partial_count;
        }
        return preg_last_error() == PREG_NO_ERROR ? $subject : null;
    }

}

/**
 * Converts an array of attributes to an html attribute string.
 *
 * @param array $attributes Associative array of attributes
 * @return string
 * @since Stud.IP 4.1
 * @todo Nested attribute definitions?
 */
function arrayToHtmlAttributes(array $attributes) {
    // Filter empty attributes
    $attributes = array_filter($attributes, function ($value) {
        return isset($value) && $value !== false;
    });

    // Actual conversion
    $result = [];
    foreach ($attributes as $key => $value) {
        if ($value === true) {
            $result[] = htmlReady($key);
        } else {
            $result[] = sprintf('%s="%s"', htmlReady($key), htmlReady($value));
        }
    }
    return implode(' ', $result);
}
