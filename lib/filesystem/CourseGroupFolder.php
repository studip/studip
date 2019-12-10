<?php
/**
 *  CourseGroupFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.gruppe
 *
 * @author    Dominik Feldschnieders <dofeldsc@uos.de>
 * @copyright 2016 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class CourseGroupFolder extends StandardFolder
{

    public static $sorter = 4;

    // the id from the associated group
    protected $group_id;

    // tutor and higher of a course can do everything with this foldertype
    protected $must_have_perm = "tutor";

    /**
     * Returns the name of the GroupFolder type.
     *
     * @return string the name of the GroupFolder type
     */
    public static function getTypeName()
    {
        return _('Gruppenordner');
    }


    public static function availableInRange($range_id_or_object, $user_id)
    {
        $course = Course::toObject($range_id_or_object);
        if ($course && !$course->isNew()) {
            return Seminar_Perm::get()->have_studip_perm('tutor', $course->id, $user_id) && Statusgruppen::countBySql("range_id = ?" , [$course->id]);
        }
    }

    /**
     * This method check the permission (global and if he is in the group) for a given user
     *
     * @param $user_id The User-ID
     * @return bool True if user have permission, False otherwise
     */
    public function checkPermission($user_id)
    {
        $group = new Statusgruppen($this->folderdata['data_content']['group']);

        return $group->isMember($user_id)
            || ($user_id && is_object($GLOBALS['perm'])
                && $GLOBALS['perm']->have_studip_perm($this->must_have_perm, $this->range_id, $user_id));
    }

    /**
     * Check if this GroupFolder instance is visible for this user or not.
     *
     * @param user_id The User-ID
     * @return bool True, if the user is in this group or is the lecturer, false otherwise
     */
    public function isVisible($user_id)
    {
        return $this->checkPermission($user_id);
    }

    /**
     * Check if this GroupFolder instance is readable for this user or not.
     *
     * @param user_id The User-ID
     * @return bool True, if the user is in this group or is the lecturer, false otherwise
     */
    public function isReadable($user_id)
    {
        return $this->checkPermission($user_id);
    }

    /**
     * Check if this GroupFolder instance is writable for this user or not.
     *
     * @param user_id The User-ID
     * @return bool True, if the user is in this group or is the lecturer, false otherwise
     */
    public function isWritable($user_id)
    {
        return $this->checkPermission($user_id);
    }

    /**
     * Check if a Subfolder can be created.
     *
     * @return bool True, if the user is in this group or is the lecturer, false otherwise
     */
    public function isSubfolderAllowed($user_id)
    {
        return $this->checkPermission($user_id);
    }

    /**
     * This function returns the suitable Icon for this folder type (GroupFolder)
     *
     * @return Icon The icon object for this folder type
     */
    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        $shape = $this->is_empty
               ? 'folder-group-empty'
               : 'folder-group-full';
        return Icon::create($shape, $role);
    }

    /**
     * This method returns the special part for the edit template for the folder type GroupFolder
     *
     * @return mixed  A edit template for a instance of the type GroupFolder
     */
    public function getEditTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/group_folder/edit.php');
        $group = Statusgruppen::find($this->folderdata['data_content']['group']);
        $template->set_attribute('group', $group);
        $template->set_attribute('folder', $this);
        return $template;
    }

    /**
     * Stores the data which was edited in the edit template
     * @return mixed The template with the edited data
     */
    public function setDataFromEditTemplate($request)
    {
        if ($request['group'] == null){
            return MessageBox::error(_('Es wurde keine gültige Gruppe ausgewählt.'));
        }

        $this->folderdata['data_content']['group'] = $request['group'];

        return parent::setDataFromEditTemplate($request);
    }

    /**
     * Returns the description template for a instance of a GroupFolder type
     *
     * @return mixed A description template for a instance of the type GroupFolder
     */
    public function getDescriptionTemplate()
    {
        $group = new Statusgruppen($this->folderdata['data_content']['group']);

        $template = $GLOBALS['template_factory']->open('filesystem/group_folder/description.php');
        $template->type       = self::getTypeName();
        $template->folder     = $this;
        $template->groupname  = $group->name;
        $template->folderdata = $this->folderdata;

        return $template;
    }
}
