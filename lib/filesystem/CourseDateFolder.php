<?php
/**
 * CourseDateFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    André Noack <noack@data-quest.de>
 * @copyright 2018 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class CourseDateFolder extends PermissionEnabledFolder implements FolderType
{
    public static $sorter = 1;

    private $date;

    public static function formatDate(CourseDate $date)
    {
        return sprintf(
            _("%'.02d. %s am %s"),
            CourseDate::getConsecutiveNumber($date),
            $date->getTypeName(),
            $date->getFullname()
        );
    }

    public static function getTypeName()
    {
        return _('Sitzungs-Ordner');
    }

    public static function availableInRange($range_id_or_object, $user_id)
    {
        $course = Course::toObject($range_id_or_object);
        if ($course && !$course->isNew()) {
            return Seminar_Perm::get()->have_studip_perm('tutor', $course->id, $user_id)
                && CourseDate::countBySql("range_id = ?" , [$course->id]);
        }
    }

    public function __construct($folderdata = null)
    {
        parent::__construct($folderdata);
        $this->getDate();
    }

    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        return Icon::create(
            count($this->getFiles()) ? 'folder-topic-full' : 'folder-topic-empty',
            $role
        );
    }

    /**
     * @return CourseDate
     */
    public function getDate()
    {
        if (isset($this->folderdata['data_content']['termin_id'])) {
            if ($this->date === null) {
                $this->date = CourseDate::find($this->folderdata['data_content']['termin_id']);
            }
            if ($this->date) {
                $this->folderdata['name'] = self::formatDate($this->date);
            } else {
                $this->folderdata['name'] = _('(Termin gelöscht)') . ' ' . $this->folderdata['name'];
            }
            return $this->date;
        }
    }

    /**
     * @param CourseDate $date
     * @return CourseDate
     */
    public function setDate(CourseDate $date)
    {
        $this->date = $date;
        $this->folderdata['data_content']['termin_id'] = $this->date->id;
        return $this->getDate();
    }

    /**
     * This method returns the special part for the edit template
     *
     * @return Flexi_Template  edit template
     */
    public function getEditTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/date_folder/edit.php');
        $template->date   = $this->getDate();
        $template->folder = $this;
        return $template;
    }

    /**
     * Stores the data which was edited in the edit template
     * @return FolderType|MessageBox The template with the edited data
     */
    public function setDataFromEditTemplate($request)
    {
        $date = CourseDate::find($request['course_date_folder_termin_id']);
        if ($date === null) {
            return MessageBox::error(_('Es wurde kein Termin ausgewählt.'));
        } else {
            $this->setDate($date);
        }
        if (isset($request['course_date_folder_perm_write'])) {
            $this->folderdata['data_content']['permission'] = 7;
        } else {
            $this->folderdata['data_content']['permission'] = 5;
        }
        $this->folderdata['description'] = $request['description'] ?: '';
        return $this;
    }

    /**
     * Returns the description template
     *
     * @return Flexi_Template description template
     */
    public function getDescriptionTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/date_folder/description.php');
        $template->type       = self::getTypeName();
        $template->folder     = $this;
        $template->date       = $this->getDate();
        $template->folderdata = $this->folderdata;

        return $template;
    }
}
