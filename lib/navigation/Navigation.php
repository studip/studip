<?php
# Lifter010: TODO
/*
 * Navigation.php - Stud.IP navigation base class
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

/**
 * This is the navigation base class that maintains the global
 * navigation structure. All navigation objects are stored in a
 * tree and can be accessed by their "path names", just like file
 * names in a normal file system. The "root" of the tree is '/'.
 *
 * So you can do for example:
 *
 * $navigation = new Navigation('Home', 'index.php');
 * $profilenav = new Navigation('Profile', 'profile.php');
 *
 * $navigation->addSubNavigation('profile', $profilenav);
 *
 * Navigation::addItem('/home', $navigation);
 * Navigation::activateItem('/home/profile');
 */
class Navigation implements IteratorAggregate
{
    private static $root;

    protected $active;
    protected $enabled;
    protected $initialized = false;

    protected $active_image;
    protected $badgeNumber;
    protected $badgeTimestamp;
    protected $description;
    protected $image;
    protected $link_attributes = [];
    protected $params;
    protected $subnav;
    protected $title;
    protected $url;

    /**
     * Mark the navigation item at the given path as active.
     * This is just a shortcut for doing:
     *
     *   Navigation::getItem($path)->setActive(true)
     *
     * @param string $path       path of navigation item
     */
    public static function activateItem($path)
    {
        self::getItem($path)->setActive(true);
        NotificationCenter::postNotification('NavigationDidActivateItem', $path);
    }

    /**
     * Add a new navigation item at the given path. If there is
     * already an item with this path, the old one is replaced
     * by the new item.
     *
     * @param string $path       path of new navigation item
     * @param object $navigation navigation item to add
     */
    public static function addItem($path, Navigation $navigation)
    {
        $nav = self::getItem(strtr(dirname($path), '\\', '/'));
        $nav->addSubNavigation(basename($path), $navigation);
    }

    /**
     * Add a new navigation item at the given path. The new
     * item is inserted immediately before the item with the
     * name given by $where (at the same level in the tree).
     *
     * @param string $path       path of new navigation item
     * @param object $navigation navigation item to add
     * @param string $where      insert it before this item
     */
    public static function insertItem($path, Navigation $navigation, $where)
    {
        $nav = self::getItem(strtr(dirname($path), '\\', '/'));
        $nav->insertSubNavigation(basename($path), $navigation, $where);
    }

    /**
     * Remove the navigation item at the given path (if there
     * is an item with this path).
     *
     * @param string $path       path of item to remove
     */
    public static function removeItem($path)
    {
        $nav = self::getItem(strtr(dirname($path), '\\', '/'));
        $nav->removeSubNavigation(basename($path));
    }

    /**
     * Return the navigation item at the given path.
     *
     * @param string $path   path of navigation item
     *
     * @throws InvalidArgumentException  if the item cannot be found
     */
    public static function getItem($path)
    {
        $node = self::$root;

        foreach (explode('/', $path) as $name) {
            if ($name === '') {
                continue;
            }

            $subnav = $node->getSubNavigation();
            $node = $subnav[$name];

            if (!isset($node)) {
                throw new InvalidArgumentException("navigation item '$path' not found");
            }
        }

        return $node;
    }

    /**
     * Test whether there is a navigation item at the given path.
     *
     * @param string $path   path of navigation item
     */
    public static function hasItem($path)
    {
        try {
            return self::getItem($path) instanceOf Navigation;
        } catch (InvalidArgumentException $ex) {
            return false;
        }
    }

    /**
     * Set the root of the navigation tree. Must be called before
     * any further items can be added to the tree.
     *
     * @param object $navigation root navigation item
     */
    public static function setRootNavigation(Navigation $navigation)
    {
        self::$root = $navigation;
    }

    /**
     * Initialize a new Navigation instance with the given title
     * and URL (optional).
     */
    public function __construct($title, $url = NULL, $params = NULL)
    {
        $this->setTitle($title);
        $this->setURL($url, $params);
        $this->setEnabled(true);
    }

    /**
     * used to defer initialization of item metadata, override in subclasses
     * if initialization is costly
     */
    public function initItem()
    {
        $this->initialized = true;
    }

    /**
     * Return the current image associated with this navigation item.
     *
     * @return Icon  an instance of class Icon depicting this item or NULL
     */
    public function getImage()
    {
        if ($this->initialized === false) {
            $this->initItem();
        }
        if (isset($this->active_image) && $this->isActive()) {
            return $this->active_image;
        } else {
            return $this->image;
        }
    }

    /**
     * Shorthand method for creating an appropriate image tag for display.
     *
     * @return string HTML tag snippet for the image
     */
    public function getImageTag()
    {
        if ($image = $this->getImage()) {
            return $image->asImg($this->getLinkAttributes());
        } else {
            return '';
        }
    }

    /**
     * Return the current title associated with this navigation item.
     *
     * @return string   title of item or NULL (no title set)
     */
    public function getTitle()
    {
        if ($this->initialized === false) {
            $this->initItem();
        }
        return $this->title;
    }

    /**
     * Return the description associated with this navigation item.
     *
     * @return string   description of item or NULL (no description set)
     */
    public function getDescription()
    {
        if ($this->initialized === false) {
            $this->initItem();
        }
        return $this->description;
    }

    /**
     * Return the current URL associated with this navigation item.
     * If not URL is set but there are subnavigation items, the URL
     * of the first visible subnavigation item is returned.
     *
     * @return string   url of item or NULL (no URL set)
     */
    public function getURL()
    {
        if ($this->initialized === false) {
            $this->initItem();
        }
        if (isset($this->url)) {
            if (isset($this->params)) {
                return URLHelper::getURL($this->url, $this->params);
            } else {
                return $this->url;
            }
        }

        foreach ($this->getSubNavigation() as $nav) {
            $url = $nav->getURL();

            if (isset($url)) {
                return $url;
            }
        }

        return NULL;
    }

    /**
     * Return the badge number of this navigation item.
     *
     * @return int  the badge number
     */
    public function getBadgeNumber()
    {
        return $this->badgeNumber;
    }

    /**
     * Return the badge number of this navigation item.
     *
     * @return int  the badge number
     */
    public function getBadgeTimestamp()
    {
        return $this->badgeTimestamp;
    }

    /**
     * Determines whether this navigation item has a badge number.
     */
    public function hasBadgeNumber()
    {
        return (boolean) $this->badgeNumber;
    }


    /**
     * Determine whether this navigation item is active.
     */
    public function isActive()
    {
        if (isset($this->active)) {
            return $this->active;
        }

        return (boolean) $this->activeSubNavigation();
    }

    /**
     * Return whether this navigation item is enabled.
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Return whether this navigation item is visible.
     *
     * @param boolean $needs_image  requires an image
     */
    public function isVisible($needs_image = false)
    {
        if ($needs_image && !$this->getImage()) {
            return false;
        }

        $url = $this->getURL();

        return isset($url);
    }

    /**
     * Set the active status of this item. This can be used to
     * override heuristics used by the class to determine this
     * automatically.
     *
     * @param boolean $active  new active status
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Set the enabled status of this item. Disabled items are
     * still visible but cannot be clicked.
     *
     * @param boolean $enabled  new enabled status
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * Set the image of this navigation item. Additional HTML
     * attributes can be passed using the $options parameter
     * (like 'style' or 'onclick').
     *
     * @param Icon   $image            an instance of class Icon depicting this item
     * @param array  $link_attributes  additional link attributes
     */
    public function setImage(\Icon $image = null, $linkAttributes = [])
    {
        $this->image = $image;
        $this->setLinkAttributes($linkAttributes);
    }

    /**
     * Set the image for the active state of this navigation item.
     * If no active image is set, the normal image is used for the
     * active state. Additional HTML attributes can be passed using
     * the $options parameter (like 'style' or 'onclick').
     *
     * @param Icon   $image            an instance of class Icon depicting this item
     * @param array  $link_attributes  additional link attributes
     */
    public function setActiveImage(\Icon $image = null, $linkAttributes = [])
    {
        $this->active_image = $image;
        $this->setLinkAttributes($linkAttributes);
    }

    /**
     * Set the attributes that should be used in the link element
     * surrounding this item's icon image.
     *
     * @param array  $attributes  the attributes to pass to the surrounding link element
     */
    public function setLinkAttributes($attributes)
    {
        $this->link_attributes = $attributes;
    }

    /**
     * Return the attributes that should be used in the link element
     * surrounding this item's icon image.
     *
     * @return array the attributes to pass to the surrounding link element
     */
    public function getLinkAttributes()
    {
        return $this->link_attributes;
    }

    /**
     * Set the title of this navigation item.
     *
     * @param string $title    display title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Set the description of this navigation item.
     *
     * @param string $description    description text
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Set the URL of this navigation item. Additional URL
     * parameters can be passed using the (optional) second
     * parameter.
     *
     * @param string $title    display title
     * @param array  $params   additional URL parameters
     */
    public function setURL($url, $params = NULL)
    {
        $this->url = $url;
        $this->params = $params;
    }

    /**
     * Set the badge number of this navigation item.
     *
     * @param string $badgeNumber    the badge number
     */
    public function setBadgeNumber($badgeNumber)
    {
        $this->badgeNumber = $badgeNumber;
    }

    /**
     * Set the badge number of this navigation item.
     *
     * @param string $badgeNumber    the badge number
     */
    public function setBadgeTimestamp($badgeTimestamp)
    {
        $this->badgeTimestamp = $badgeTimestamp;
    }

    /**
     * Get the active subnavigation item of this navigation
     * (if there is one). Returns NULL if the subnavigation
     * has no active item.
     */
    public function activeSubNavigation()
    {
        if (isset($this->subnav)) {
            foreach ($this->subnav as $nav) {
                if ($nav->isActive()) {
                    return $nav;
                }
            }
        }

        return NULL;
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    protected function initSubNavigation()
    {
        $this->subnav = [];
    }

    /**
     * Add the given item to the subnavigation of this object.
     * This also assigns a name to this subnavigation item. If
     * there is already a subitem with this name, the old one
     * is replaced by the new item.
     *
     * @param string $name       name of new navigation item
     * @param object $navigation navigation item to add
     */
    public function addSubNavigation($name, Navigation $navigation)
    {
        if (!isset($this->subnav)) {
            $this->initSubNavigation();
        }

        $this->subnav[$name] = $navigation;
    }

    /**
     * Add the given item to the subnavigation of this object.
     * The new item is inserted immediately before the item with
     * the name given by $where (if there is one, it is appended
     * to the end otherwise). This also assigns a name to this
     * subnavigation item.
     *
     * @param string $name       name of new navigation item
     * @param object $navigation navigation item to add
     * @param string $where      insert it before this item
     */
    public function insertSubNavigation($name, Navigation $navigation, $where)
    {
        foreach ($this->getSubNavigation() as $key => $nav) {
            if ($key == $where) {
                $subnav[$name] = $navigation;
                $done = true;
            }

            $subnav[$key] = $nav;
        }

        if (!$done) {
            $subnav[$name] = $navigation;
        }

        $this->subnav = $subnav;
    }

    /**
     * Return the list of subnavigation items of this object.
     */
    public function getSubNavigation()
    {
        if (!isset($this->subnav)) {
            $this->initSubNavigation();
        }

        return $this->subnav;
    }

    /**
     * Remove the given item from the subnavigation of this
     * object (if there is an item with this name).
     *
     * @param string $name       name of item to remove
     */
    public function removeSubNavigation($name)
    {
        if (!isset($this->subnav)) {
            $this->initSubNavigation();
        }

        unset($this->subnav[$name]);
    }

    /**
     * IteratorAggregate: Create interator for request parameters.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getSubNavigation());
    }
}
