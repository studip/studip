<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
 * URLHelper.php - utility functions for URL parameter handling
 *
 * Copyright (c) 2008  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * The URLHelper class provides several utility functions (as class
 * methods) to ease the transition from using session data to URL
 * parameters.
 *
 * The most important method is URLHelper::getLink(), which appends
 * a number of additional parameters to a given URL. The parameters
 * can be set using the addLinkParam() or bindLinkParam() methods.
 */
class URLHelper
{
    /**
     * array of registered parameter values (initially empty)
     */
    private static $params = [];

    /**
     * base URL for all links generated from relative URLs
     */
    private static $base_url;

    /**
     * Set a base URL to be used when resolving relative URLs passed
     * to URLHelper::getLink() and URLHelper::getURL(). Set this to
     * NULL to use no base URL and skip the URL resolving step.
     *
     * @param string $url  relative or absolute URL (or NULL)
     * @return string old URL
     */
    static function setBaseURL ($url)
    {
        $old = self::$base_url;
        self::$base_url = $url;
        return $old;
    }

    /**
     * Resolve the given relative or absolute URL relative to the
     * currently defined base URL (if set). This is a private method.
     *
     * @param string $url    relative or absolute URL
     *
     * @return string modified URL
     */
    private static function resolveURL ($url)
    {
        $base_url = self::$base_url;

        if (!isset($base_url) ||
            preg_match('%^[a-z]+:%', $url)) {
            return $url;
        }

        if ($url[0] === '/') {
            preg_match('%^[a-z]+://[\w:\.\-]+%', $base_url, $host);
            $base_url = isset($host) ? $host[0] : '';
        }

        return $base_url.$url;
    }

    /**
     * Add a new link parameter. If a parameter with this name already
     * exists, its value will be replaced with the new one. All link
     * parameters will be included in the link returned by getLink().
     *
     * @param string $name  parameter name
     * @param mixed  $value parameter value
     */
    static function addLinkParam ($name, $value)
    {
        self::$params[$name] = $value;
    }

    /**
     * Bind a new link parameter to a variable. If a parameter with this
     * name already exists, its value will re replaced with the binding.
     *
     * This method differs from addLinkParam() in two respects:
     *
     * - The bound variable is initialized with the parameter value in
     *   the current request.
     * - The parameter value is the value of the bound variable at the
     *   time getLink() is called.
     *
     * @param string $name  parameter name
     * @param mixed  $var   variable to bind
     */
    static function bindLinkParam ($name, &$var)
    {
        if (isset($_REQUEST[$name])) {
            $var = $_REQUEST[$name];
        }

        self::$params[$name] = &$var;
    }

    /**
     * Get the list of currently registered link parameters.
     *
     * @return array list of registered link parameters
     */
    static function getLinkParams ()
    {
        return self::$params;
    }

    /**
     * Remove a link parameter.
     *
     * @param string $name  parameter name
     */
    static function removeLinkParam ($name)
    {
        unset(self::$params[$name]);
    }

    /**
     * Augment the given URL by appending all registered link parameters.
     * Note that for each bound variable, its current value is used. You
     * can use the second parameter to add futher URL parameters to this
     * link without adding them globally. Any parameters included in the
     * argument list take precedence over registered link parameters of
     * the same name. This method is identical to getURL() except that it
     * returns an entity encoded URL suitable for use in HTML attributes.
     *
     * @param string $url    relative or absolute URL
     * @param array  $params array of additional link parameters to add
     * @param bool $ignore_registered_params do not add registered params
     *
     * @return string modified URL (entity encoded)
     */
    static function getLink ($url = '', $params = NULL, $ignore_registered_params = false)
    {
        return htmlReady(self::getURL($url, $params, $ignore_registered_params));
    }

    /**
     * Augment the given URL by appending all registered link parameters.
     * Note that for each bound variable, its current value is used. You
     * can use the second parameter to add futher URL parameters to this
     * link without adding them globally. Any parameters included in the
     * argument list take precedence over registered link parameters of
     * the same name.
     *
     * @param string $url    relative or absolute URL
     * @param array  $params array of additional link parameters to add
     * @param bool $ignore_registered_params do not add registered params
     *
     * @return string modified URL
     */
    static function getURL ($url = '', $params = NULL, $ignore_registered_params = false)
    {
        $link_params = $ignore_registered_params ? [] : self::$params;

        list($url, $fragment) = explode('#', $url);
        list($url, $query)    = explode('?', $url);

        if ($url !== '') {
            $url = self::resolveURL($url);
        }

        if (isset($query)) {
            parse_str($query, $query_params);
            $link_params = array_merge($link_params, $query_params);
        }

        if (isset($params)) {
            $link_params = array_merge($link_params, $params);
        }

        $query_string = http_build_query($link_params);

        if (mb_strlen($query_string) || $url === '') {
            $url .= '?'.$query_string;
        }

        if (isset($fragment)) {
            $url .= '#'.$fragment;
        }

        return $url;
    }

    /**
     * Augment the given URL by adding URL parameters from the second parameter,
     * without bound parameters
     *
     * @param string $url    relative or absolute URL
     * @param array  $params array of additional link parameters to add
     *
     * @return string modified URL
     */
    static function getScriptURL ($url = '', $params = NULL)
    {
        return self::getURL($url, $params, true);
    }

    /**
     * This method is identical to getScriptURL() except that it
     * returns an entity encoded URL suitable for use in HTML attributes.
     *
     * @param string $url    relative or absolute URL
     * @param array  $params array of additional link parameters to add
     *
     * @return string modified URL (entity encoded)
     */
    static function getScriptLink ($url = '', $params = NULL)
    {
        return self::getLink($url, $params, true);
    }
}
?>
