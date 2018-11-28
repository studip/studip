<?php
/**
 * Visibility_Me.php - Verifies if the visibility belongs to me
 * 
 * For all other users (except root) it is not possible to see the content
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
class Visibility_Me extends VisibilityAbstract {

    // Should this state be used?
    protected $activated = true;
    
    // What number does this state get in the database?
    protected $int_representation = 1;
    
    // How is the state displayed in the settings?
    protected $display_name = "nur mich selbst";
    
    // Description for the state
    protected $description = "nur für mich sichtbar";

    // When do two users have this state
    function verify($user_id, $other_id)
    {
        return $user_id == $other_id;
    }
}
?>