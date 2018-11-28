<?php
/**
 * Calendar widget view, links to details page of courses.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.4
 */
class CalendarWidgetView extends CalendarWeekView
{
    /**
     * Creates a widget view from a week view.
     *
     * @param CalendarWeekView $view The CalendarWeekView object
     * @return CalendarWidgetView object with the data from the
     *         CalendarWeekView
     */
    public static function createFromWeekView(CalendarWeekView $view)
    {
        $new_view = new self($view->getColumns(), $view->getContext());
        $new_view->setReadOnly(true);
        return $new_view;
    }

    /**
     * Returns all columns of the calendar-view and removes everything that
     * is not needed and links the entry to the details page of the course.
     *
     * @return array of CalendarColumn
     */
    public function getColumns()
    {
        foreach ($this->entries as $column) {
            $column->setURL(false);
            foreach ($column->entries as $key => $entry) {
                if (isset($entry['cycle_id'])) {
                    list($course_id, $cycle_id) = explode('-', $entry['id']);

                    $url = URLHelper::getLink('dispatch.php/course/details/?sem_id=' . $course_id);
                    $column->entries[$key]['url'] = $url;
                } else {
                    unset($column->entries[$key]['url']);
                }

                unset($column->entries[$key]['onClick']);
                unset($column->entries[$key]['icons']);
            }
        }

        return $this->entries;
    }
}
