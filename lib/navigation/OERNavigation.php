<?php
/*
 * OERNavigation.php - navigation for tools page
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       5.0
 */

/**
 * This navigation includes the OER Campus in Stud.IP.
 */
class OERNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        parent::__construct(Config::get()->OER_TITLE);

        $this->setImage(Icon::create('service', Icon::ROLE_NAVIGATION, ["title" => _('OER Campus')]));
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        global $perm;
        parent::initSubNavigation();

        $navigation = new Navigation(Config::get()->OER_TITLE, 'dispatch.php/oer/market');
        $this->addSubNavigation('market', $navigation);

        if ($perm->have_perm('autor')) {
            $navigation = new Navigation(_('Meine Materialien'), 'dispatch.php/oer/mymaterial');
            $this->addSubNavigation('mymaterial', $navigation);
        }

    }
}
