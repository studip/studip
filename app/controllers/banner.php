<?php
/**
 * banner.php - controller class for the banners
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author   Nico Müller <nico.mueller@uni-oldenburg.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @package  admin
 * @since    2.4
 */
class BannerController extends StudipController
{
    public function click_action(Banner $banner)
    {
        $banner->clicks += 1;
        $banner->store();

        $this->redirect($banner->getLink());
    }
}
