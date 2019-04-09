<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2015 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 */

class CourseMarkedEvent extends CourseEvent
{

    protected static function configure($config= [])
    {
        parent::configure($config);
    }

    /**
     * Returns all CourseMarkedEvents in the given time range for the given range_id.
     *
     * @param string $user_id Id of Stud.IP object from type user, course, inst
     * @param DateTime $start The start date time.
     * @param DateTime $end The end date time.
     * @return SimpleORMapCollection Collection of found CourseMarkedEvents.
     */
    public static function getEventsByInterval($user_id, DateTime $start, dateTime $end)
    {
        $stmt = DBManager::get()->prepare('SELECT DISTINCT termine.* FROM schedule_seminare '
                . 'INNER JOIN termine ON schedule_seminare.seminar_id = range_id '
                . 'LEFT JOIN seminar_user ON seminar_user.seminar_id = range_id AND seminar_user.user_id= :user_id '
                . 'WHERE schedule_seminare.user_id = :user_id AND schedule_seminare.visible = 1 '
                . 'AND seminar_user.seminar_id IS NULL AND date BETWEEN :start AND :end '
                . 'ORDER BY date ASC');
        $stmt->execute([
            ':user_id' => $user_id,
            ':start'   => $start->getTimestamp(),
            ':end'     => $end->getTimestamp()
        ]);
        $event_collection = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $event = new CourseMarkedEvent();
            $event->setData($row);
            $event->setNew(false);
            $event_collection[] = $event;
        }
        $event_collection = SimpleORMapCollection::createFromArray($event_collection, false);
        $event_collection->setClassName('Event');
        return $event_collection;
    }

    public function getPermission($user_id = null)
    {
        return Event::PERMISSION_READABLE;
    }

    /**
     * Returns the title of this event.
     * The title of a course event is the name of the course or if a topic is
     * assigned, the title of this topic. If the user has not the permission
     * Event::PERMISSION_READABLE, the title is "Keine Berechtigung.".
     *
     * @return string
     */
    public function getTitle()
    {
        $title = $this->course->name;
        $title .= ' ' . _('(vorgemerkt)');

        return $title;
    }

    /**
     * Returns the index of the category.
     * If the user has no permission, 255 is returned.
     *
     * TODO remove? use getStudipCategory instead?
     *
     * @see config/config.inc.php $TERMIN_TYP
     * @return int The index of the category
     */
    public function getCategory()
    {
        return 256;
    }

    public function getDescription()
    {
        return '';
    }

}