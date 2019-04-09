<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
// +--------------------------------------------------------------------------+
// This file is part of Stud.IP
// Issue.class.php
//
// Repräsentiert ein einzelnes Thema einer Veranstaltung
//
// +--------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +--------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +--------------------------------------------------------------------------+


/**
 * Issue.class.php
 *
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @version     19. Oktober 2005
 * @access      protected
 * @package     raumzeit
 */

class Issue {
    var $issue_id = '';
    var $seminar_id = '';
    var $author_id = '';
    var $title = '';
    var $description = '';
    var $mkdate = 0;
    var $chdate = 0;
    var $priority = 0;
    var $file = FALSE;
    var $folder_id = '';
    var $messages = [];
    var $new = false;
    var $hasForum = false;

    /**
     * Constructor for class Issue
     *
     * $data is an Array with the following possible fields:
     * issue_id:        when set, the Issue with this id is restored
     * seminar_id:  when set and issue_id is not set, a new issue for this seminar is created
     *
     * returns NULL if both are unset
     */
    function __construct($data = []) {
        global $user;

        if ($data['issue_id']) {
            $this->issue_id = $data['issue_id'];
            $this->restore();
        } else if ($data['seminar_id']) {
            $this->issue_id = md5(uniqid('Issue'));
            $this->seminar_id = $data['seminar_id'];
            $this->mkdate = time();
            $this->chdate = time();
            $this->author_id = $user->id;
            $this->new = true;
        } else {
            return NULL;
        }
    }

    function getPriority() {
        return $this->priority;
    }

    function setPriority($priority) {
        $this->priority = $priority;
    }

    function getMkDate() {
        return $this->mkdate;
    }

    function getChDate() {
        return $this->chdate;
    }

    function getTitle() {
        return $this->title;
    }

    function setTitle($title) {
        $this->title = $title;
    }

    function getDescription() {
        return $this->description;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function getAuthorID() {
        return $this->author_id;
    }

    function getIssueID() {
        return $this->issue_id;
    }

    function setSeminarID($seminar_id) {
        $this->seminar_id = $seminar_id;
    }

    function getSeminarID() {
        return $this->seminar_id;
    }

    function addSingleDate($termin_id) {
        $this->singleDates[] = $termin_id;
        return TRUE;
    }

    function readSingleDates() {
        /*if ($termin_data = IssueDB::getTermine($this->issue_id)) {
            foreach ($termin_data as $val) {
                $this->singleDates[] = $val['termin_id'];
            }
            return TRUE;
        }*/

        return FALSE;

    }

    function store() {
        $this->chdate = time();

        if ($this->hasForum) {
            $sem = Seminar::getInstance($this->seminar_id);
            $forum_slot = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$sem->status]['class']]->getSlotModule('forum');
            
            foreach (PluginEngine::getPlugins('ForumModule') as $plugin) {
                if (get_class($plugin) == $forum_slot) {
                    $plugin->setThreadForIssue($this->issue_id, $this->title, $this->description);
                }
            }
        }
        
        IssueDB::storeIssue($this);
        $this->new = false;
    }

    function restore() {
        /*
         * To avoid inconsistency, the restore function has been removed.
         * The only way to load an Issue is via the Seminar.class.php, with the function fillValuesFromArray
         */
        $this->fillValuesFromArray(IssueDB::restoreIssue($this->issue_id));
    }

    function delete() {
        $dates = IssueDB::getDatesforIssue($this->issue_id);

        $titles = [];
        $title = '';

        foreach ($dates as $termin_id => $termin_data) {
            $titles[] = date('d.m.y, H:i', $termin_data['date']).' - '.date('H:i', $termin_data['end_time']);
        }

        if (sizeof($titles) > 0) {
            $title = implode(', ', $titles).', '.$this->getTitle() . ' ' ._("(Thema gelöscht)");
        } else {
            $title = $this->getTitle() . ' ' ._("(Thema gelöscht)");
        }
        $description = _("Dateiordner bezieht sich auf ein nicht mehr vorhandenes Thema.");

        IssueDB::deleteIssue($this->issue_id, $this->seminar_id, $title, $description);
    }

    function fillValuesFromArray($data) {
        $this->issue_id = $data['issue_id'];
        $this->seminar_id = $data['seminar_id'];
        $this->author_id = $data['author_id'];
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->mkdate = $data['mkdate'];
        $this->chdate = $data['chdate'];
        $this->priority = $data['priority'];
        $this->file = ($data['range_id'] == '') ? FALSE : TRUE;
        if ($this->file) {
            $this->folder_id = $data['folder_id'];      
        }
        $this->new = false;

        // check, if there is a forums-connection
        $sem = Seminar::getInstance($this->seminar_id);
        $forum_slot = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$sem->status]['class']]->getSlotModule('forum');
            
        foreach (PluginEngine::getPlugins('ForumModule') as $plugin) {
            if (get_class($plugin) == $forum_slot) {
                $this->hasForum = $plugin->getLinkToThread($this->issue_id) ? true : false;
            }
        }

        $this->readSingleDates();
    }

    function toString() {
        return $this->title;
    }

    function getFolderID() {
        if ($this->file) {
            return $this->folder_id;
        } else {
            return FALSE;
        }
    }

    function hasFile() {
        return $this->file;
    }

    function setFile($file) {
        if ($file != $this->file) {
            if ($file) {
                $this->messages[] = sprintf(_("Dateiordner für das Thema \"%s\" angelegt."),$this->toString());
            } else {
                //$this->messages[] = sprintf(_("Dateiordner für das Thema \"%s\" gelöscht!"),$this->toString());
            }
        }
        $this->file = $file;
    }

    function setForum($newForumValue) {
        // only do something, if we enable the link to a thread in a forum
        if ($newForumValue) {

            // find the ForumModule which takes the role of the CoreForum in the current Seminar
            $sem = Seminar::getInstance($this->seminar_id);
            $forum_slot = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$sem->status]['class']]->getSlotModule('forum');
            
            foreach (PluginEngine::getPlugins('ForumModule') as $plugin) {
                if (get_class($plugin) == $forum_slot) {
                    
                    // only link if there is none yet
                    if (!$plugin->getLinkToThread($this->issue_id)) {
                        $plugin->setThreadForIssue($this->issue_id, $this->title, $this->description);
                        $this->messages[] = sprintf(_("Ordner im Forum für das Thema \"%s\" angelegt."), $this->toString());
                    }
                }
            }
        }
    }

    function getMessages() {
        $temp = $this->messages;
        $this->messages = NULL;
        return $temp;
    }

  /* * * * * * * * * * * * * * * * * * * * 
     * * S T A T I C   F U N C T I O N S * * 
   * * * * * * * * * * * * * * * * * * * */
     
    function isIssue($issue_id) {
        return IssueDB::isIssue($issue_id);
    }
}