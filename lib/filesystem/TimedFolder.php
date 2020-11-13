<?php
/**
 * TimedFolder.php
 * A folder type that provides time-based access to its contents.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    Thomas Hackl <thomas.hackl@uni-passau.de>
 * @copyright 2017 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class TimedFolder extends PermissionEnabledFolder
{

    public static $sorter = 5;

    /**
     * @var int start of folder visibility (0 for always)
     */
    public $start_time;

    /**
     * @var int end of folder visibility (0 for always)
     */
    public $end_time;

    /**
     * Provides the ranges this folder type is available in for the given user.
     * Doesn't really make sense in other contexts than a course.
     *
     * @param string $range_id_or_object the object (or object ID) to check for
     * @param $user_id the user to check for (must have at least 'tutor' perm in given course)
     * @return bool available or not?
     */
    public static function availableInRange($range_id_or_object, $user_id)
    {
        $course = Course::toObject($range_id_or_object);
        if ($course && !$course->isNew()) {
            return Seminar_Perm::get()->have_studip_perm('tutor', $course->id, $user_id);
        }
    }

    /**
     * Returns the name of the TimedFolder type.
     *
     * @return string the name of the TimedFolder type
     */
    public static function getTypeName()
    {
        return _('Zeitgesteuerter Ordner');
    }

    /**
     * Is the current folder visible for the given user?
     * That depends on parent folder visibility and time settings.
     *
     * @param string|null $user_id the user to check visibility for
     * @return bool visible or not?
     */
    public function isVisible($user_id = null)
    {
        $now = time();
        return (
                ($this->start_time == 0 || $this->start_time <= $now) &&
                    ($this->end_time == 0 || $this->end_time >= $now)
                ||
                $GLOBALS['perm']->have_studip_perm($this->must_have_perm, $this->range_id, $user_id)) &&
            parent::isVisible($user_id);
    }

    public function isReadable($user_id = null)
    {
        $now = time();
        return (
                ($this->start_time == 0 || $this->start_time <= $now) &&
                ($this->end_time == 0 || $this->end_time >= $now)
                ||
                $GLOBALS['perm']->have_studip_perm($this->must_have_perm, $this->range_id, $user_id)) &&
            parent::isReadable($user_id);
    }

    public function isWritable($user_id = null)
    {
        $now = time();
        return (
                ($this->start_time == 0 || $this->start_time <= $now) &&
                ($this->end_time == 0 || $this->end_time >= $now)
                ||
                $GLOBALS['perm']->have_studip_perm($this->must_have_perm, $this->range_id, $user_id)) &&
            parent::isWritable($user_id);
    }

    /**
     * This function returns the suitable Icon for this folder type (TimedFolder)
     *
     * @return Icon The icon object for this folder type
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        $shape = $this->is_empty
               ? 'folder-date-empty'
               : 'folder-date-full';
        return Icon::create($shape, $role);
    }

    /**
     * TimedFolder constructor.
     *
     * @param array|null $folderdata data to create folder with
     */
    public function __construct($folderdata = null)
    {
        parent::__construct($folderdata);

        if (isset($this->folderdata['data_content']['permission'])) {
            $this->permission = $this->folderdata['data_content']['permission'];
        } else {
            $this->folderdata['data_content']['permission'] = $this->permission;
        }

        $this->start_time = intval($this->folderdata['data_content']['start_time']);
        $this->end_time = intval($this->folderdata['data_content']['end_time']);

        $this->must_have_perm = 'tutor';
    }

    /**
     * Returns the description template for a instance of a TimedFolder type.
     *
     * @return Flexi_Template A description template for a instance of the type TimedFolder
     */
    public function getDescriptionTemplate()
    {

        $template = $GLOBALS['template_factory']->open('filesystem/timed_folder/description');

        $template->type       = self::getTypeName();
        $template->folder     = $this;
        $template->folderdata = $this->folderdata;

        if (!Seminar_Perm::get()->have_studip_perm('tutor', $this->range_id) &&
                $this->isWritable($GLOBALS['user']->id) && !$this->isReadable($GLOBALS['user']->id)) {
            $files = new SimpleCollection($this->getFiles());
            $template->own_files = $files->findBy('user_id', $GLOBALS['user']->id)->orderBy('name');
        }

        return $template;
    }

    /**
     * Returns the edit template for this folder type.
     *
     * @return Flexi_Template
     */
    public function getEditTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/timed_folder/edit');

        $template->folder = $this;

        return $template;
    }

    /**
     * Stores the data which was edited in the edit template
     * @return mixed The template with the edited data
     */
    public function setDataFromEditTemplate($request)
    {
        $permvalue = ($request['perm_read'] ? $this->perms['r'] : 0) +
            ($request['perm_write'] ? $this->perms['w'] : 0) +
            $this->perms['x'];
        $this->folderdata['data_content']['permission'] = $permvalue;
        $start = strtotime($request['start_time']);
        $end = strtotime($request['end_time']);

        if (!$start && !$end) {

            return MessageBox::error(_('Bitte geben Sie eine Start- und/oder Endzeit an.'));

        } else {

            if (!$end || $start < $end) {
                $this->folderdata['data_content']['start_time'] = $start;
            } else {
                return MessageBox::error(_('Die Startzeit muss kleiner als die Endzeit sein.'));
            }

            if (!$start || $end > $start) {
                $this->folderdata['data_content']['end_time'] = $end;
            } else {
                $this->folderdata['data_content']['end_time'] = 0;
            }

            return parent::setDataFromEditTemplate($request);
        }

    }

    /**
     * @param FileRef|string $fileref_or_id
     * @param string $user_id
     * @return bool
     */
    public function isFileEditable($fileref_or_id, $user_id)
    {
        //HomeworkFolder Style
        if (!Seminar_Perm::get()->have_studip_perm('tutor', $this->range_id) &&
            $this->isWritable($user_id) && !$this->isReadable($user_id)) {
            return false;
        }
        return parent::isFileEditable($fileref_or_id, $user_id);
    }

    /**
     * Checks if a user has write permissions to a file.
     *
     *
     * @param FileRef|string $fileref_or_id
     * @param string $user_id
     * @return bool
     */
    public function isFileWritable($fileref_or_id, $user_id)
    {
        //HomeworkFolder Style
        if (!Seminar_Perm::get()->have_studip_perm('tutor', $this->range_id) &&
            $this->isWritable($user_id) && !$this->isReadable($user_id)) {
            return false;
        }
        return parent::isFileWritable($fileref_or_id, $user_id);
    }

}
