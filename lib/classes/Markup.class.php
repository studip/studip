<?php
/**
 * Markup.class.php - Handling of Stud.IP- and HTML-markup.
 **
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Stud.IP
 * @copyright   (c) 2014 Stud.IP e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       File available since Release 3.0
 * @author      Robert Costa <rcosta@uos.de>
 */
namespace Studip;

require_once 'vendor/HTMLPurifier/HTMLPurifier.standalone.php';
require_once 'htmlpurifier/HTMLPurifier_Injector_ClassifyLinks.php';
require_once 'htmlpurifier/HTMLPurifier_Injector_ClassifyTables.php';
require_once 'htmlpurifier/HTMLPurifier_Injector_LinkifyEmail.php';
require_once 'htmlpurifier/HTMLPurifier_Injector_TransformLinks.php';
require_once 'htmlpurifier/HTMLPurifier_Injector_Unlinkify.php';

class Markup
{
    /**
     * Apply markup rules and clean the text up.
     *
     * @param TextFormat $markup  Markup rules applied on marked-up text.
     * @param string     $text    Marked-up text on which rules are applied.
     * @param boolean    $trim    Trim text before applying markup rules, if TRUE.
     *
     * @return string  HTML code computed from marked-up text.
     */
    public static function apply($markup, $text, $trim)
    {
        return $markup->format(self::markupToHtml($text, $trim, false));
    }

    // signature for HTML entries
    const HTML_MARKER = '<!--HTML-->';

    // signature for HTML fallback entries
    const HTML_MARKER_FALLBACK = '<!-- HTML: Insert text after this line only. -->';

    // regular expression for detecting HTML signature
    const HTML_MARKER_REGEXP = '/^\s*<!--\s*HTML.*?-->/i';

    /**
     * Return `true` if the WYSIWYG editor is enabled for this user.
     *
     * @return boolean  `true` if the editor is enabled.
     */
    public static function editorEnabled()
    {
        return \Config::get()->WYSIWYG && !$GLOBALS['user']->cfg->WYSIWYG_DISABLED;
    }

    /**
     * Return `true` for HTML code and `false` for plain text.
     *
     * HTML code must either match `HTML_MARKER_REGEXP` or begin
     * with '<' and end with '>' (leading and trailing whitespace
     * is ignored). Everything else is considered to be plain
     * text.
     *
     * @param string $text  HTML code or plain text.
     *
     * @return boolean  `true` for HTML code, `false` for plain text.
     */
    public static function isHtml($text)
    {
        return self::hasHtmlMarker($text);
    }

    /**
     * Return `true` for Stud.IP-HTML and `false` otherwise.
     *
     * Stud.IP-HTML is HTML that can contain Stud.IP Markup.
     *
     * Stud.IP-HTML must match Stud.IP 3.2's HTML marker.
     * Leading and trailing whitespace is ignored.
     *
     * Everything else is considered not Stud.IP-HTML. In other
     * words, if it's not Stud.IP-HTML it might be everything
     * from plain text to binary code. But usually it's either
     * Stud.IP markup or plain HTML code, then.
     *
     * @param string $text  Text that is or isn't Stud.IP-HTML.
     *
     * @return boolean  `true` for Stud.IP-HTML
     */
    public static function isHtmlFallback($text)
    {
        $text = trim($text);

        // it's not fallback if the new HTML marker is detected
        if (MarkupPrivate\Text\startsWith($text, self::HTML_MARKER)) {
            return false;
        }

        // it's Stud.IP-HTML if Stud.IP 3.2's HTML marker is detected
        if (MarkupPrivate\Text\startsWith($text, self::HTML_MARKER_FALLBACK)) {
            return true;
        }

        return false;
    }

    /**
     * Return `true` for HTML code and `false` for plain text.
     *
     * HTML code must start with a match for `HTML_MARKER_REGEXP`.
     *
     * @param string $text  HTML code or plain text.
     *
     * @return boolean  `true` for HTML code, `false` for plain text.
     */
    public static function hasHtmlMarker($text)
    {
        return preg_match(self::HTML_MARKER_REGEXP, $text);
    }

    /**
     * Mark a given text as HTML code.
     *
     * No sanity-checking is done on the given text. It is simply
     * marked up so to be identified by Markup::isHtml as HTML
     * code.
     *
     * @param string $text  The text to be marked up as HTML code.
     *
     * @return string  The text marked up as HTML code.
     */
    public static function markAsHtml($text)
    {
        // NOTE keep this function in sync with the JavaScript
        // function markAsHtml in WyswygHtmlHead.php
        if (self::hasHtmlMarker($text) || trim($text) === '') {
            return $text; // marker already set, don't set twice
        }
        return self::HTML_MARKER . PHP_EOL . $text;
    }

    /**
     * Apply markup rules after running text through HTML ready.
     *
     * @param TextFormat $markup  Markup rules applied on marked-up text.
     * @param string     $text    Marked-up text on which rules are applied.
     * @param boolean    $trim    Trim text before applying markup rules, if TRUE.
     *
     * @return string  HTML code computed from marked-up text.
     */
    private static function markupHtmlReady($markup, $text, $trim)
    {
        return str_replace("\n", '<br>', self::markupText(
            $markup, self::htmlReady(self::unixEOL($text), $trim)));
    }

    /**
     * Convert line break to Unix format.
     *
     * @param string $text  Text with possibly mixed line breaks (Win, Mac, Unix).
     *
     * @return string  Text with Unix line breaks only.
     */
    private static function unixEOL($text)
    {
        return preg_replace("/\r\n?/", "\n", $text);
    }

    /**
     * Apply markup rules on plain text.
     *
     * @param TextFormat $markup  Markup rules applied on marked-up text.
     * @param string     $text    Marked-up text on which rules are applied.
     *
     * @return string  HTML code computed from marked-up text.
     */
    private static function markupText($markup, $text)
    {
        return symbol(smile($markup->format($text), false));
    }

    /**
     * Call HTMLPurifier to create safe HTML.
     *
     * @param   string  $dirty_html  Unsafe or 'uncleaned' HTML code.
     * @param   boolean $autoformat  Apply the AutoFormat rules
     * @return  string              Clean and safe HTML code.
     */
    private static function purify($dirty_html, $autoformat = true)
    {
        $purifier = self::createPurifier($autoformat);

        return $purifier->purify($dirty_html);
    }

    /**
     * Call HTMLPurifier to filter the HTML code (if the source is detected
     * to contain HTML, returns the argument unchanged otherwise). The HTML
     * marker is restored afterwards, if it was present.
     *
     * @param   string  $dirty_html  Unsafe or 'uncleaned' HTML code.
     * @return  string              Clean and safe HTML code.
     */
    public static function purifyHtml($html)
    {
        if ($html instanceof \I18NString) {
            $base = self::purifyHtml($html->original());
            $lang = $html->toArray();

            foreach ($lang as &$value) {
                $value = self::purifyHtml($value);
            }

            return new \I18NString($base, $lang);
        }

        if (self::isHtml($html)) {
            $html = self::markAsHtml(self::purify($html));
        }

        return $html;
    }

    /**
     * Create HTML purifier instance with Stud.IP-specific configuration.
     *
     * @param  boolean $autoformat  Apply the AutoFormat rules
     * @return HTMLPurifier A new instance of the HTML purifier.
     */
    private static function createPurifier($autoformat)
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', $GLOBALS['TMP_PATH']);
        $config->set('Core.RemoveInvalidImg', true);

        // restrict allowed HTML tags and attributes
        //
        // note that changes here should also be reflected in CKEditor's
        // settings!!
        //
        // NOTE The list could be restricted even further by allowing only
        // specific values for some attributes and CSS styles, but that is not
        // directly supported by HTMLPurifier and would need to be implemented
        // with a filter similar to ClassifyLinks.
        //
        // This is a list of further restrictions that can/should be introduced
        // at a later time point maybe, if possible:
        //
        // - always open external links in a new tab or window
        //   a[class="link-extern" href="..." target="_blank"]
        // - only allow left margin and horizontal text alignment to be set in
        //   divs (NOTE maybe remove these two features completely?):
        //   div[style="margin-left:(40|80|...)px; text-align:(center|right|justify)"]
        // - img[style] should only allow float:left or float:right
        // - only allow text color and background color to be set in a span's
        //   style attribute (NOTE 'wiki-links' are currently set here due to
        //   implementation difficulties, but probably this should be
        //   changed...):
        //   span[style="color:(#000000|#800000|...);
        //               background-color:(#000000|#800000|...)"
        //        class="wiki-link"]
        // - tables should always have the class "content" (it should not be
        //   optional and no other class should be set):
        //   table[class="content"]
        // - table headings should have a column and/or a row scope or no scope
        //   at all, but nothing else:
        //   th[scope="(col | row)"]
        // - fonts: only Stud.IP-specific fonts should be allowed
        //
        $config->set('HTML.Allowed', '
            a[class|href|target|rel|name|id]
            audio[controls|src|height|width|style]
            big
            blockquote
            br
            caption
            code
            div[class|style]
            em
            h1
            h2
            h3
            h4
            h5
            h6
            hr
            img[alt|src|height|width|style]
            li
            ol
            p[style]
            pre[class]
            span[style|class]
            strong
            u
            ul
            s
            small
            sub
            sup
            table[class]
            tbody
            td[colspan|rowspan|style]
            thead
            th[colspan|rowspan|style|scope]
            tr
            tt
            video[controls|src|height|width|style]
        ');

        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('Attr.AllowedRel', ['nofollow']);
        $config->set('Attr.EnableID', true);
        $config->set('Attr.AllowedClasses', [
            'author',
            'content',
            'link-extern',
            'link-intern',
            'math-tex',
            'usercode',
            'wiki-link'
        ]);
        $config->set('CSS.AllowedFonts', [
            'serif',
            'sans-serif',
            'monospace',
            'cursive'
        ]);
        $config->set('CSS.AllowedProperties', [
            'margin-left',
            'text-align',
            'width',
            'height',
            'color',
            'background-color', // needed by span, td
            'float'
        ]);

        if ($autoformat) {
            $config->set('AutoFormat.Linkify', true);
            $config->set('AutoFormat.Custom', [
                'ClassifyLinks',
                'ClassifyTables',
                'LinkifyEmail'
            ]);
            $config->set('AutoFormat.RemoveSpansWithoutAttributes', true);
        } else {
            $config->set('AutoFormat.Custom', ['TransformLinks']);
        }

        // avoid <img src="evil_CSRF_stuff">
        $def = $config->getHTMLDefinition(true);
        $img = $def->addBlankElement('img');
        $img->attr_transform_post[]
            = new MarkupPrivate\Purifier\AttrTransform_Image_Source();

        $def->addElement('audio', 'Inline', 'Flow', 'Common', [
              'src*' => 'URI',
              'width' => 'Length',
              'height' => 'Length',
              'controls' => 'Text',     // Bool triggers bug in HTMLPurifier
        ]);

        $def->addElement('video', 'Inline', 'Flow', 'Common', [
              'src*' => 'URI',
              'width' => 'Length',
              'height' => 'Length',
              'controls' => 'Text',     // Bool triggers bug in HTMLPurifier
        ]);

        return new \HTMLPurifier($config);
    }

    /**
     * Convert special characters to HTML entities, and clean up.
     *
     * @param  string  $text  This text's special chars will be converted.
     * @param  boolean $trim  Trim text before applying markup rules, if TRUE.
     * @param  boolean $br    Replace newlines by <br>, if TRUE.
     * @param  boolean $double_encode  Encode existing HTML entities, if TRUE.
     * @return string         The converted string.
     */
    public static function htmlReady(
        $text, $trim = true, $br = false, $double_encode = true
    ) {
        $text = htmlspecialchars($text, ENT_QUOTES, 'utf-8', $double_encode);

        if ($trim) {
            $text = trim($text);
        }
        if ($br) { // fix newlines
            $text = nl2br($text, false);
        }
        return $text;
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
    public static function wysiwygReady(
        $text, $trim = true, $br = false, $double_encode = true
    ) {
        if (self::editorEnabled()) {
            $text = self::markupToHtml($text, $trim);
        }
        return self::htmlReady($text, $trim, $br, $double_encode);
    }

    /**
     * Convert Stud.IP markup (possibly mixed with HTML if fallback mode is
     * enabled) to editable HTML. Pure HTML will only run through the purifier.
     *
     * @param  string  $text  The text.
     * @param  boolean $trim  Trim text before applying markup rules, if TRUE.
     * @param  boolean $mark  Mark result text as HTML, if TRUE.
     * @return string         The converted string.
     */
    public static function markupToHtml($text, $trim = true, $mark = true)
    {
        if (!trim($text)) {
            return $text;
        }
        if (self::isHtml($text)) {
            $is_fallback = self::isHtmlFallback($text);
            $text = self::purify($text, false);

            if ($is_fallback) {
                $text = self::markupText(new \StudipCoreFormat(), $text);
            }
        } else {
            $text = self::markupHtmlReady(new \StudipCoreFormat(), $text, $trim);
        }

        return $mark ? self::markAsHtml($text) : $text;
    }

    /**
     * Call HTMLPurifier to remove all HTML tags from the string (if the source
     * is detected to contain HTML, returns the argument unchanged otherwise).
     *
     * @param   string  $html  HTML code to filter
     * @return  string         The converted string.
     */
    public static function removeHtml($html)
    {
        if (self::isHtml($html)) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Cache.SerializerPath', $GLOBALS['TMP_PATH']);
            $config->set('HTML.Allowed', 'a[href],img[alt|src],br');
            $config->set('AutoFormat.Custom', ['Unlinkify']);

            $purifier = new \HTMLPurifier($config);
            $html = $purifier->purify($html);
            $html = \decodeHTML(trim(str_replace('<br />', PHP_EOL, $html)));
        }

        return $html;
    }
}

/**
 * Members of Studip\MarkupPrivate must not be used outside of this file!!
 */

namespace Studip\MarkupPrivate\Purifier;

use Studip\MarkupPrivate\MediaProxy;

/**
 * Remove invalid <img src> attributes.
 */
class AttrTransform_Image_Source extends \HTMLPurifier_AttrTransform
{
    /**
     * Implements abstract method of base class.
     */
    function transform($attr, $config, $context)
    {
        try {
            $attr['src'] = MediaProxy\getMediaUrl($attr['src']);
        } catch (MediaProxy\InvalidInternalLinkException $e) {
            // invalid internal link ==> remove <img src> attribute
            $GLOBALS['msg'][] = _('Ungültige interne Medienverknüpfung entfernt: ')
                . \htmlentities($e->getUrl());
            $attr['src'] = NULL; // remove <img src> attribute
        } catch (MediaProxy\ExternalMediaDeniedException $e) {
            $GLOBALS['msg'][] = _('Verbotene externe Medienverknüpfung entfernt: ')
                . \htmlentities($e->getUrl());
            $attr['src'] = NULL; // remove <img src> attribute
        }
        return $attr;
    }
}

//// media proxy //////////////////////////////////////////////////////////////

namespace Studip\MarkupPrivate\MediaProxy;

use Studip\MarkupPrivate\Text;

/**
 * Check if media proxy should be used and if so return the respective URL.
 *
 * @param string $url   URL to media file.
 * @return mixed        URL string to media file (possibly 'proxied')
 *                      or NULL if URL is invalid.
 */
function getMediaUrl($url) {
    // even though proxied URLs shouldn't be stored in the database, the
    // next line will handle those cases where they're accidentally there
    $url = decodeMediaProxyUrl($url);

    // handle internal media links
    if (isStudipMediaUrl($url)) {
        return transformInternalIdnaLink($url);
    }
    if (isInternalLink($url)) {
        // link is studip-internal, but not to a valid media location
        throw new InvalidInternalLinkException($url);
    }

    // handle external media links
    $external_media = \Config::get()->LOAD_EXTERNAL_MEDIA;
    if ($external_media === 'proxy' &&
        \Seminar_Session::is_current_session_authenticated()
    ) {
        // media proxy must be accessed by an internal link
        return encodeMediaProxyUrl($url);
    }
    if ($external_media === 'allow') {
        return $url;
    }
    throw new ExternalMediaDeniedException($url);
}

/**
 * Return media proxy URL for an unproxied URL.
 *
 * @params string $url  Unproxied media URL.
 * @return string       Media proxy URL for accessing the same resource.
 */
function encodeMediaProxyUrl($url) {
    return transformInternalIdnaLink(
        getMediaProxyUrl() .'?url=' . \urlencode(\idna_link($url)));
}

/**
 * Extract the original URL from a media proxy URL.
 *
 * @param string  $url  The media proxy URL.
 * return string  The original URL. If $url does not point to the media
 *                proxy then this is the exact same value given by $url.
 */
function decodeMediaProxyUrl($url) {
    # TODO make it work for 'url=' at any position in query
    $urlpath = removeStudipDomain($url);
    $proxypath = removeStudipDomain(getMediaProxyUrl()) . '?url=';
    if (Text\startsWith($urlpath, $proxypath)) {
        return \urldecode(Text\removePrefix($urlpath, $proxypath));
    }
    return $url;
}

/**
 * Return Stud.IP's absolute media proxy URL.
 */
function getMediaProxyUrl() {
    return $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'dispatch.php/media_proxy';
}

/**
 * Test if an URL points to a valid internal Stud.IP media path.
 *
 * @param   string   $url  Internal Stud.IP URL.
 * @returns boolean  TRUE for internal media link URLs, FALSE otherwise.
 */
function isStudipMediaUrl($url) {
    return isInternalLink($url) &&
        isStudipMediaUrlPath(getStudipRelativePath($url));
}

function isInternalLink($url) {
    return is_internal_url(transformInternalIdnaLink($url));
}

//// url utilities ////////////////////////////////////////////////////////////

/**
 * Remove domain name from internal URLs.
 *
 * Remove scheme, domain and authentication information from internal
 * Stud.IP URLs. Leave external URLs untouched.
 *
 * @param string $url   URL from which to remove internal domain.
 * @returns string      URL without internal domain or the exact same
 *                      value as $url for external URLs.
 */
function removeStudipDomain($url) {
    if (!isInternalLink($url)) {
        return $url;
    }
    $parsed_url = \parse_url(transformInternalIdnaLink($url));
    $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
    $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
    $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
    return $path . $query . $fragment;
}

/**
 * Return a URL's path component with the absolute Stud.IP path removed.
 *
 * NOTE: If the URL is not an internal Stud.IP URL, the path component will
 * nevertheless be returned without issuing an error message.
 *
 * Example:
 * >>> getStudipRelativePath('http://localhost:8080'
 *      . '/studip/sendfile.php?type=0&file_id=ABC123&file_name=nice.jpg')
 * 'sendfile.php'
 *
 * @param string $url   The URL from which to return the Stud.IP-relative
 *                      path component.
 * returns string Stud.IP-relative path component of $url.
 */
function getStudipRelativePath($url) {
    $parsed_url = \parse_url(transformInternalIdnaLink($url));
    $parsed_studip_url = getParsedStudipUrl();
    return Text\removePrefix($parsed_url['path'], $parsed_studip_url['path']);
}

/**
 * Return an associative array containing the Stud.IP URL elements.
 *
 * see also: http://php.net/manual/en/function.parse-url.php
 *
 * @returns mixed  Same values that PHP's parse_url() returns.
 */
function getParsedStudipUrl() {
    return \parse_url($GLOBALS['ABSOLUTE_URI_STUDIP']);
}

/**
 * Test if path is valid for internal Stud.IP media URLs.
 *
 * @params string $path The path component of an URL.
 * return boolean       TRUE for valid media paths, FALSE otherwise.
 */
function isStudipMediaUrlPath($path) {
    list($path_head) = \explode('/', $path);
    $valid_paths = ['sendfile.php', 'download', 'assets', 'pictures'];
    return \mb_strpos(\urldecode($path), '../') === false && \in_array($path_head, $valid_paths);
}

/**
 * Return a normalized, internal URL.
 *
 * @params string $url  An internal URL.
 * @returns string      Normalized internal URL.
 */
function transformInternalIdnaLink($url) {
    return \idna_link(\TransformInternalLinks($url));
}

//// url exceptions ///////////////////////////////////////////////////////////

class UrlException extends \Exception
{
    private $url;

    public function __construct($url) {
        parent::__construct();
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }
}

class InvalidInternalLinkException extends UrlException
{
}

class ExternalMediaDeniedException extends UrlException
{
}

//// string utilities /////////////////////////////////////////////////////////

namespace Studip\MarkupPrivate\Text;

/**
 * Test if string starts with prefix.
 *
 * @param string $string  Tested string.
 * @param string $prefix  Prefix of tested string.
 *
 * @return boolean  TRUE if string starts with prefix.
 */
function startsWith($string, $prefix) {
    return \mb_substr($string, 0, \mb_strlen($prefix)) === $prefix;
}

/**
 * Test if string ends with suffix.
 *
 * @param string $string  Tested string.
 * @param string $suffix  Suffix of tested string.
 *
 * @return boolean  TRUE if string ends with suffix.
 */
function endsWith($string, $suffix) {
    return \mb_substr($string, - \mb_strlen($suffix)) === $suffix;
}

/**
 * Remove prefix from string.
 *
 * Does not change the string if it has a different prefix.
 *
 * @param string $string The string that must start with the prefix.
 * @param string $prefix The prefix of the string.
 *
 * @return string String without prefix.
 */
function removePrefix($string, $prefix) {
    return startsWith($string, $prefix) ? \mb_substr($string, \mb_strlen($prefix)) : $string;
}
