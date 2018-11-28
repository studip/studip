<?php
/**
 * ResponsiveHelper.php
 *
 * This class collects helper methods for Stud.IP's responsive design.
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license   GPL2 or any later version
 * @copyright Stud.IP core group
 * @since     Stud.IP 3.2
 */
class ResponsiveHelper
{
    /**
     * Returns the current navigation as an array.
     *
     * @return Array containing the navigation
     */
    public static function getNavigationArray()
    {
        $navigation = [];

        $link_params = array_fill_keys(array_keys(URLHelper::getLinkParams()), null);

        foreach (Navigation::getItem('/')->getSubNavigation() as $path => $nav) {
            if (!$nav->isVisible(true)) {
                continue;
            }

            $image = $nav->getImage();
            $image_src = $image ? $image->copyWithRole('info_alt')->asImagePath() : false;
            $item = array(
                'icon'   => $image_src ? self::getAssetsURL($image_src) : false,
                'title'  => $nav->getTitle(),
                'url'    => self::getURL($nav->getURL(), $link_params),
                'active' => $nav->isActive(),
            );

            if ($nav->getSubnavigation()) {
                $item['children'] = self::getChildren($nav, $path);
            }

            $navigation[$path] = $item;
        }

        return $navigation;
    }

    /**
     * Recursively build a navigation array from the subnavigation/children
     * of a navigation object.
     *
     * @param Navigation $navigation The navigation object
     * @param String     $path       Current path segment
     * @return Array containing the children (+ grandchildren...)
     */
    protected static function getChildren(Navigation $navigation, $path)
    {
        $children = array();

        foreach ($navigation->getSubNavigation() as $subpath => $subnav) {
            if (!$subnav->isVisible()) {
                continue;
            }

            $item = array(
                'title'  => $subnav->getTitle(),
                'url'    => self::getURL($subnav->getURL()),
                'active' => $subnav->isActive(),
            );

            if ($subnav->getSubNavigation()) {
                $item['children'] = self::getChildren($subnav, $path . '_' . $subpath);
            }

            $children[$path . '_' . $subpath] = $item;
        }

        return $children;
    }

    /**
     * Try to get a compressed version of the passed navigation url.
     * The URL is processed is processed by URLHelper and the absolute uri
     * of the Stud.IP installation is stripped from it afterwards.
     *
     * @param  String $url The url to compress
     * @return String containing the compressed url
     */
    protected static function getURL($url, $params = [])
    {
        return str_replace($GLOBALS['ABSOLUTE_URI_STUDIP'], '', URLHelper::getURL($url, $params));
    }

    /**
     * Try to get a compressed version of the passed assets url.
     * The absolute uri of the Stud.IP installation is stripped from the url.
     *
     * @param  String $url The assets url to compress
     * @return String containing the compressed assets url
     */
    protected static function getAssetsURL($url)
    {
        return str_replace($GLOBALS['ASSETS_URL'], '', $url);
    }
}
