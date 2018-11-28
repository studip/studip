<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

interface Event
{
    const PERMISSION_FORBIDDEN = 0;
    const PERMISSION_CONFIDENTIAL = 1;
    const PERMISSION_READABLE = 2;
    const PERMISSION_DELETABLE = 3;
    const PERMISSION_WRITABLE = 4;
    const PERMISSION_OWN = 5;
    
    /**
     * Returns a list of all categories the event belongs to.
     * Returns an empty string if no permission.
     *
     * @return string All categories as list.
     */
    public function toStringCategories();

    /**
     * Returns an array that represents the recurrence rule for this event.
     * If an index is given, returns only this field of the rule.
     * 
     * @return array|string The array with th recurrence rule or only one field.
     */
    public function getRecurrence($index = null);
    
    /**
     * TODO Wird das noch benötigt?
     */
    public function getType();
    
    /**
     * Returns the title of this event.
     * If the user has not the permission Event::PERMISSION_READABLE,
     * the title is "Keine Berechtigung.".
     * 
     * @return string 
     */
    public function getTitle();
    
    /**
     * Returns the starttime as unix timestamp of this event.
     *
     * @return int The starttime of this event as a unix timestamp
     */
    public function getStart();
    
    /**
     * Returns the endtime as unix timestamp of this event.
     *
     * @return int the endtime of this event as a unix timestamp
     */
    public function getEnd();
    
    /**
     * Returns the duration of this event in seconds.
     *
     * @return int the duration of this event in seconds
     */
    function getDuration();
    
    /**
     * Returns the location.
     * Without permission or the location is not set an empty string is returned.
     * 
     * @return string The location
     */
    function getLocation();
    
    /**
     * Returns the global uni id of this event.
     * 
     * @return string The global unique id.
     */
    public function getUid();
    
    /**
     * Returns the description of the topic.
     * If the user has no permission or the event has no topic
     * or the topics have no descritopn an empty string is returned.
     *
     * @return String the description
     */
    function getDescription();
    
    /**
     * Returns the index of the category.
     * If the user has no permission, 255 is returned.
     * 
     * @see config/config.inc.php $TERMIN_TYP
     * @return int The index of the category
     */
    public function getCategory();
    
    /**
     * Returns the user id of the last editor.
     * 
     * @return null|int The editor id.
     */
    public function getEditorId();
    
    /**
     * Returns whether the event is a all day event.
     * 
     * @return 
     */
    public function isDayEvent();
    
    /**
     * Returns the accessibility of this event. The value is not influenced by
     * the permission of the actual user.
     * 
     * According to RFC5545 the accessibility (property CLASS) is represented
     * by the 3 values PUBLIC, PRIVATE and CONFIDENTIAL. In RFC5545 the default
     * value is PUBLIC. In Stud.IP the default is PRIVATE.
     * 
     * @return string The accessibility as string.
     */
    function getAccessibility();
    
    /**
     * Returns the unix timestamp of the last change.
     *
     * @access public
     */
    public function getChangeDate();
    
    /**
     * Returns the date time the event was imported.
     * 
     * TODO not sure if we need this anymore
     * 
     * @return int Date time of import as unix timestamp:
     */
    function getImportDate();
    
    /**
     * Returns all properties of this event.
     * The name of the properties correspond to the properties of the
     * iCalendar calendar data exchange format. There are a few properties with
     * the suffix STUDIP_ which have no eqivalent in the iCalendar format.
     * 
     * DTSTART: The start date-time as unix timestamp.
     * DTEND: The end date-time as unix timestamp.
     * SUMMARY: The short description (title) that will be displayed in the views.
     * DESCRIPTION: The long description.
     * UID: The global unique id of this event.
     * CLASS:
     * CATEGORIES: A comma separated list of categories.
     * PRIORITY: The priority.
     * LOCATION: The location.
     * EXDATE: A comma separated list of unix timestamps.
     * CREATED: The creation date-time as unix timestamp.
     * LAST-MODIFIED: The date-time of last modification as unix timestamp.
     * DTSTAMP: The cration date-time of this instance of the event as unix
     * timestamp.
     * RRULE: All data for the recurrence rule for this event as array.
     * EVENT_TYPE:
     * 
     * 
     * @return array The properties of this event.
     */
    public function getProperties();
    
    /**
     * Returns the value of property with given name.
     * 
     * @param type $name See CalendarEvent::getProperties() for accepted values.
     * @return mixed The value of the property.
     * @throws InvalidArgumentException
     */
    public function getProperty($name);
    
    public function havePermission($permission, $user_id = null);
    
    public function getPermission($user_id = null);
    
    /**
     * Returns the priority in a human readable form.
     * If the user has no permission an epmty string will be returned.
     * 
     * @return string The priority as a string.
     */
    public function toStringPriority();
    
    /**
     * Returns the accessibilty in a human readable form.
     * If the user has no permission an epmty string will be returned.
     * 
     * @return string The accessibility as string.
     */
    public function toStringAccessibility();
    
    /**
     * Returns a string representation of the recurrence rule.
     * If $only_type is true returns only the type of the recurrence.
     *
     * @param bool $only_type If true returns only the type of recurrence.
     * @return string The recurrence rule - human readable
     */
    public function toStringRecurrence($only_type = false);
    
    /**
     * Returns the author of this event as user object.
     * 
     * @return User|null User object.
     */
    public function getAuthor();
    
    /**
     * Returns the editor of this event as user object.
     * 
     * @return User|null User object.
     */
    public function getEditor();
}