<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author     Rasmus Fuhse <fuhse@data-quest.de>
 * @copyright   2014 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string issue_id database column
 * @property string id alias column for issue_id
 * @property string seminar_id database column
 * @property string author_id database column
 * @property string title database column
 * @property string description database column
 * @property string priority database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property DocumentFolder folder belongs_to DocumentFolder
 * @property Course course belongs_to Course
 * @property User author belongs_to User
 * @property SimpleORMapCollection dates has_and_belongs_to_many CourseDate
 */

class CourseTopic extends SimpleORMap {

    static public function findByTermin_id($termin_id)
    {
        return self::findBySQL("INNER JOIN themen_termine USING (issue_id)
            WHERE themen_termine.termin_id = ?
            ORDER BY priority ASC",
            array($termin_id)
        );
    }

    static public function findBySeminar_id($seminar_id, $order_by = "ORDER BY priority")
    {
        return parent::findBySeminar_id($seminar_id, $order_by);
    }

    static public function findByTitle($seminar_id, $name)
    {
        return self::findOneBySQL("seminar_id = ? AND title = ?", array($seminar_id, $name));
    }

    static public function getMaxPriority($seminar_id)
    {
        return DbManager::get()->fetchColumn("SELECT MAX(priority) FROM themen WHERE seminar_id=?", array($seminar_id));
    }

    protected static function configure($config = array())
    {
        $config['db_table'] = 'themen';
        $config['has_and_belongs_to_many']['dates'] = array(
            'class_name' => 'CourseDate',
            'thru_table' => 'themen_termine',
            'order_by' => 'ORDER BY date',
            'on_delete' => 'delete',
            'on_store' => 'store'
        );
        $config['has_many']['folders'] = array(
            'class_name'  => 'Folder',
            'assoc_func' => 'findByTopic_id'
        );
        $config['belongs_to']['course'] = array(
            'class_name'  => 'Course',
            'foreign_key' => 'seminar_id'
        );
        $config['belongs_to']['author'] = array(
            'class_name'  => 'User',
            'foreign_key' => 'author_id'
        );

        $config['additional_fields']['forum_thread_url']['get'] = 'getForumThreadURL';

        parent::configure($config);
    }

    function __construct($id = null)
    {
        parent::__construct($id);
        $this->registerCallback('before_create', 'cbDefaultValues');
        $this->registerCallback('after_store', 'cbUpdateConnectedContentModules');
    }

    /**
    * set or update connection with document folder
    */
    function connectWithDocumentFolder()
    {
        if ($this->seminar_id) {
            $document_module = Seminar::getInstance($this->seminar_id)->getSlotModule('documents');
            if ($document_module) {
                if (!$this->folders->count()) {
                    $folder = new Folder();
                    $folder['range_id'] = $this['seminar_id'];
                    $folder['parent_id'] = Folder::findTopFolder($this['seminar_id'])->getId();
                    $folder['range_type'] = "course";
                    $folder['folder_type'] = "CourseTopicFolder";
                    $folder['data_content']['topic_id'] = $this->getId();
                    $folder['user_id'] = $GLOBALS['user']->id;
                    $folder['name'] = $this['title'];
                    $folder['description'] = $this['description'];
                    return $folder->store();
                }
            }
        }
        return false;
    }

    /**
    * set or update connection with forum thread
    */
    function connectWithForumThread()
    {
        if ($this->seminar_id) {
            $forum_module = Seminar::getInstance($this->seminar_id)->getSlotModule('forum');
            if ($forum_module instanceOf ForumModule) {
                $forum_module->setThreadForIssue($this->id, $this->title, $this->description);
                return true;
            }
        }
        return false;
    }

    function getForumThreadURL()
    {
        if ($this->seminar_id) {
            $forum_module = Seminar::getInstance($this->seminar_id)->getSlotModule('forum');
            if ($forum_module instanceOf ForumModule) {
                return html_entity_decode($forum_module->getLinkToThread($this->id));
            }
        }
        return '';
    }

    protected function cbUpdateConnectedContentModules()
    {
        if ($this->isFieldDirty('title') || $this->isFieldDirty('description')) {
            if ($this->forum_thread_url) {
                $this->connectWithForumThread();
            }
        }
    }

    protected function cbDefaultValues()
    {
        if (empty($this->content['priority'])) {
            $this->content['priority'] = self::getMaxPriority($this->seminar_id) + 1;
        }
    }

    /**
     * return all filerefs belonging to this topic, permissions fpr given user are checked
     *
     * @param string|User $user_or_id
     * @return mixed[] A mixed array with FolderType and FileRef objects.
     */
    public function getAccessibleFolderFiles($user_or_id)
    {
        $user_id = $user_or_id instanceof User ? $user_or_id->id : $user_or_id;
        $all_files = [];
        $all_folders = [];
        $folders = $this->folders->getArrayCopy();
        foreach ($this->dates as $date) {
            $folders = array_merge($folders, $date->folders->getArrayCopy());
        }
        foreach ($folders as $folder) {
            list($files, $typed_folders) = array_values(FileManager::getFolderFilesRecursive($folder->getTypedFolder(), $user_id));
            foreach ($files as $file) {
                $all_files[$file->id] = $file;
            }
            $all_folders = array_merge($all_folders, $typed_folders);
        }
        return ['files' => $all_files, 'folders' => $all_folders];
    }
}
