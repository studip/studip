<?php

/**
 * ResourceLabel.class.php - model class for a resource label
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
 * @since       4.5
 *
 * All properties are inherited from the parent class (Resource).
 */


/**
 * The ResourceLabel class represents headings or subheadings whose
 * only purpose is helping with organising the resource tree.
 * ResourceLabel instances must not be booked, reserved or locked.
 * Furthermore, they cannot be requested.
 */
class ResourceLabel extends Resource
{
    public function getFolder($create_if_missing = true)
    {
        return null;
    }
    
    public function setFolder(ResourceFolder $folder)
    {
        return false;
    }
    
    public function createFolder()
    {
        return null;
    }
    
    public function createSimpleBooking(
        User $user,
        DateTime $begin,
        DateTime $end,
        $preparation_time = 0,
        $description = '',
        $internal_comment = '',
        $booking_type = 0
    )
    {
        return null;
    }
    
    public function createBookingFromRequest(
        User $user,
        ResourceRequest $request,
        $preparation_time = 0,
        $description = '',
        $internal_comment = '',
        $booking_type = 0,
        $prepend_preparation_time = false,
        $notify_lecturers = false
    )
    {
        return null;
    }
    
    public function createBooking(
        User $user,
        $range_id = null,
        $time_ranges = [],
        $repetition_interval = null,
        $repetition_amount = 0,
        $repetition_end_date = null,
        $preparation_time = 0,
        $description = '',
        $internal_comment = '',
        $booking_type = 0,
        $force_booking = false
    )
    {
        return null;
    }
    
    public function createSimpleRequest(
        User $user,
        DateTime $begin,
        DateTime $end,
        $comment = '',
        $preparation_time = 0
    )
    {
        return null;
    }
    
    public function createRequest(
        User $user,
        $date_range_ids = null,
        $comment = '',
        $properties = [],
        $preparation_time = 0
    )
    {
        return null;
    }
    
    public function createLock(
        User $user,
        DateTime $begin,
        DateTime $end,
        $internal_comment = ''
    )
    {
        return null;
    }
    
    public function propertyExists($name = '')
    {
        //Resource labels don't have properties:
        return false;
    }
    
    public function getPropertyObject(string $name)
    {
        return null;
    }
    
    public function getProperty(string $name)
    {
        return null;
    }
    
    public function getPropertyRelatedObject(string $name)
    {
        return null;
    }
    
    public function setProperty(string $name, $state = '', $user_id = null)
    {
        return false;
    }
    
    public function isPropertyEditable(string $name, User $user)
    {
        return false;
    }
    
    public function setPropertyByDefinitionId(
        $property_definition_id = null,
        $state = null
    )
    {
        return false;
    }
    
    public function setPropertyRelatedObject(string $name, SimpleORMap $object)
    {
        return false;
    }
    
    public function getIcon($role = Icon::ROLE_INFO)
    {
        return Icon::create('resource-label', $role);
    }
    
    public function getPropertyArray($only_requestable_properties = false)
    {
        return [];
    }
    
    public function isAssigned(
        DateTime $begin,
        DateTime $end,
        $excluded_booking_ids = []
    )
    {
        return false;
    }
    
    public function isReserved(
        DateTime $begin,
        DateTime $end,
        $excluded_reservation_ids = []
    )
    {
        return false;
    }
    
    public function isLocked(
        DateTime $begin,
        DateTime $end,
        $excluded_lock_ids = []
    )
    {
        return true;
    }
    
    public function isAvailable(
        DateTime $begin,
        DateTime $end,
        $excluded_booking_ids = []
    )
    {
        return false;
    }
    
    public function isAvailableForRequest(ResourceRequest $request)
    {
        return false;
    }
    
    public function getFullName()
    {
        return $this->name;
    }
    
    public function getOpenResourceRequests(DateTime $begin, DateTime $end)
    {
        return [];
    }
    
    public function getResourceBookings(DateTime $begin, DateTime $end)
    {
        return [];
    }
    
    public function getResourceLocks(DateTime $begin, DateTime $end)
    {
        return [];
    }
    
    public function hasFiles()
    {
        return false;
    }
    
    public function checkHierarchy()
    {
        return true;
    }
    
    public function getItemAvatarURL()
    {
        return Icon::create('info',  Icon::ROLE_INFO)->asImagePath();
    }
}
