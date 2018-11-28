<?php
/**
 * fakeserver.php - Helper functions for faking a server in unit tests.
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

# common set-up, usually done by lib/bootstraph.php and
# config/config_local.inc.php when run on web server
$STUDIP_BASE_PATH = realpath(dirname(__FILE__) . '/../..');
$ABSOLUTE_PATH_STUDIP = $STUDIP_BASE_PATH . '/public/';

/**
 * Set various variables that are usually present when running Stud.IP on a 
 * server.
 *
 * @param $uri      string          URI to "opened" site on faked server.
 * @param $domains  string-array    Value to which $STUDIP_DOMAINS is set.
 */
function fakeServer($uri, $domains) {
    global $STUDIP_DOMAINS;

    fakeServerGlobals($uri);
    $STUDIP_DOMAINS = $domains;
    unset($GLOBALS['TransformInternalLinks_domainData']);
    computeRelativePath();
    computeAbsoluteURI();
}

/**
 * Set various PHP globals that are usually present on a server.
 * @param $uri string  URI to "opened" site on faked server.
 */
function fakeServerGlobals($uri) {
    $urlComponents = parse_url($uri);

    if (isset($urlComponents['host'])) {
        $_SERVER['SERVER_NAME'] = $urlComponents['host'];
        $_SERVER['HTTP_HOST'] = $urlComponents['host'];
    }

    $_SERVER['HTTPS'] = false;
    $_SERVER['SERVER_PORT'] = 80;
    if (isset($urlComponents['scheme'])
        && mb_strtolower($urlComponents['scheme']) == 'https'
    ) {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = 443;
    }

    if (isset($urlComponents['port'])) {
        $_SERVER['SERVER_PORT'] = $urlComponents['port'];
    }

    $path = '';
    if (isset($urlComponents['path'])) {
        $_SERVER['PHP_SELF'] = $urlComponents['path'];
        $path = $urlComponents['path'];
    }

    $query = isset($urlComponents['query']) ? ('?' . $urlComponents['query']) : '';
    $fragment = isset($urlComponents['fragment']) ? ('#' . $urlComponents['fragment']) : '';
    $_SERVER['REQUEST_URI'] = $path . $query . $fragment;

    if (!isset(Config::get()->CONVERT_IDNA_URL)) {
        Config::get()->CONVERT_IDNA_URL = false;
    }
}

/**
 * Compute $CANONICAL_RELATIVE_PATH_STUDIP. Must be called after fakeserver().
 */
function computeRelativePath() {
    global $_SERVER, $CANONICAL_RELATIVE_PATH_STUDIP;

    // code copied from config/config_local.inc.php
    $CANONICAL_RELATIVE_PATH_STUDIP = dirname($_SERVER['PHP_SELF']);
    if (DIRECTORY_SEPARATOR != '/') {
        $CANONICAL_RELATIVE_PATH_STUDIP = str_replace(
            DIRECTORY_SEPARATOR, '/', $CANONICAL_RELATIVE_PATH_STUDIP
        );
    }

    if (mb_substr($CANONICAL_RELATIVE_PATH_STUDIP, -1) != '/') {
        $CANONICAL_RELATIVE_PATH_STUDIP .= '/';
    }
}

/**
 * Computes $ABSOLUTE_URI_STUDIP. Must be called after computeRelativePath.
 */
function computeAbsoluteURI() {
    global $_SERVER, $ABSOLUTE_URI_STUDIP, $CANONICAL_RELATIVE_PATH_STUDIP;

    // code copied from config/config_local.inc.php
    if (isset($_SERVER['SERVER_NAME'])) {
        // work around possible bug in lighttpd
        if (mb_strpos($_SERVER['SERVER_NAME'], ':') !== false) {
            list($_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT']) =
                explode(':', $_SERVER['SERVER_NAME']);
        }

        $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on';
        $ABSOLUTE_URI_STUDIP = $https ? 'https' : 'http';
        $ABSOLUTE_URI_STUDIP .= '://'.$_SERVER['SERVER_NAME'];

        if ($https && $_SERVER['SERVER_PORT'] != 443 ||
            !$https && $_SERVER['SERVER_PORT'] != 80
        ) {
            $ABSOLUTE_URI_STUDIP .= ':'.$_SERVER['SERVER_PORT'];
        }

        $ABSOLUTE_URI_STUDIP .= $CANONICAL_RELATIVE_PATH_STUDIP;
    }
}

/**
 * Utility to help with debugging unit test creation.
 * Should not be called in working unit tests.
 */
function echoWebGlobals()
{
    echo PHP_EOL . "base path\t" . $GLOBALS['STUDIP_BASE_PATH'];
    echo PHP_EOL . "PHP self\t" . $_SERVER['PHP_SELF'];
    echo PHP_EOL . "relative path\t" . $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']; 
    echo PHP_EOL . "server name\t" . $_SERVER['SERVER_NAME'];
    echo PHP_EOL . "server port\t" . $_SERVER['SERVER_PORT'];
    echo PHP_EOL . "HTTPS\t\t" . $_SERVER['HTTPS'];
    echo PHP_EOL . "absolute URI\t" . $GLOBALS['ABSOLUTE_URI_STUDIP'];
}

