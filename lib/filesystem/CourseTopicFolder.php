<?php
/**
 * CourseTopicFolder.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    André Noack <noack@data-quest.de>
 * @copyright 2016 Stud.IP Core-Group
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category  Stud.IP
 */
class CourseTopicFolder extends PermissionEnabledFolder implements FolderType
{

    public static $sorter = 1;

    private $topic;


    public static function getTypeName()
    {
        return _('Themen-Ordner');
    }

    public static function availableInRange($range_id_or_object, $user_id)
    {
        $course = Course::toObject($range_id_or_object);
        if ($course && !$course->isNew()) {
            return Seminar_Perm::get()->have_studip_perm('tutor', $course->id, $user_id) && CourseTopic::countBySql("seminar_id = ?" , [$course->id]);
        }
    }

    public function __construct($folderdata = null)
    {
        parent::__construct($folderdata);
        $this->getTopic();
    }

    public function getIcon($role = Icon::DEFAULT_ROLE)
    {
        return Icon::create(
            count($this->getFiles()) ? 'folder-topic-full' : 'folder-topic-empty',
            $role
        );
    }

    /**
     * @return CourseTopic
     */
    public function getTopic()
    {
        if (isset($this->folderdata['data_content']['topic_id'])) {
            if ($this->topic === null) {
                $this->topic = CourseTopic::find($this->folderdata['data_content']['topic_id']);
            }
            if ($this->topic) {
                $this->folderdata['name']        = (string) $this->topic->title;
                $this->folderdata['description'] = (string) $this->topic->description;
            } else {
                $this->folderdata['name'] = _('(Thema gelöscht)') . ' ' . $this->folderdata['name'];
            }
            return $this->topic;
        }
    }

    /**
     * @param CourseTopic $topic
     * @return CourseTopic
     */
    public function setTopic(CourseTopic $topic)
    {
        $this->topic = $topic;
        $this->folderdata['data_content']['topic_id'] = $this->topic->id;
        return $this->getTopic();
    }

    /**
     * This method returns the special part for the edit template for the folder type GroupFolder
     *
     * @return mixed  A edit template for a instance of the type GroupFolder
     */
    public function getEditTemplate()
    {
        $template = $GLOBALS['template_factory']->open('filesystem/topic_folder/edit.php');
        $template->set_attribute('topic', $this->getTopic());
        $template->set_attribute('folder', $this);
        return $template;
    }

    /**
     * Stores the data which was edited in the edit template
     * @return mixed The template with the edited data
     */
    public function setDataFromEditTemplate($request)
    {
        $topic = CourseTopic::find($request['topic_id']);
        if ($topic === null) {
            return MessageBox::error(_('Es wurde kein Thema ausgewählt.'));
        } else {
            if ($topic->id === $this->getTopic()->id) {
                if (!$request['name']) {
                    return MessageBox::error(_('Die Bezeichnung des Ordners fehlt.'));
                }
                $topic->title = $request['name'];
                $topic->description = $request['description'] ?: '';
                $topic->store();
            }
            $this->setTopic($topic);
        }

        if (isset($request['course_topic_folder_perm_write'])) {
            $this->folderdata['data_content']['permission'] = 7;
        } else {
            $this->folderdata['data_content']['permission'] = 5;
        }
        return $this;
    }

    /**
     * Returns the description template for a instance of a GroupFolder type
     *
     * @return mixed A description template for a instance of the type GroupFolder
     */
    public function getDescriptionTemplate()
    {

        $template = $GLOBALS['template_factory']->open('filesystem/topic_folder/description.php');
        $template->type       = self::getTypeName();
        $template->folder     = $this;
        $template->topic      = $this->getTopic();
        $template->folderdata = $this->folderdata;

        return $template;
    }

    /**
     * @see FolderType::copySettings()
     */
    public function copySettings()
    {
        return ['description' => $this->description];
    }

}
