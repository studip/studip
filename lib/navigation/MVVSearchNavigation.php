<?php
/*
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.4
 */

/**
 * Class MVVSearchNavigation to check visibility of navigation item from MVV
 */

class MVVSearchNavigation extends Navigation
{
    /**
     * Checks if the MVV-search should be visible (if some elements in correct status do exist).
     * @param bool $needs_image : uninteresting since we have a third-level navigation item.
     * @return bool : from MVV::isVisibleSearch()
     */
    public function isVisible($needs_image = false)
    {
        return MVV::isVisibleSearch();
    }
}