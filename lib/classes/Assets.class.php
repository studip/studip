<?php
# Lifter002: TODO
# Lifter005: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO

/*
 * Assets.class.php - assets helper
 *
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * This class is used to construct URLs for static content like images,
 * stylesheets or javascripts. As the URL to the "assets" directory is
 * configurable one always has to construct the above mentioned URLs
 * dynamically.
 *
 * Example:
 *
 *     # construct the URL for the image "blank.gif"
 *     $url = Assets::image_path('blank.gif');
 *
 * @package   studip
 *
 * @author    mlunzena
 * @copyright (c) Authors
 */
class Assets
{

    const NUMBER_OF_ALIASES = 2;

    /**
     * @ignore
     */
    private static $assets_url, $dynamic, $counter_cache;


    /**
     * This method sets the URL to your assets.
     *
     * @param  string       the URL to the assets
     *
     * @return void
     */
    static function set_assets_url($url)
    {
        Assets::$assets_url = $url;
        Assets::$counter_cache = NULL;
        Assets::$dynamic = mb_strpos($url, '%d') !== FALSE;
    }


    /**
     * This class method is an accessor to the URL "prefix" for all things "asset"
     * Prepend the return value of this method to the relative path of the wanted
     * static content.
     *
     * Additionally if the ASSETS_URL contains the string '%d', it will be
     * replaced with a random number between 0 and 3. If you passed an argument
     * this number will not be random but specific to that argument thus being
     * referentially transparent.
     *
     * Example:
     *
     *  # static ASSETS_URL
     *  $ASSETS_URL = 'http://www.example.com/public/';
     *  echo Assets::url() . 'javascripts/prototype.js' . "\n";
     *  echo Assets::url('javascripts/prototype.js')    . "\n";
     *
     *  # output
     *  http://www.example.com/public/javascripts/prototype.js
     *  http://www.example.com/public/javascripts/prototype.js
     *
     *
     *  # dynamic ASSETS_URL
     *  $ASSETS_URL = 'http://www%d.example.com/public/';
     *  echo Assets::url() . 'javascripts/prototype.js' . "\n";
     *  echo Assets::url() . 'javascripts/prototype.js' . "\n";
     *  echo Assets::url() . 'javascripts/prototype.js' . "\n";
     *  echo Assets::url('javascripts/prototype.js')    . "\n";
     *  echo Assets::url('javascripts/prototype.js')    . "\n";
     *  echo Assets::url('javascripts/prototype.js')    . "\n";
     *
     *  # output
     *  http://www0.example.com/public/javascripts/prototype.js
     *  http://www1.example.com/public/javascripts/prototype.js
     *  http://www2.example.com/public/javascripts/prototype.js
     *  http://www1.example.com/public/javascripts/prototype.js
     *  http://www1.example.com/public/javascripts/prototype.js
     *  http://www1.example.com/public/javascripts/prototype.js
     *
     *
     * @param string an optional suffix which is used to construct a number if
     *               ASSETS_URL is dynamic (contains '%d')
     *
     * @return string the URL "prefix"
     */
    static function url($to = '')
    {

        if (!Assets::$dynamic)
            return Assets::$assets_url . $to;

        # dynamic ASSETS_URL
        return sprintf(Assets::$assets_url,
            $to == ''
                ? Assets::$counter_cache++ % Assets::NUMBER_OF_ALIASES
                # alternative implementation
                # : hexdec(mb_substr(sha1($to),-1)) & 3)
                : ord($to[1]) & (Assets::NUMBER_OF_ALIASES - 1))

        . $to;
    }

    /**
     * Returns an image tag using options as html attributes on the
     * tag, but with these special cases:
     *
     * 'alt'  - If no alt text is given, the file name part of the $source is used
     *   (capitalized and without the extension)
     * * 'size' - Supplied as "X@Y", so "30@45" becomes width="30" and height="45"
     *
     * The source can be supplied as a...
     * * full path, like "/my_images/image.gif"
     * * file name, like "rss.png", that gets expanded to "/images/rss.png"
     * * file name without extension, like "logo", that gets expanded to "/images/logo.png"
     *
     * Do not use this to render icons. Use the more appropiate class
     * Icon for this.
     */
    static function img($source, $opt = [])
    {
        if (!$source) {
            return '';
        }

        $parts = explode('/', $source);

        $size = $opt['size'];

        $opt = Assets::parse_attributes($opt);

        $opt['src'] = Assets::image_path($source);

        if (!isset($opt['alt'])) {
            $opt['alt'] = ucfirst(current(explode('.', basename($opt['src']))));
        }

        if (isset($size) && !isset($opt['width'])) {
            list($opt['width'], $opt['height']) = explode('@', $size, 2);
            unset($opt['size']);
        }

        return Assets::tag('img', $opt);
    }


    /**
     * Returns an input tag using options as html attributes on the
     * tag, but with these special cases:
     *
     * * 'size' - Supplied as "X@Y", so "30@45" becomes width="30" and height="45"
     *
     * The source can be supplied as a...
     * * full path, like "/my_images/image.gif"
     * * file name, like "rss.png", that gets expanded to "/images/rss.png"
     * * file name without extension, like "logo", that gets expanded to "/images/logo.png"
     *
     * Do not use this to render icons. Use the more appropiate class
     * Icon for this.
     */
    static function input($source, $opt = [])
    {

        if (!$source) {
            return '';
        }

        $parts = explode('/', $source);

        $size = $opt['size'];

        $opt = Assets::parse_attributes($opt);

        $opt['src'] = Assets::image_path($source);
        $opt['type'] = 'image';

        if (isset($size) && !isset($opt['width'])) {
            list($opt['width'], $opt['height']) = explode('@', $size, 2);
            unset($opt['size']);
        }

        return Assets::tag('input', $opt);
    }

    /**
     * Returns path to an image asset.
     *
     * Example:
     *
     * The src can be supplied as a...
     *
     * full path,
     *   like "/my_images/image.gif"
     *
     * file name,
     *   like "rss.png", that gets expanded to "/images/rss.png"
     *
     * file name without extension,
     *   like "logo", that gets expanded to "/images/logo.png"
     *
     * Note: This function should be private/depracated for the use in other
     * scripts, as we would like to always generate the complete <image> oder
     * <input> tag. Please use Assets::img or Assets::input instead.
     */
    static function image_path($source, $respect_retina = false)
    {
        $path = Assets::compute_public_path($source, 'images', 'png');

        return $path;
    }

    /**
     * Returns a script include tag per source given as argument.
     *
     * Examples:
     *
     *   Assets::script('prototype') =>
     *     <script src="/javascript/prototype.js"></script>
     *
     *   Assets::script('common.javascript', '/elsewhere/cools') =>
     *     <script src="/js/common.javascript"></script>
     *     <script src="/elsewhere/cools.js"></script>
     */
    static function script($atLeastOneArgument)
    {
        $html = '';
        foreach (func_get_args() as $source) {
            $source = Assets::javascript_path($source);
            $html .= Assets::content_tag('script', '',
                ['src' => $source]);
            $html .= "\n";
        }

        return $html;
    }


    /**
     * Returns path to a javascript asset.
     *
     * Example:
     *
     *   Assets::javascript_path('ajax') => /javascripts/ajax.js
     */
    static function javascript_path($source)
    {
        return Assets::compute_public_path($source, 'javascripts', 'js');
    }


    /**
     * Returns a css link tag per source given as argument.
     *
     * Examples:
     *
     *   Assets::stylesheet('style') =>
     *     <link href="/stylesheets/style.css" media="screen" rel="stylesheet">
     *
     *   Assets::stylesheet('style', array('media' => 'all'))  =>
     *     <link href="/stylesheets/style.css" media="all" rel="stylesheet">
     *
     *   Assets::stylesheet('random.styles', '/css/stylish') =>
     *     <link href="/stylesheets/random.styles" media="screen" rel="stylesheet">
     *     <link href="/css/stylish.css" media="screen" rel="stylesheet">
     */
    static function stylesheet($atLeastOneArgument)
    {
        $sources = func_get_args();
        $sourceOptions = (func_num_args() > 1 &&
            is_array($sources[func_num_args() - 1]))
            ? array_pop($sources)
            : [];

        $html = '';
        foreach ($sources as $source) {
            $source = Assets::stylesheet_path($source);
            $opt = array_merge(['rel' => 'stylesheet',
                    'media' => 'screen',
                    'href' => $source],
                $sourceOptions);
            $html .= Assets::tag('link', $opt) . "\n";
        }

        return $html;
    }


    /**
     * Returns path to a stylesheet asset.
     *
     * Example:
     *
     *   stylesheet_path('style') => /stylesheets/style.css
     */
    static function stylesheet_path($source)
    {
        return Assets::compute_public_path($source, 'stylesheets', 'css');
    }


    /**
     * This function computes the public path to the given source by using default
     * dir and ext if not specified by the source. If source is not an absolute
     * URL, the assets url is incorporated.
     *
     * @ignore
     */
    private static function compute_public_path($source, $dir, $ext)
    {

        # add extension if not present
        if ('' == mb_substr(mb_strrchr($source, "."), 1))
            $source .= ".$ext";

        # if source is not absolute
        if (FALSE === mb_strpos($source, ':')) {

            # add dir if url does not contain a path
            if ('/' !== $source[0])
                $source = "$dir/$source";

            # consider asset host
            $source = Assets::url(ltrim($source, '/'));
        }

        return $source;
    }


    /**
     * Constructs an html tag.
     *
     * @ignore
     *
     * @param  string  tag name
     * @param  array   tag options
     * @param  boolean true to leave tag open
     *
     * @return string
     */
    private static function tag($name, $options = [], $open = FALSE)
    {
        if (!$name)
            return '';
        ksort($options);
        return '<' . $name . ' ' . arrayToHtmlAttributes($options) . ($open ? '>' : '>');
    }


    /**
     * Helper function for content tags.
     *
     * @param name    tag name
     * @param content tag content
     * @param options tag options
     *
     * @return type <description>
     */
    private static function content_tag($name, $content = '', $options = [])
    {
        if (!$name) return '';
        return '<' . $name . ' ' . arrayToHtmlAttributes($options) . '>' .
        $content .
        '</' . $name . '>';
    }

    /**
     * Parse a HTML attribute string into an array.
     *
     * @ignore
     */
    private static function parse_attributes($stringOrArray)
    {

        if (is_array($stringOrArray))
            return $stringOrArray;

        preg_match_all('/
      \s*(\w+)              # key                               \\1
      \s*=\s*               # =
      (\'|")?               # values may be included in \' or " \\2
      (.*?)                 # value                             \\3
      (?(2) \\2)            # matching \' or " if needed        \\4
      \s*(?:
        (?=\w+\s*=) | \s*$  # followed by another key= or the end of the string
      )
    /x', $stringOrArray, $matches, PREG_SET_ORDER);

        $attributes = [];
        foreach ($matches as $val)
            $attributes[$val[1]] = $val[3];

        return $attributes;
    }
}

