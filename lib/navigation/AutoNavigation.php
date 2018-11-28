<?php
# Lifter010: TODO
/*
 * AutoNavigation.php - Stud.IP auto navigation class
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

class AutoNavigation extends Navigation
{
    /**
     * Determine whether this navigation item is active or not.
     * This implementation tries to guess it based on the request
     * URL and query parameters.
     *
     * @return boolean  true if item is active, false otherwise
     */
    public function isActive()
    {
        if (isset($this->active)) {
            return $this->active;
        }

        $url = $this->getURL();

        // if URL is set, try to guess whether active or not
        if (isset($url)) {
            list($request_path, $query) = explode('?', Request::path());
            list($request_url, $query)  = explode('?', Request::url());
            list($url, $query)          = explode('?', $url);

            if (!preg_match('%^[a-z]+:%', $url) && $url[0] !== '/') {
                $url = $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'].$url;
            }

            if ($url === $request_path || $url === $request_url) {
                $this->active = true;

                if (isset($this->params)) {
                    foreach ($this->params as $key => $val) {
                        if (Request::get($key) != $val) {
                            $this->active = false;
                        }
                    }
                }

                if ($this->active) {
                    return true;
                }
            }
        }

        return $this->active = (boolean) $this->activeSubNavigation();
    }
}
