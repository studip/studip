<?php

/**
 * Interface AdminCourseContents
 * With this interface a plugin is able to add columns to the course-overview table for admins and roots.
 */
interface AdminCourseContents
{
    /**
     * The available columns for the course-overview table for admins. Index is the identifier of the column
     * for the method adminAreaGetCourseContent. The value is the display name of the column.
     * @return array : an associative array like array('index' => _("Translated display name"))
     */
    public function adminAvailableContents();

    /**
     * Returns the value of the additional column for the course-overview table in the admin-area.
     * @param Course $course : A Course-object of the given ... course
     * @param string $index : the index that comes from adminAvailableContents to identify the column.
     * @return Flexi_Template | String : Either one will do, but string is preferred, because it can exported as CSV-file more easily.
     */
    public function adminAreaGetCourseContent($course, $index);
}
