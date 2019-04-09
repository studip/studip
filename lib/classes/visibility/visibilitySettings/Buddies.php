<?php
/**
 * Visibility_Buddies.php - Verifies if two users are buddies
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */
class Visibility_Buddies extends VisibilityAbstract
{

    // Should this state be used?
    protected $activated = true;

    // What number does this state get in the database?
    protected $int_representation = 2;

    // How is the state displayed in the settings?
    protected $display_name = 'Kontakte';

    // Description for the state
    protected $description = "nur für meine Kontakte sichtbar";

    // When do two users have this state
    function verify($user_id, $other_id)
    {
        if ($other_id === 'nobody') {
            return false;
        }

        return $user_id == $other_id || Contact::CountBySQL("user_id=? AND owner_id=?", [$user_id, $other_id]);
    }
}
?>
