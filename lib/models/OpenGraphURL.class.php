<?php
/*
 * Copyright (C) 2013 - Rasmus Fuhse <fuhse@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * A model class to handle the database table "opengraphdata", fetch data from
 * an Opengraph-URL and render a fitting box with the opengraph information to
 * the user.
 * @property string url database column
 * @property string id alias column for url
 * @property string is_opengraph database column
 * @property string title database column
 * @property string image database column
 * @property string description database column
 * @property string type database column
 * @property string data database column
 * @property string last_update database column
 * @property string chdate database column
 * @property string mkdate database column
 */
class OpenGraphURL extends SimpleORMap
{
    const EXPIRES_DURATION = 86400; // = 24 * 60 * 60

    /**
     * Configures this model.
     *
     * @param Array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'opengraphdata';
        $config['serialized_fields']['data'] = 'JSONArrayObject';
        parent::configure($config);
    }

    /**
     * Create an instance of this model given url. Differs from findOneByURL
     * insofar that it will return a new object with the given url set
     * instead of null.
     *
     * @param String $url URL to find
     * @return OpenGraphURL Either existing instance or a new instance for
     *                      the given url
     */
    public static function fromURL($url)
    {
        $og = self::findOneByUrl($url);
        if (!$og) {
            $og = new self();
            $og->url = $url;
        }
        return $og;
    }

    /**
     * Constructor of the object. Provides a fallback if a url is passed
     * instead of the usually expected numeric id in order to not break
     * backward compatibility.
     * But this constructor will fail miserably if a url is passed that
     * is not in the database. This was chosen by design to encourage the
     * correct use of an id.
     *
     * @param mixed $id Numeric id, existing url or null
     */
    public function __construct($id = null)
    {
        // Try to find matching id when an url is passed instead of an id.
        // This is to ensure that no legacy code will immediately break.
        if ($id !== null && !ctype_digit($id)) {
            $temp = self::findOneByUrl($id);
            if ($temp) {
                $id = $temp->id;
            }
        }
        parent::__construct($id);
    }

    /**
     * Stores the object and fetches the opengraph information when either
     * the object is new or outdated.
     *
     * @return int Number of updated records
     */
    public function store()
    {
        if ($this->isNew() || $this->last_update < time() - self::EXPIRES_DURATION) {
            // Store last update timestamp BEFORE fetching so another thread
            // will not fetch again
            $this->last_update = time();
            parent::store();

            $this->fetch();
        }

        return parent::store();
    }

    /**
     * Fetches information from the url by getting the contents of the
     * webpage, parse the webpage and extract the information from the
     * opengraph meta-tags.
     * If the site doesn't have any opengraph-metatags it is in fact no
     * opengraph node and thus no data will be stored in the database.
     * Only $url['is_opengraph'] === '0' indicates that the site is no
     * opengraph node at all.
     *
     * @todo The combination of FileManager::fetchURLMetadata() and the following request
     *       leads to two requests for the open graph data. This should
     *       be fixed due to performance reasons.
     */
    public function fetch()
    {
        if (!Config::get()->OPENGRAPH_ENABLE) {
            return;
        }

        $response = FileManager::fetchURLMetadata($this['url']);
        if ($response['response_code'] == 200 && mb_strpos($response['Content-Type'],'html') !== false) {
            if (preg_match('/(?<=charset=)[^;]*/i', $response['Content-Type'], $match)) {
                $currentEncoding = trim($match[0], '"');
            } else {
                $currentEncoding = 'UTF-8';
            }

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => sprintf("User-Agent: Stud.IP v%s OpenGraph Parser\r\n", $GLOBALS['SOFTWARE_VERSION']),
                ],
            ]);

            $content = file_get_contents($this['url'], false, $context);
            $content = mb_encode_numericentity($content, [0x80, 0xffff, 0, 0xffff], $currentEncoding);
            $old_libxml_error = libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->loadHTML($content);
            libxml_use_internal_errors($old_libxml_error);

            $metatags = $doc->getElementsByTagName('meta');
            $reservedTags = ['url', 'chdate', 'mkdate', 'last_update', 'is_opengraph', 'data'];
            $isOpenGraph = false;
            $ogTags = [];
            $data = [];
            foreach ($metatags as $tag) {
                $key = false;
                if ($tag->hasAttribute('property')
                    && mb_strpos($tag->getAttribute('property'), 'og:') === 0)
                {
                    $key = mb_strtolower(mb_substr($tag->getAttribute('property'), 3));
                }
                if (!$key && $tag->hasAttribute('name')
                    && mb_strpos($tag->getAttribute('name'), 'og:') === 0)
                {
                    $key = mb_strtolower(mb_substr($tag->getAttribute('name'), 3));
                }
                if ($key) {
                    $content = $tag->getAttribute('content');
                    $data[] = ['og:'.$key => $content];
                    $ogTags[$key] = $content;
                    $isOpenGraph = true;
                }
            }
            foreach ($ogTags as $key => $tag) {
                if ($this->isField($key) && !in_array($key, $reservedTags)) {
                    $this[$key] = $tag;
                }
            }
            if (!$this['title'] && $isOpenGraph) {
                $titles = $doc->getElementsByTagName('title');
                if ($titles->length > 0) {
                    $this['title'] = $titles->item(0)->textContent;
                }
            }
            if (!$this['description'] && $isOpenGraph) {
                foreach ($metatags as $tag) {
                    if (mb_stripos($tag->getAttribute('name'), "description") !== false
                        || mb_stripos($tag->getAttribute('property'), "description") !== false)
                    {
                        $this['description'] = $tag->getAttribute('content');
                    }
                }
            }
            $this['data'] = $data;
        }
        $this['is_opengraph'] = (int) $isOpenGraph;
    }

    /**
     * Renders a small box with the information of the opengraph url. Used in
     * blubber and in the forum.
     *
     * @return string html output of the box.
     */
    public function render()
    {
        if (!Config::get()->OPENGRAPH_ENABLE || !$this->getValue('is_opengraph')) {
            return '';
        }
        $template = $GLOBALS['template_factory']->open('shared/opengraphinfo_wide.php');
        $template->og = $this;
        return $template->render();
    }

    /**
     * Returns an array with all audiofiles that are provided by the opengraph-node.
     * Each array-entry is an array itself with the url as first parameter and the
     * content-type (important for <audio/> tags) as the second.
     *
     * @return array(array($url, $content_type), ...)
     */
    public function getAudioFiles()
    {
        return $this->getMediaFiles('audio');
    }

    /**
     * Returns an array with all videofiles that are provided by the opengraph-node.
     * Each array-entry is an array itself with the url as first parameter and the
     * content-type (important for <video/> tags) as the second.
     *
     * @return array(array($url, $content_type), ...)
     */
    public function getVideoFiles()
    {
        return $this->getMediaFiles('video');
    }

    /**
     * Returns an array with all mediafiles that are provided by the opengraph-node.
     * Each array-entry is an array itself with the url as first parameter and the
     * content-type (important for <audio/> or <video/> tags) as the second.
     *
     * @param string $type "audio" or "video"
     * @return array(array($url, $content_type), ...)
     */
    protected function getMediaFiles($type)
    {
        $files = [];
        $media = [];
        $secure_media = [];
        $media_types = [];
        foreach ($this['data'] as $meta) {
            foreach ($meta as $key => $value) {
                switch ($key) {
                    case "og:$type:url":
                        $media[] = $value;
                        break;
                    case "og:$type":
                        $media[] = $value;
                        break;
                    case "og:$type:secure_url":
                        $secure_media[] = $value;
                        break;
                    case "og:$type:type":
                        $media_types[] = $value;
                        break;
                }
            }
        }
        if ($_SERVER['HTTPS'] === 'on' && count($secure_media)) {
            foreach ($secure_media as $index => $url) {
                $files[] = [$url, $media_types[$index]];
            }
        } else {
            foreach ($media as $index => $url) {
                $files[] = [$url, $media_types[$index]];
            }
        }
        return $files;
    }
}
