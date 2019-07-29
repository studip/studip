<?php
namespace RESTAPI\Routes;

/**
 * @author  André Klaßen <andre.klassen@elan-ev.de>
 * @author  <mlunzena@uos.de>
 * @license GPL 2 or later
 *
 * @condition user_id ^[a-f0-9]{32}$
 * @condition semester_id ^[a-f0-9]{32}$
 */
class Schedule extends \RESTAPI\RouteMap
{
    /**
     * returns schedule for a given user and semester
     *
     * @get /user/:user_id/schedule/:semester_id
     * @get /user/:user_id/schedule
     */
    public function getSchedule($user_id, $semester_id = null)
    {
        if ($user_id !== $GLOBALS['user']->id) {
            $this->error(401);
        }

        $current_semester = isset($semester_id)
            ? \Semester::find($semester_id)
            : \Semester::findCurrent();

        if (!$current_semester) {
            $this->notFound('No such semester.');
        }

        $schedule_settings = \UserConfig::get($user_id)->SCHEDULE_SETTINGS;
        $days = $schedule_settings['glb_days'];
        foreach ($days as $key => $day_number) {
            $days[$key] = ($day_number + 6) % 7;
        }

        $entries = \CalendarScheduleModel::getEntries(
            $user_id, $current_semester,
            $schedule_settings['glb_start_time'], $schedule_settings['glb_end_time'],
            $days,
            $visible = false
        );

       $json = [];
       foreach ($entries as $number_of_day => $schedule_of_day) {
           $entries = [];
           foreach ($schedule_of_day->entries as $entry) {
               $entries[$entry['id']] = self::entryToJson($entry);
           }
           $json[$number_of_day] = $entries;
       }

       $this->etag(md5(serialize($json)));

       return array_reverse($json, true);
    }


    private static function entryToJson($entry)
    {
        $json = [];
        foreach (['start', 'end', 'content', 'title', 'color', 'type'] as $key) {
            $json[$key] = $entry[$key];
        }

        return $json;
    }
}
