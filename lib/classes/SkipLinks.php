<?php
# Lifter010: TODO
/**
 * SkipLinks.php - API for global skip links
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * The SkipLinks class provides utility functions to handle
 * the integration of skip links.
 */
class SkipLinks
{
    /**
     * array of Skip links
     * @var array
     */
    private static $links = [];

    /**
     * array of positions of skip links
     * @var array
     */
    private static $position = [];

    /**
     * Inserts container for skip links in page layout.
     */
    public static function insertContainer()
    {
        if (UserConfig::get($GLOBALS['user']->id)->getValue('SKIPLINKS_ENABLE')) {
            PageLayout::addBodyElements('<style> *:focus, .focus_box, a.button:focus, button.button:focus { outline: 2px dashed #ff6600; }</style>');
            if (is_object($GLOBALS['auth']) && $GLOBALS['auth']->is_authenticated()) {
                PageLayout::addBodyElements('<div id="skip_link_navigation" aria-busy="true"></div>');
            }
        }
    }

    /**
     * Adds a link to the list of skip links.
     *
     * @param string $name the displayed name of the links
     * @param string $url the url of the links
     * @param integer $position the position of the link in the list
     * @param boolean $overwriteable false if position is not overwritable by another link
     */
    public static function addLink($name, $url, $position = null, $overwriteable = false)
    {
        $position = (is_null($position) || $position < 1) ? sizeof(self::$links) + 100 : intval($position);
        $new_link = ['name' => $name, 'url' => decodeHTML($url), 'position' => $position, 'overwriteable' => $overwriteable];
        if (self::checkOverwrite($new_link)) {
            self::$links[$new_link['url']] = $new_link;
        }
    }

    /**
     * Adds a link to an anker on the same page to the list of skip links.
     *
     * @param string $name the displayed name of the links
     * @param string $id the id of the anker
     * @param integer $position the position of the link in the list
     * @param boolean $overwriteable false if position is not overwritable by another link
     */
    public static function addIndex($name, $id, $position = null, $overwriteable = false)
    {
        $url = '#' . $id;
        self::addLink($name, $url, $position, $overwritable);
    }

    /**
     * Returns the formatted list of skip links
     *
     * @return string the formatted list of skip links
     */
    public static function getHTML()
    {
        $html = '';
        if (UserConfig::get($GLOBALS['user']->id)->getValue('SKIPLINKS_ENABLE') && $GLOBALS['auth']->is_authenticated() && sizeof(self::$links)) {
            Navigation::addItem('/skiplinks', new Navigation(''));
            uasort(self::$links, function ($a, $b) {
                return $a['position'] > $b['position'];
            });
            $i = 1;
            $position = 0;
            $overwriteable = false;
            foreach (self::$links as $link) {
                $navigation = new Navigation($link['name'], $link['url']);
                $path = '/skiplinks/' . $i++;
                Navigation::addItem($path, $navigation);
                $position = $link['position'];
                $overwriteable = $link['overwriteable'];
            }
            $navigation = Navigation::getItem('/skiplinks');
            $html = $GLOBALS['template_factory']->render('skiplinks', compact('navigation'));
        }
        return $html;
    }

    /**
     * Checks if there is another link at the same position and if it is overwritable.
     *
     * @return boolean true if the link at the same position is overwritable
     */
    private static function checkOverwrite($link)
    {
        if (isset(self::$position[$link['position']])) {
            return false;
        }
        if (!$link['overwrite']) {
            self::$position[$link['position']] = true;
        }
        return true;
    }

}
