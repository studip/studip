<?php


namespace Studip\Calendar;


/**
 * This interface defines methods that can be implemented by classes
 * that can provide calendar event data in a standardised format.
 * The methods are meant to be called on a "per-object base",
 * meaning that filtering of the event sources has to be done
 * before calling methods of this interface.
 */
interface EventSource
{
    /**
     * Returns all event data this event source can provide.
     *
     * @returns EventData[] An array of Studip\Calendar\EventData objects.
     */
    public function getAllEventData();


    /**
     * Returns event data that fall in the specified time range.
     *
     * @returns EventData[] An array of Studip\Calendar\EventData objects.
     */
    public function getEventDataForTimeRange(\DateTime $begin, \DateTime $end);


    /**
     * Allows a filtered output of event data based on a specified user,
     * a specified range-ID and range type (must be supported by the
     * Context class) or a specified time range.
     * If no filters are applied the result should be the same as if
     * the getAllEventData method from this interface is called.
     *
     * @param string $user_id The user for whom the event data shall be
     *     retrieved. Depending on the implementation, this parameter
     *     might be necessary to check permissions for event objects.
     *
     * @param string $range_id An optional range-ID that may be necessary
     *     for an implementation to check for permissions.
     *
     * @param string $range_type An optional range type that may be
     *     necessary for an implementation to check for permissions.
     *
     * @param DateTime|int|string $begin The begin date as DateTime object
     *     or unix timestamp.
     *
     * @param DateTime|int|string $end The end date as DateTime object
     *     or unix timestamp.
     *
     * @returns EventData[] An array of Studip\Calendar\EventData objects.
     */
    public function getFilteredEventData(
        $user_id = null,
        $range_id = null,
        $range_type = null,
        $begin = null,
        $end = null
    );
}
