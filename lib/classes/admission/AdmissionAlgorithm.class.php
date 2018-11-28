<?php

/**
 * AdmissionAlgorithm.class.php
 * 
 * Abstract class for seminar seat distribution. A concrete algorithm 
 * needs to be implemented.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

abstract class AdmissionAlgorithm
{

    /**
     * Runs an algorithm that distributes all seats in the given CourseSet.
     *
     * @param  CourseSet course set object
     * @return boolean Did the algorithm run successfully?
     */
    public function run($courseSet)
    {
        return true;
    }

} /* end of class AdmissionAlgorithm */

?>