<?php

/**
 * PreferentialAdmission.class.php
 *
 * An admission rule that favors selected courses of study or semesters of study.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require_once('lib/classes/admission/AdmissionRule.class.php');
require_once('lib/classes/admission/UserFilter.class.php');

class PreferentialAdmission extends AdmissionRule
{
    // --- ATTRIBUTES ---

    /**
     * Stores IDs of userlists generated for representing the selected
     * conditions. These lists are created on seat distribution in the course
     * set and are deleted immediately after.
     */
    public $userlists = [];

    /**
     * Conditions for selecting the favored people in seat distribution.
     */
    public $conditions = [];

    /**
     * Should higher semesters of study be favored?
     */
    public $favorSemester = false;

    /**
     * If semesters are favored, which bonus difference shall be set between
     * each semester of study?
     */
    public $bonus_difference = 100;

    /**
     * The courseset this rule belongs to.
     */
    public $courseset = null;

    // --- OPERATIONS ---

    /**
     * Standard constructor.
     *
     * @param  String ruleId If this rule has been saved previously, it
     *      will be loaded from database.
     * @return AdmissionRule the current object (this).
     */
    public function __construct($ruleId='')
    {
        $this->id = $ruleId;
        if ($ruleId) {
            $this->load();
        } else {
            $this->id = $this->generateId('prefadmissions');
        }
    }

    /**
     * Adds a new UserFilter to this rule.
     *
     * @param  UserFilter condition
     * @return PreferentialAdmission
     */
    public function addCondition($condition)
    {
        $this->conditions[$condition->getId()] = $condition;
        return $this;
    }

    /**
     * Hook that can be called after the seat distribution on the courseset
     * has completed. User lists that were generated before are removed now.
     */
    public function afterSeatDistribution($courseset)
    {
        foreach ($this->userlists as $id) {
            $current = new AdmissionUserList($id);
            $courseset->removeUserList($id);
            $current->delete();
        }
    }

    /**
     * Hook that can be called when the seat distribution on the courseset
     * starts. This type of admission rule gets all users that fulfill the
     * specified conditions and generates user lists with modified chances
     * in seat distribution.
     *
     * @param CourseSet The courseset this rule belongs to.
     */
    public function beforeSeatDistribution($courseset)
    {
        $this->courseset = $courseset;
        /*
         * First, we need to calculate the maximum of persons applying
         * for a single course as that number will influence the numbers
         * to set for preferation.
         */
        $this->bonus_difference = DBManager::get()->fetchColumn("SELECT MAX(users) FROM (
                SELECT `priority`, COUNT(DISTINCT `user_id`) AS users
                FROM `priorities`
                WHERE `set_id` = ?
                GROUP BY `priority`
            ) t", [$courseset->getId()]);
        $users = $this->getAffectedUsers();

        // No study semester variation, just put all users together.
        if (!$this->favorSemester) {

            $userlist = new AdmissionUserList();
            $userlist->setUsers($users)->setFactor($this->bonus_difference + 1)->store();
            $this->userlists[] = $userlist->getId();
            $courseset->addUserList($userlist->getId());

        // Study semesters need to be considered for differentiation...
        } else {
            /*
             * Build data grouped by semester of study for users affected
             * by given conditions.
             */
            if ($this->conditions) {
                $grouped = $this->getSemesterGroups($users, true);

                /*
                 * Build data grouped by semester of study for all users
                 * (excluding all users affected by given conditions).
                 */
                $rest = $this->getSemesterGroups(
                    array_keys(AdmissionPriority::getPriorities($courseset->getId())),
                    false, $users);

                /*
                 * Now set bonus factors to higher semesters. We are processing
                 * users not affected by conditions first so that we get the
                 * maximum bonus these users get and can build on top of that
                 * for users affected by conditions.
                 */
                $maxbonus = $this->setSemesterBonus($courseset, $rest);

                /*
                 * Finally, set bonuses for the users affected by conditions.
                 */
                $endbonus = $this->setSemesterBonus($courseset, $grouped, $maxbonus + 1);
            /*
             * No conditions given, just group all users
             * by their semester of study.
             */
            } else {
                // Build list of users by semester of study.
                $grouped = $this->getSemesterGroups(
                    array_keys(AdmissionPriority::getPriorities($courseset->getId())),
                    false);

                // Assign corresponding bonus to users.
                $maxbonus = $this->setSemesterBonus($courseset, $grouped);
            }
        }
    }

    /**
     * Deletes the admission rule and all associated data.
     */
    public function delete()
    {
        parent::delete();
        // Delete rule data.
        $stmt = DBManager::get()->prepare("DELETE FROM `prefadmissions`
            WHERE `rule_id`=?");
        $stmt->execute([$this->id]);
        // Delete all associated conditions...
        foreach ($this->conditions as $condition) {
            $condition->delete();
        }
        // ... and their connection to this rule.
        $stmt = DBManager::get()->prepare("DELETE FROM `prefadmission_condition`
            WHERE `rule_id`=?");
        $stmt->execute([$this->id]);
    }

    /**
     * Gets all users that are matched by thís rule.
     *
     * @return Array An array containing IDs of users who are matched by
     *      this rule.
     */
    public function getAffectedUsers()
    {
        $users = [];
        if ($this->conditions) {
            // Get users from all specified conditions.
            foreach ($this->conditions as $condition) {
                $users = array_unique(array_merge($users, $condition->getUsers()));
            }
        } else {
            $users = array_keys(AdmissionPriority::getPriorities($this->courseset->getId()));
        }
        return $users;
    }

    /**
     * Gets all defined conditions.
     *
     * @return Array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * Gets some text that describes what this AdmissionRule (or respective
     * subclass) does.
     */
    public static function getDescription()
    {
        return _('Sie können hier festlegen, dass bestimmte Studiengänge, '.
            'Fachsemester etc. bei der Platzverteilung zu Veranstaltungen '.
            'bevorzugt behandelt werden sollen.');
    }

    /**
     * Returns whether higher semesters of study should be favored.
     *
     * @return bool
     */
    public function getFavorSemester()
    {
        return $this->favorSemester;
    }

    /**
     * Return this rule's name.
     */
    public static function getName()
    {
        return _('Bevorzugte Anmeldung');
    }

    /**
     * Gets the semesters of study for the given users. If conditions are
     * set and should be considered, only the semesters of study belonging
     * to the given conditions are set.
     *
     * @param $users user IDs to process
     * @param $considerConditions should only the semesters of study belonging
     *                            to given conditions be considered?
     * @param array $exclude user IDs to exclude
     * @return array Users with their maximal semester of study.
     */
    public function getSemesterGroups($users, $considerConditions, $exclude = [])
    {
        /*
         * Get all selected condition values so that the study semester
         * can be matched against that data; we don't want some "general"
         * value for a user's study semester, but the one that is assigned
         * to a given subject and degree.
         */
        $queryParts = [];
        $values = [$users];
        if ($exclude) {
            $values[] = $exclude;
        }
        if ($considerConditions) {
            foreach ($this->conditions as $condition) {
                $queryPart = "";
                // Search for subject and degree entries.
                foreach ($condition->getFields() as $field) {
                    switch (get_class($field)) {
                        case 'DegreeCondition':
                            if ($queryPart) {
                                $queryPart .= " AND ";
                            }
                            $queryPart .= "`abschluss_id`".$field->getCompareOperator()."?";
                            $values[] = $field->getValue() ?: '';
                            break;
                        case 'SubjectCondition':
                            if ($queryPart) {
                                $queryPart .= " AND ";
                            }
                            $queryPart .= "`fach_id`".$field->getCompareOperator()."?";
                            $values[] = $field->getValue() ?: '';
                            break;
                        case 'SemesterOfStudyCondition':
                            if ($queryPart) {
                                $queryPart .= " AND ";
                            }
                            $queryPart .= "`semester`".$field->getCompareOperator()."?";
                            $values[] = $field->getValue() ?: '';
                            break;
                        default:
                            break;
                    }
                }
                if ($queryPart) {
                    $queryParts[] = $queryPart;
                }
            }
        }
        // Build SQL query with affected users and selected subjects and degrees.
        $query = "SELECT `user_id`, MAX(`semester`) AS semester
                FROM `user_studiengang`
                WHERE `user_id` IN (?)";
        if ($exclude) {
            $query .= " AND `user_id` NOT IN (?)";
        }
        if ($queryParts) {
            $query .= " AND ((".implode(") OR (", $queryParts)."))";
        }
        $query .= " GROUP BY `user_id` ORDER BY `semester`, `user_id`";
        $groups = [];
        foreach (DBManager::get()->fetchAll($query, $values) as $entry) {
            if (intval($entry['semester'])) {
                $groups[intval($entry['semester'])][] = $entry['user_id'];
            }
        }
        ksort($groups);
        return $groups;
    }

    /**
     * Gets the template that provides a configuration GUI for this rule.
     *
     * @return String
     */
    public function getTemplate()
    {
        $factory = new Flexi_TemplateFactory(__DIR__.'/templates/');
        // Now open specific template for this rule and insert base template.
        $tpl = $factory->open('configure');
        $tpl->set_attribute('rule', $this);
        return $tpl->render();
    }

    /**
     * Helper function for loading data from DB. Generic AdmissionRule data is
     * loaded with the parent load() method.
     */
    public function load()
    {
        // Load basic data.
        $stmt = DBManager::get()->prepare("SELECT *
            FROM `prefadmissions` WHERE `rule_id`=? LIMIT 1");
        $stmt->execute([$this->id]);
        if ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->favorSemester = $current['favor_semester'];
            // Retrieve conditions.
            $stmt = DBManager::get()->prepare("SELECT *
                FROM `prefadmission_condition` WHERE `rule_id`=?");
            $stmt->execute([$this->id]);
            $conditions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($conditions as $condition) {
                $currentCondition = new UserFilter($condition['condition_id']);
                $this->conditions[$condition['condition_id']] = $currentCondition;
            }
        }
    }

    /**
     * Removes the condition with the given ID from the rule.
     *
     * @param  String conditionId
     * @return PreferentialAdmission
     */
    public function removeCondition($conditionId)
    {
        $this->conditions[$conditionId]->delete();
        unset($this->conditions[$conditionId]);
        return $this;
    }

    /**
     * Admission is open for everyone. On seat distribution, the rule conditions
     * will be used to generate user lists with the specified chance.
     *
     * @param String $userId
     * @param String $courseId
     * @return Array Is the user allowed to register or are there any errors?
     */
    public function ruleApplies($userId, $courseId)
    {
        return [];
    }

    /**
     * Uses the given data to fill the object values. This can be used
     * as a generic function for storing data if the concrete rule type
     * isn't known in advance.
     *
     * @param Array $data
     * @return AdmissionRule This object.
     */
    public function setAllData($data)
    {
        UserFilterField::getAvailableFilterFields();
        parent::setAllData($data);
        $this->favorSemester = (bool) $data['favor_semester'];
        $this->conditions = [];
        if ($data['conditions']) {
            foreach ($data['conditions'] as $condition) {
                $this->addCondition(ObjectBuilder::build($condition, 'UserFilter'));
            }
        }
        return $this;
    }

    /**
     * New setting for favoring higher semesters of study.
     *
     * @param  bool $newFavorSemester
     * @return PreferentialAdmission
     */
    public function setFavorSemester($newFavorSemester) {
        $this->favorSemester = $newFavorSemester;
        return $this;
    }

    /**
     * Create user lists and set bonus corresponding to
     * the maximal available semester of study for given users.
     *
     * @param $courseset CourseSet to add user lists to
     * @param $grouped associative array of users in the form
     *               <semester> => array(<user_id1>, <user_id2, ...))
     * @param $baseBonus basic bonus to start with, defaults to 0.
     */
    public function setSemesterBonus($courseset, $grouped, $baseBonus = 1)
    {
        // Create user lists from each semester group.
        $bonus = $baseBonus;
        foreach ($grouped as $semester => $members) {
            $userlist = new AdmissionUserList();
            $userlist->setUsers($members);
            $userlist->setFactor($bonus);
            $userlist->store();
            $bonus = $bonus * ($this->bonus_difference + 1);
            $courseset->addUserList($userlist->getId());
            $this->userlists[] = $userlist->getId();
        }
        return $bonus;
    }

    /**
     * Helper function for storing data to DB.
     */
    public function store()
    {
        // Store rule data.
        $stmt = DBManager::get()->prepare("INSERT INTO `prefadmissions`
            (`rule_id`, `favor_semester`, `mkdate`, `chdate`)
            VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE
            `favor_semester`=VALUES(`favor_semester`),
            `chdate`=VALUES(`chdate`)");
        $stmt->execute([$this->id, $this->favorSemester, time(), time()]);
        // Delete removed conditions from DB.
        $entries = DBManager::get()->fetchAll("SELECT `condition_id` FROM
            `prefadmission_condition` WHERE `rule_id`=? AND `condition_id` NOT IN (?)",
            [$this->id, array_keys($this->conditions)]);
        foreach ($entries as $entry) {
            $current = new UserFilter($entry['condition_id']);
            $current->delete();
        }
        DBManager::get()->execute("DELETE FROM `prefadmission_condition`
            WHERE `rule_id`=? AND `condition_id` NOT IN (?)", [$this->id, array_keys($this->conditions)]);
        // Store all conditions.
        $queries = [];
        $parameters = [];
        if ($this->conditions) {
            foreach ($this->conditions as $condition) {
                // Store each condition...
                $condition->store();
                $queries[] = "(?, ?, ?)";
                $parameters[] = $this->id;
                $parameters[] = $condition->getId();
                $parameters[] = time();
            }
            // Store all assignments between rule and condition.
            $stmt = DBManager::get()->execute("INSERT INTO `prefadmission_condition`
                (`rule_id`, `condition_id`, `mkdate`)
                VALUES ".implode(",", $queries)." ON DUPLICATE KEY UPDATE
                `condition_id`=VALUES(`condition_id`)", $parameters);
        }
        return $this;
    }

    /**
     * A textual description of the current rule.
     *
     * @return String
     */
    public function toString()
    {
        $factory = new Flexi_TemplateFactory(__DIR__.'/templates/');
        $tpl = $factory->open('info');
        $tpl->set_attribute('rule', $this);
        return $tpl->render();
    }

    /**
     * Validates if the given request data is sufficient to configure this rule
     * (e.g. if required values are present).
     *
     * @param  Array Request data
     * @return Array Error messages.
     */
    public function validate($data)
    {
        $errors = parent::validate($data);
        if (!$data['conditions'] && !$data['favor_semester']) {
            $errors[] = _('Es muss mindestens eine Auswahlbedingung angegeben werden.');
        }
        return $errors;
    }

    public function __clone()
    {
        $this->id = md5(uniqid(get_class($this)));
        $this->courseSetId = null;
        $cloned_conditions = [];
        foreach ($this->conditions as $condition) {
            $dolly = clone $condition;
            $cloned_conditions[$dolly->id] = $dolly;
        }
        $this->conditions = $cloned_conditions;
    }

} /* end of class PreferentialAdmission */
