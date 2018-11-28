<?php
/**
 * HomeworkFolder.class.php
 *
 * This is a FolderType implementation for homework folders.
 *
 * A homework folder in Stud.IP can be writeable by all course members
 * but is only readable by teachers.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    Moritz Strohm <strohm@data-quest.de>
 * @copyright 2016 data-quest
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class HomeworkFolder extends PermissionEnabledFolder
{
    public static $sorter = 2;

    /**
     * Returns a localised name of the HomeworkFolder type.
     *
     * @return string The localised name of this folder type.
     */
    public static function getTypeName()
    {
        return _('Ordner für Hausarbeiten');
    }

    public static function availableInRange($range_id_or_object, $user_id)
    {
        $course = Course::toObject($range_id_or_object);
        if ($course && !$course->isNew()) {
            return Seminar_Perm::get()->have_studip_perm('tutor', $course->id, $user_id);
        }
    }

    public function __construct($folderdata = null)
    {
        parent::__construct($folderdata);
        $this->permission = 3;
    }

    /**
     * Returns the Icon object for the HomeworkFolder type.
     *
     * @return Icon An icon object with the icon for this folder type.
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        $shape = count($this->getSubfolders()) + count($this->getFiles()) === 0
            ? 'folder-lock-empty'
            : 'folder-lock-full';
        return Icon::create($shape, $role);
    }


    /**
     * Homework folders don't allow subfolders.
     *
     * @return bool False
     */
    public function isSubfolderAllowed($user_id)
    {
        //no subfolders allowed (I'm a homework folder, not a home folder backup!)
        return false;
    }

    /**
     * Returns the description template for a HomeworkFolder instance.
     *
     * @return string A description for the HomeworkFolder instance.
     */
    public function getDescriptionTemplate()
    {

        $template = $GLOBALS['template_factory']->open('filesystem/homework_folder/description.php');
        $template->folder     = $this;
        $template->folderdata = $this->folderdata;
        if (!Seminar_Perm::get()->have_studip_perm('tutor', $this->range_id)) {
            $files = new SimpleCollection($this->getFiles());
            $template->own_files = $files->findBy('user_id', $GLOBALS['user']->id)->orderBy('name');
        }

        return $template;
    }

    /**
     * Folders of this type don't have an edit template.
     *
     * @return string
     */
    public function getEditTemplate()
    {
        return '';
    }

    /**
     * @param FileRef|string $fileref_or_id
     * @param string $user_id
     * @return bool
     */
    public function isFileEditable($fileref_or_id, $user_id)
    {
        return $GLOBALS['perm']->have_studip_perm('tutor', $this->range_id, $user_id);
    }

    /**
     * Checks if a user has write permissions to a file.
     *
     * For standard folders write permissions are granted
     * if the user is the owner of the file or if the user has at least
     * tutor permissions on the Stud.IP object specified by range_id
     * (such objects may be courses or institutes for example).
     *
     * @param FileRef|string $fileref_or_id
     * @param string $user_id
     * @return bool
     */
    public function isFileWritable($fileref_or_id, $user_id)
    {
        return  $GLOBALS['perm']->have_studip_perm('tutor', $this->range_id, $user_id);
    }
}
