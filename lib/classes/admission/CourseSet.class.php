<?php

/**
 * CourseSet.class.php
 *
 * Represents groups of Stud.IP courses that have common rules for admission.
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

class CourseSet
{
    // --- ATTRIBUTES ---

    /**
     * Admission rules that are applied to the courses belonging to this set.
     */
    protected $admissionRules = [];

    /**
     * Seat distribution algorithm.
     */
    protected $algorithm = null;

    /**
     * IDs of courses that are aggregated into this set. The array is in the
     * form ($courseId1 => true, $courseId2 => true).
     */
    protected $courses = [];

    /**
     * Has the seat distribution algorithm already been executed?
     */
    protected $hasAlgorithmRun = false;

    /**
     * Unique identifier for this set.
     */
    protected $id = '';

    /**
     * Some extensive descriptional text for informing confused students.
     */
    protected $infoText = '';

    /**
     * Which Stud.IP institute does the course set belong to?
     */
    protected $institutes = [];

    /**
     * Some display name for this course set.
     */
    protected $name = '';

    /**
     * Is the course set only visible for the creator?
     */
    protected $private = false;

    /**
     * Who owns this course set?
     */
    protected $user_id = false;

    /*
     * Lists of users who are treated differently on seat distribution
     */
    protected $userlists = [];

    // --- OPERATIONS ---

    public function __construct($setId='') {
        $this->id = $setId;
        $this->name = _("Anmeldeset");
        $this->algorithm = new RandomAlgorithm();
        // Autoload admission rules.
        AdmissionRule::getAvailableAdmissionRules();
        // Define autoload function for admission rules.
        spl_autoload_register(['UserFilterField', 'getAvailableFilterFields']);
        if ($setId) {
            $this->load();
        }
    }

    /**
     * Adds the given admission rule to the list of rules for the course set.
     *
     * @param  AdmissionRule rule
     * @return CourseSet
     */
    public function addAdmissionRule($rule)
    {
        $this->admissionRules[$rule->getId()] = $rule;
        return $this;
    }

    /**
     * Adds the course with the given ID to the course set.
     *
     * @param  String courseId
     * @return CourseSet
     */
    public function addCourse($courseId)
    {
        $this->courses[$courseId] = true;
        return $this;
    }

    /**
     * Adds a new institute ID.
     *
     * @param  String newId
     * @return CourseSet
     */
    public function addInstitute($newId) {
        $this->institutes[$newId] = true;
    return $this;
    }

    /**
     * Adds several institute IDs to the existing institute assignments.
     *
     * @param  Array newIds
     * @return CourseSet
     */
    public function addInstitutes($newIds) {
        foreach ($newIds as $newId) {
            $this->addInstitute($newId);
        }
        return $this;
    }

    /**
     * Adds a UserList to the course set. The list contains several users and a
     * factor that changes seat distribution chances for these users;
     *
     * @param  String listId
     * @return CourseSet
     */
    public function addUserList($listId)
    {
        $this->userlists[$listId] = true;
        return $this;
    }

    /**
     * Is the given user allowed to register as participant in the given
     * course according to the rules of this course set?
     *
     * @param  String userId
     * @param  String courseId
     * @return Array Optional error messages from rules if something went wrong.
     */
    public function checkAdmission($userId, $courseId) {
        $errors = [];
        foreach ($this->admissionRules as $rule) {
            // All rules must be fulfilled.
            $ruleCheck = $rule->ruleApplies($userId, $courseId);
            if ($ruleCheck) {
                $errors = array_merge($errors, $ruleCheck);
            }
        }
        return $errors;
    }

    /**
     * Removes all admission rules at once.
     *
     * @return CourseSet
     */
    public function clearAdmissionRules() {
        $this->admissionRules = [];
        return $this;
    }

    /**
     * Deletes the course set and all associated data.
     */
    public function delete() {
        NotificationCenter::postNotification('CourseSetWillDelete', $this->id, $GLOBALS['user']->id);
        // Delete institute associations.
        $stmt = DBManager::get()->prepare("DELETE FROM `courseset_institute`
            WHERE `set_id`=?");
        $stmt->execute([$this->id]);
        // Delete course associations.
        $stmt = DBManager::get()->prepare("DELETE FROM `seminar_courseset`
            WHERE `set_id`=?");
        $stmt->execute([$this->id]);
        // Delete all rules...
        foreach ($this->admissionRules as $rule) {
            $rule->delete();
        }
        // ... and their association to the current course set.
        $stmt = DBManager::get()->prepare("DELETE FROM `courseset_rule`
            WHERE `set_id`=?");
        $stmt->execute([$this->id]);
        // Delete associations to user lists.
        $stmt = DBManager::get()->prepare("DELETE FROM `courseset_factorlist`
            WHERE `set_id`=?");
        $stmt->execute([$this->id]);
        // Delete course set data.
        $stmt = DBManager::get()->prepare("DELETE FROM `coursesets`
            WHERE `set_id`=?");
        $stmt->execute([$this->id]);
        /*
         * Delete waiting lists
         */
        foreach ($this->courses as $id => $assigned) {
            AdmissionApplication::deleteBySQL("status='awaiting' AND seminar_id=?", [$id]);
        }
        //Delete priorities
        AdmissionPriority::unsetAllPriorities($this->getId());
        NotificationCenter::postNotification('CourseSetDidDelete', $this->id, $GLOBALS['user']->id);
    }

    /**
     * Starts the seat distribution algorithm.
     *
     * @return CourseSet
     */
    public function distributeSeats() {
        if ($this->algorithm) {
            // Call pre-distribution hooks on all assigned rules.
            foreach ($this->admissionRules as &$rule) {
                $rule->beforeSeatDistribution($this);
            }
            $this->algorithm->run($this);
            // Mark as "seats distributed".
            $this->setAlgorithmRun(true);
            // Call post-distribution hooks on all assigned rules.
            foreach ($this->admissionRules as &$rule) {
                $rule->afterSeatDistribution($this);
            }
            AdmissionPriority::unsetAllPriorities($this->getId());
        }
    }

    public function setAlgorithmRun($state)
    {
        NotificationCenter::postNotification('CourseSetAlgorithmWillStart', $state, $this->getId());
        $this->hasAlgorithmRun = (bool)$state;
        $db = DbManager::get();
        $ok = $db->execute("UPDATE coursesets SET algorithm_run = ? WHERE set_id = ?", [$this->hasAlgorithmRun, $this->getId()]);
        if ($ok) {
            NotificationCenter::postNotification('CourseSetAlgorithmDidStart', $state, $this->getId());
        }
        return $ok;
    }

    /**
     * returns true if the set allows only a limited number of places
     *
     * @return boolean
     */
    public function isSeatDistributionEnabled()
    {
        return $this->getSeatDistributionTime() !== null;
    }

    /**
     * returns timestamp of distribution time or null if no distribution time available
     * may return 0 if first-come-first-serve is enabled
     *
     * @return integer|null timestamp of distribution
     */
    public function getSeatDistributionTime()
    {
        $pr_admission = $this->getAdmissionRule('ParticipantRestrictedAdmission');
        if ($pr_admission) {
            return $pr_admission->getDistributionTime();
        }
    }

    /**
     * Get all admission rules belonging to the course set.
     *
     * @return AdmissionRule[]
     */
    public function getAdmissionRules()
    {
        return $this->admissionRules;
    }

    public function getAdmissionRule($class_name)
    {
        return array_pop(array_filter($this->getAdmissionRules(), function($r) use ($class_name) {
            return $r instanceof $class_name;}));
    }
    /**
     * check if course set has given admission rule
     *
     * @param string $rule name of AdmissionRule class
     * @return boolean
     */
    public function hasAdmissionRule($rule)
    {
        return is_object($this->getAdmissionRule($rule));
    }

    /**
     * Get the currently used distribution algorithm.
     *
     * @return AdmissionAlgorithm
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * How many users will be allowed to register according to the defined
     * rules? This can help in estimating whether the combination of the
     * defined rules makes sense.
     *
     * @return int
     */
    public function getAllowedUserCount()
    {
        $users = [];
        foreach ($this->admissionRules as $rule) {
            $users = array_merge($users, $rule->getAffectedUsers());
        }
        return sizeof($users);
    }

    /**
     * Gets the course IDs belonging to the course set.
     *
     * @return Array
     */
    public function getCourses()
    {
        return array_keys($this->courses);
    }

    /**
     * Gets all courses belonging to the given course set ID.
     *
     * @param String $courseSetId
     * @return Array
     */
    public static function getCoursesByCourseSetId($courseSetId)
    {
        $query = "SELECT `seminar_id`
                  FROM `seminar_courseset`
                  WHERE `set_id` = ?";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute([$courseSetId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets all course sets belonging to the given institute ID.
     *
     * @param String $instituteId
     * @return Array
     */
    public static function getCoursesetsByInstituteId($instituteId, $filter = []) {
        $query = "SELECT DISTINCT ci.*
            FROM `courseset_institute` ci
            JOIN `coursesets` c ON (ci.`set_id`=c.`set_id`)
            LEFT JOIN courseset_rule cr ON cr.set_id=ci.set_id
            LEFT JOIN seminar_courseset sc ON c.set_id = sc.set_id
            LEFT JOIN seminare s ON s.seminar_id = sc.seminar_id
            WHERE ci.`institute_id`=?";
        $parameters = [$instituteId];
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $query .= " AND (c.`private`=0 OR c.`user_id`=?)";
            $parameters[] = $GLOBALS['user']->id;
        }
        if ($filter['course_set_name']) {
            $query .= " AND c.name LIKE ?";
            $parameters[] = $filter['course_set_name'] . '%';
        }
        if (is_array($filter['rule_types']) && count($filter['rule_types'])) {
            $query .= " AND cr.type IN (?)";
            $parameters[] = $filter['rule_types'];
        }
        if ($filter['semester_id']) {
            $query .= " AND s.start_time = ?";
            $parameters[] = Semester::find($filter['semester_id'])->beginn;
        }
        if ($filter['course_set_chdate']) {
            $query .= " AND c.chdate < ?";
            $parameters[] = $filter['chdate'];
        }
        $query .= " ORDER BY c.name";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute($parameters);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets all global course sets
     *
     * @param array $filter
     * @return Array
     */
    public static function getGlobalCoursesets($filter = []) {
        $query = "SELECT DISTINCT c.set_id
            FROM coursesets c
            LEFT JOIN courseset_institute ci ON ci.`set_id`=c.`set_id`
            LEFT JOIN courseset_rule cr ON cr.set_id=ci.set_id
            LEFT JOIN seminar_courseset sc ON c.set_id = sc.set_id
            LEFT JOIN seminare s ON s.seminar_id = sc.seminar_id
            WHERE ci.institute_id IS NULL";
        $parameters = [];
        $query .= " AND (c.`private`=0 OR c.`user_id`=?)";
        $parameters[] = $GLOBALS['user']->id;
        if ($filter['course_set_name']) {
            $query .= " AND c.name LIKE ?";
            $parameters[] = $filter['course_set_name'] . '%';
        }
        if (is_array($filter['rule_types']) && count($filter['rule_types'])) {
            $query .= " AND cr.type IN (?)";
            $parameters[] = $filter['rule_types'];
        }
        if ($filter['semester_id']) {
            $query .= " AND s.start_time = ?";
            $parameters[] = Semester::find($filter['semester_id'])->beginn;
        }
        if ($filter['course_set_chdate']) {
            $query .= " AND c.chdate < ?";
            $parameters[] = $filter['chdate'];
        }
        $query .= " ORDER BY c.name";
        $stmt = DBManager::get()->prepare($query);
        $stmt->execute($parameters);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the identifier of the course set.
     *
     * @return String
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the course set's info text.
     *
     * @return String
     */
    public function getInfoText()
    {
        return $this->infoText;
    }

    /**
     * Which institutes does the rule belong to?
     *
     * @return Array
     */
    public function getInstituteIds() {
        return $this->institutes;
    }

    public function isGlobal()
    {
        return count($this->institutes) == 0;
    }
    /**
     * Gets this course set's display name.
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Retrieves the priorities given to the courses in this set.
     *
     * @return Array
     */
    public function getPriorities()
    {
        return AdmissionPriority::getPriorities($this->id);
    }

    public function getNumApplicants()
    {
        return AdmissionPriority::getPrioritiesCount($this->id);
    }

    /**
     * Is the current courseset private?
     *
     * @return bool
     */
    public function getPrivate() {
        return $this->private;
    }


    /**
     * returns latest semester id from assigned courses
     * if no courses are assigned current semester
     *
     * @return string id of semester
     */
    public function getSemester() {
        $db = DBManager::get();
        $timestamp = $db->fetchColumn("SELECT MAX(start_time + duration_time)
                 FROM seminare WHERE duration_time <> -1
                 AND seminar_id IN(?)", [$this->getCourses()]);
        $semester = Semester::findByTimestamp($timestamp);
        if (!$semester) {
            $semester = $_SESSION['_default_sem'] ? Semester::find($_SESSION['_default_sem']) : Semester::findCurrent();
        }
        return $semester->id;
    }

    /**
     * Gets the owner of this course set.
     */
    public function getUserId() {
        return $this->user_id;
    }

    public function setUserId($user_id) {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * Gets the course sets the given course belongs to.
     *
     * @param  String courseId
     * @return CourseSet
     */
    public static function getSetForCourse($courseId)
    {
        $stmt = DBManager::get()->prepare("SELECT `set_id`
            FROM `seminar_courseset` WHERE `seminar_id`=?");
        $stmt->execute([$courseId]);
        $set_id = $stmt->fetchColumn();
        if ($set_id) {
            return new CourseSet($set_id);
        }
        return null;
    }

    /**
     * Gets the course sets the given rule belongs to.
     *
     * @param  String $rule_id
     * @return CourseSet
     */
    public static function getSetForRule($rule_id)
    {
        $set_id = DBManager::get()->fetchColumn("SELECT `set_id`
            FROM `courseset_rule` WHERE `rule_id`=?", [$rule_id]);
        if ($set_id) {
            return new CourseSet($set_id);
        }
        return null;
    }

    /**
     * Retrieves the lists of users that are considered specially in
     * seat distribution.
     *
     * @return Array
     */
    public function getUserLists()
    {
        return array_keys($this->userlists);
    }

    public function getUserFactorList()
    {
        $factored_users = [];
        foreach ($this->getUserLists() as $ul_id) {
            $user_list = new AdmissionUserList($ul_id);
            $factored_users = array_merge($factored_users,
                                 array_combine(array_keys($user_list->getUsers()),
                                         array_fill(0, count($user_list->getUsers()), $user_list->getFactor())
                                         )
                    );
        }
        return $factored_users;
    }

    /**
     * Evaluates whether the seat distribution algorithm has already been
     * executed on this course set.
     *
     * @return boolean True if algorithm has already run, otherwise false.
     */
    public function hasAlgorithmRun() {
        return $this->hasAlgorithmRun;
    }

    /**
     * Helper function for loading data from DB.
     */
    public function load() {
        // Load basic data.
        $stmt = DBManager::get()->prepare(
            "SELECT * FROM `coursesets` WHERE set_id=? LIMIT 1");
        $stmt->execute([$this->id]);
        if ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->name = $data['name'];
            $this->infoText = $data['infotext'];
            $this->hasAlgorithmRun = (bool)$data['algorithm_run'];
            if ($data['algorithm']) {
                if (class_exists($data['algorithm'])) {
                    $this->algorithm = new $data['algorithm']();
                }
            }
            $this->private = (bool) $data['private'];
            $this->user_id = $data['user_id'];
            $this->chdate = $data['chdate'];
        }
        // Load institute assigments.
        $stmt = DBManager::get()->prepare("
            SELECT courseset_institute.institute_id
            FROM `courseset_institute`
                INNER JOIN Institute ON (courseset_institute.institute_id = Institute.Institut_id)
            WHERE courseset_institute.set_id = ?
            ORDER BY Institute.Name ASC
        ");
        $stmt->execute([$this->id]);
        $this->institutes = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->institutes[$data['institute_id']] = true;
        }
        // Load courses.
        $stmt = DBManager::get()->prepare(
            "SELECT seminar_id FROM `seminar_courseset` WHERE set_id=?");
        $stmt->execute([$this->id]);
        $this->courses = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->courses[$data['seminar_id']] = true;
        }
        // Load admission rules.
        $stmt = DBManager::get()->prepare(
            "SELECT * FROM `courseset_rule` WHERE set_id=?");
        $stmt->execute([$this->id]);
        $this->admissionRules = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (class_exists($data['type'])) {
                $this->admissionRules[$data['rule_id']] =
                    new $data['type']($data['rule_id'], $this->id);
            }
        }
        // Load assigned user lists.
        $stmt = DBManager::get()->prepare("SELECT `factorlist_id`
            FROM `courseset_factorlist` WHERE `set_id`=?");
        $stmt->execute([$this->id]);
        $this->userlists = [];
        while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->userlists[$current['factorlist_id']] = true;
        }
        return $this;
    }

    /**
     * Removes the course with the given ID from the set.
     *
     * @param  String courseId
     * @return CourseSet
     */
    public function removeCourse($courseId)
    {
        unset($this->courses[$courseId]);
        return $this;
    }

    /**
     * Removes the rule with the given ID from the set.
     *
     * @param  String ruleId
     * @return CourseSet
     */
    public function removeAdmissionRule($ruleId)
    {
        unset($this->admissionRules[$ruleId]);
        return $this;
    }

    /**
     * Removes the institute with the given ID from the set.
     *
     * @param  String instituteId
     * @return CourseSet
     */
    public function removeInstitute($instituteId)
    {
        unset($this->institutes[$instituteId]);
        return $this;
    }

    /**
     * Removes the user list with the given ID from the set.
     *
     * @param  String listId
     * @return CourseSet
     */
    public function removeUserlist($listId)
    {
        unset($this->userlists[$listId]);
        return $this;
    }

    /**
     * Adds several admission rules after clearing the existing rule
     * assignments.
     *
     * @param  Array newIds
     * @return CourseSet
     */
    public function setAdmissionRules($newRules) {
        $this->admissionRules = [];
        foreach ($newRules as $newRule) {
            $rule = ObjectBuilder::build($newRule, 'AdmissionRule');
            $this->addAdmissionRule($rule);
        }
        return $this;
    }

    /**
     * Sets a seat distribution algorithm for this course set. This will only
     * have an effect in conjunction with a TimedAdmission, as the algorithm
     * needs a defined point in time where it will start.
     *
     * @param  String newAlgorithm
     * @return CourseSet
     */
    public function setAlgorithm($newAlgorithm)
    {
        try {
            $this->algorithm = new $newAlgorithm();
        } catch (Exception $e) {
        }
        return $this;
    }

    /**
     * Adds several course IDs after clearing the existing course
     * assignments.
     *
     * @param  Array newIds
     * @return CourseSet
     */
    public function setCourses($newIds) {
        $this->courses = array_fill_keys($newIds, true);
        return $this;
    }

    /**
     * Adds several institute IDs after clearing the existing institute
     * assignments.
     *
     * @param  Array newIds
     * @return CourseSet
     */
    public function setInstitutes($newIds) {
        $this->institutes = [];
        $this->addInstitutes($newIds);
        return $this;
    }

    /**
     * Set the course set's info text.
     *
     * @return CourseSet
     */
    public function setInfoText($newText)
    {
        $this->infoText = $newText;
        return $this;
    }

    /* Sets a new name for this course set.
     *
     * @param  String newName
     * @return CourseSet
     */
    public function setName($newName) {
        $this->name = $newName;
        return $this;
    }

    /**
     * Set a new value for courseset privacy.
     *
     * @param  bool $newPrivate
     * @return CourseSet
     */
    public function setPrivate($newPrivate) {
        $this->private = $newPrivate;
        return $this;
    }

    /**
     * Adds several user list IDs after clearing the existing user list
     * assignments.
     *
     * @param  Array newIds
     * @return CourseSet
     */
    public function setUserlists($newIds) {
        $this->userlists = [];
        foreach ($newIds as $newId) {
            $this->addUserlist($newId);
        }
        return $this;
    }

    public function store() {
        // Generate new ID if course set doesn't exist in DB yet.
        if (!$this->id) {
            do {
                $newid = md5(uniqid(get_class($this), true));
                $db = DBManager::get()->query("SELECT `set_id`
                    FROM `coursesets` WHERE `set_id`='$newid'");
            } while ($db->fetch());
            $this->id = $newid;
        }
        if (!$this->user_id) {
            $this->user_id = $GLOBALS['user']->id;
        }
        if ($this->isSeatDistributionEnabled()) {
            if (!$this->getAlgorithm()) {
                $algorithm = new RandomAlgorithm();
                $this->setAlgorithm($algorithm);
            }
            if (!$this->getSeatDistributionTime()) {
                $this->setAlgorithmRun(true);
                //Delete priorities
                AdmissionPriority::unsetAllPriorities($this->getId());
            }
            if ($this->getSeatDistributionTime() > time()) {
                $this->setAlgorithmRun(false);
            }
        }
        // Store basic data.
        $stmt = DBManager::get()->prepare("INSERT INTO `coursesets`
            (`set_id`, `user_id`, `name`, `infotext`, `algorithm`, `algorithm_run`,
            `private`, `mkdate`, `chdate`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE
            `name`=VALUES(`name`), `infotext`=VALUES(`infotext`),
            `algorithm`=VALUES(`algorithm`), `algorithm_run`=VALUES(`algorithm_run`), `private`=VALUES(`private`),
            `chdate`=VALUES(`chdate`)");
        $stmt->execute([$this->id, $this->user_id, $this->name, $this->infoText,
            get_class($this->algorithm), $this->hasAlgorithmRun(), intval($this->private), time(), time()]);
        // Delete removed institute assignments from database.
        DBManager::get()->exec("DELETE FROM `courseset_institute`
            WHERE `set_id`='".$this->id."' AND `institute_id` NOT IN ('".
            implode("', '", array_keys($this->institutes))."')");
        // Store associated institute IDs.
        foreach ($this->institutes as $institute => $associated) {
            $stmt = DBManager::get()->prepare("INSERT IGNORE INTO `courseset_institute`
                (`set_id`, `institute_id`, `mkdate`)
                VALUES (?, ?, ?)");
            $stmt->execute([$this->id, $institute, time()]);
        }
        // log removed course assignments.
        DBManager::get()->fetchAll("SELECT seminar_id,set_id FROM `seminar_courseset`
            WHERE `set_id` = ? AND `seminar_id` NOT IN (?)", [$this->id, count($this->courses) ? array_keys($this->courses) : ''],
            function ($row) {
                StudipLog::log('SEM_CHANGED_ACCESS', $row['seminar_id'],
                null, 'Entfernung von Anmeldeset', sprintf('Anmeldeset: %s', $row['set_id']));
                //Delete priorities
                AdmissionPriority::unsetAllPrioritiesForCourse($row['seminar_id']);
                Course::buildExisting(['seminar_id' => $row['seminar_id']])->triggerChdate();
            });
        //removed course assignments
        DBManager::get()->execute("DELETE FROM `seminar_courseset`
            WHERE `set_id` = ? AND `seminar_id` NOT IN (?)", [$this->id, count($this->courses) ? array_keys($this->courses) : '']);
        //log removing other associations
        DBManager::get()->execute("SELECT seminar_id,set_id FROM `seminar_courseset`
            WHERE `set_id` <> ? AND `seminar_id` IN (?)", [$this->id, array_keys($this->courses)],
            function ($row) {
                StudipLog::log('SEM_CHANGED_ACCESS', $row['seminar_id'],
                null, 'Entfernung von Anmeldeset', sprintf('Anmeldeset: %s', $row['set_id']));
                //Delete priorities
                AdmissionPriority::unsetAllPrioritiesForCourse($row['seminar_id']);
                Course::buildExisting(['seminar_id' => $row['seminar_id']])->triggerChdate();
            });
        //Delete other associations, only one set possible
        DBManager::get()->execute("DELETE FROM `seminar_courseset`
            WHERE `set_id` <> ? AND `seminar_id` IN (?)", [$this->id, array_keys($this->courses)]);
        // Store associated course IDs.
        foreach ($this->courses as $course => $associated) {
            $stmt = DBManager::get()->prepare("INSERT IGNORE INTO `seminar_courseset`
                (`set_id`, `seminar_id`, `mkdate`)
                VALUES (?, ?, ?)");
            $stmt->execute([$this->id, $course, time()]);
            if ($stmt->rowCount()) {
                StudipLog::log('SEM_CHANGED_ACCESS', $course,
                null, 'Zuordnung zu Anmeldeset', sprintf('Anmeldeset: %s', $this->id));
                Course::buildExisting(['seminar_id' => $course])->triggerChdate();
            }
        }

        // Delete removed user list assignments from database.
        DBManager::get()->exec("DELETE FROM `courseset_factorlist`
            WHERE `set_id`='".$this->id."' AND `factorlist_id` NOT IN ('".
            implode("', '", array_keys($this->userlists))."')");
        // Store associated user list IDs.
        foreach ($this->userlists as $list => $associated) {
            $stmt = DBManager::get()->prepare("INSERT IGNORE INTO `courseset_factorlist`
                (`set_id`, `factorlist_id`, `mkdate`)
                VALUES (?, ?, ?)");
            $stmt->execute([$this->id, $list, time()]);
        }
        // Delete removed admission rules from database.
        $stmt = DBManager::get()->query("SELECT `rule_id`, `type` FROM `courseset_rule`
            WHERE `set_id`='".$this->id."' AND `rule_id` NOT IN ('".
            implode("', '", array_keys($this->admissionRules))."')");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as $ruleData) {
            $rule = new $ruleData['type']($ruleData['rule_id']);
            $rule->delete();
        }
        // Store all rules.
        foreach ($this->admissionRules as $rule) {
            // Store each rule...
            $rule->store();
            // ... and its connection to the current course set.
            $stmt = DBManager::get()->prepare("INSERT INTO `courseset_rule`
                (`set_id`, `rule_id`, `type`, `mkdate`)
                VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE
                `type`=VALUES(`type`)");
            $stmt->execute([$this->id, $rule->getId(), get_class($rule), time()]);
        }
        //fix free access courses
        if (count($this->courses)) {
            DBManager::get()->execute("UPDATE seminare SET Lesezugriff=1,Schreibzugriff=1 WHERE seminar_id IN(?)", [array_keys($this->courses)]);
        }
        //create general log
        $this->log_store();

    }

    /**
     * Generates a general log entry if the CourseSet rules were changed.
     */
    private function log_store()
    {
        $text = '';
        $rule_counter = 1;
        foreach ($this->getAdmissionRules() as $rule) {
            $rule_text = strip_tags(kill_format($rule->toString()));
            $semicolon = ($rule_counter < count($this->getAdmissionRules()) ? '; ' : '');
            $text .= '#' . $rule_counter . ' => ' . $rule_text . $semicolon;
            $rule_counter++;
        }
        
        $courses = $this->getCourses();
        foreach ($courses as $course_id) {
            StudipLog::log(
                'SEM_CHANGED_ACCESS',
                $course_id,
                NULL,
                $text,
                sprintf('Anmeldeset: %s (%s)', strip_tags(kill_format(($this->name))), $this->id)
            );
        }
    }

    /**
     * A textual description of the current rule.
     *
     * @param bool short Show only short info without overview of assigned
     *                   courses and institutes.
     * @return String
     */
    public function toString($short=false) {
        $tpl = $GLOBALS['template_factory']->open('admission/courseset/info');
        $tpl->set_attribute('courseset', $this);
        $institutes = [];
        if (!$short) {
            $institutes = Institute::findAndMapMany(function($i) {return $i->name;}, array_keys($this->institutes), 'ORDER BY Name');
            $tpl->set_attribute('institutes', $institutes);
        }
        if (!$short || $this->hasAdmissionRule('LimitedAdmission')) {
            $courses = Course::findAndMapMany(function($c) {
                return ['id' => $c->id,
                             'name' => $c->getFullname('number-name-semester')];
            },
                array_keys($this->courses),
                'ORDER BY start_time,VeranstaltungsNummer,Name');
            $tpl->set_attribute('is_limited', $this->hasAdmissionRule('LimitedAdmission'));
            $tpl->set_attribute('courses', $courses);
        }
        $tpl->set_attribute('short', $short);
        return $tpl->render();
    }

    public function __toString() {
        return $this->toString();
    }

    /**
     * is user with given user id allowed to assign/unassign given course to courseset
     *
     * @param string $user_id
     * @param string $course_id
     * @return boolean
     */
    public function isUserAllowedToAssignCourse($user_id, $course_id)
    {
        global $perm;
        $is_dozent = $perm->have_studip_perm('tutor', $course_id, $user_id);
        $is_admin = $perm->have_perm('admin', $user_id) || ($perm->have_perm('dozent', $user_id) && get_config('ALLOW_DOZENT_COURSESET_ADMIN'));
        $is_private = $this->getPrivate();
        $is_my_own = $this->getUserId() == $user_id;
        $is_correct_institute = $this->isGlobal() || isset($this->institutes[Course::find($course_id)->institut_id]);
        return $is_dozent && $is_correct_institute && ($is_my_own || $is_admin || !$is_private || $this->isGlobal()) || $perm->have_perm('root', $user_id);
    }

    /**
     * is user with given user id allowed to edit or delete the courseset
     *
     * @param string $user_id
     * @return boolean
     */
    public function isUserAllowedToEdit($user_id)
    {
        global $perm;

        if ($this->getUserId() == '') {
            return false;
        }
        if ($perm->have_perm('root', $user_id) || $this->getUserId() == $user_id) {
            return true;
        }
        if (count($this->institutes) == 0 && count($this->courses) == 1 && $perm->have_studip_perm('tutor', current($this->getCourses()), $user_id)) {
            return true;
        }
        if ($perm->have_perm('admin', $user_id) || ($perm->have_perm('dozent', $user_id) && get_config('ALLOW_DOZENT_COURSESET_ADMIN'))) {
            foreach (array_keys($this->getInstituteIds()) as $one) {
                if ($perm->have_studip_perm('dozent', $one, $user_id)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * checks if given rule is allowed to be added to current set of rules
     *
     * @param AdmissionRule|string $admission_rule
     * @return boolean
     */
    public function isAdmissionRuleAllowed($admission_rule)
    {
        foreach ($this->getAdmissionRules() as $one) {
            if (!$one->isCombinationAllowed($admission_rule)) {
                return false;
            }
        }
        return true;
    }

    public static function getGlobalLockedAdmissionSetId()
    {
        $db = DBManager::get();
        $locked_set_id = $db->fetchColumn("SELECT cr.set_id FROM courseset_rule cr
                                    INNER JOIN coursesets USING(set_id)
                                    WHERE type='LockedAdmission' and private=1 and user_id='' LIMIT 1");
        if (!$locked_set_id) {
            $cs_insert = $db->prepare("INSERT INTO coursesets (set_id,user_id,name,infotext,algorithm,private,mkdate,chdate)
                                   VALUES (?,?,?,?,'',?,?,?)");
            $cs_r_insert = $db->prepare("INSERT INTO courseset_rule (set_id,rule_id,type,mkdate) VALUES (?,?,?,UNIX_TIMESTAMP())");
            $locked_insert = $db->prepare("INSERT INTO lockedadmissions (rule_id,message,mkdate,chdate) VALUES (?,'Die Anmeldung ist gesperrt',UNIX_TIMESTAMP(),UNIX_TIMESTAMP())");
            $locked_set_id = md5(uniqid('coursesets',1));
            $name = 'Anmeldung gesperrt (global)';
            $cs_insert->execute([$locked_set_id,'',$name,'',1,time(),time()]);
            $locked_rule_id = md5(uniqid('lockedadmissions',1));
            $locked_insert->execute([$locked_rule_id]);
            $cs_r_insert->execute([$locked_set_id,$locked_rule_id,'LockedAdmission']);
        }
        return $locked_set_id;
    }

    public static function addCourseToSet($set_id, $course_id)
    {
        $db = DBManager::get();
        $ok = $db->execute("INSERT IGNORE INTO seminar_courseset (set_id,seminar_id,mkdate) VALUES (?,?,UNIX_TIMESTAMP())", [$set_id, $course_id]);
        if ($ok) {
            StudipLog::log('SEM_CHANGED_ACCESS', $course_id,
                null, 'Zuordnung zu Anmeldeset', sprintf('Anmeldeset: %s', $set_id));
                Course::buildExisting(['seminar_id' => $course_id])->triggerChdate();
        }
        return $ok;
    }

    public static function removeCourseFromSet($set_id, $course_id)
    {
        $db = DBManager::get();
        $ok =  $db->execute("DELETE FROM seminar_courseset WHERE set_id=? AND seminar_id=? LIMIT 1", [$set_id, $course_id]);
        if ($ok) {
            StudipLog::log('SEM_CHANGED_ACCESS', $course_id,
                null, 'Entfernung von Anmeldeset', sprintf('Anmeldeset: %s', $set_id));
            //Delete priorities
            AdmissionPriority::unsetAllPrioritiesForCourse($course_id);
            Course::buildExisting(['seminar_id' => $course_id])->triggerChdate();
        }
        return $ok;
    }

    public function __clone()
    {
        $this->courses = [];
        $this->id = null;
        $this->user_id = null;
        $cloned_rules = [];
        foreach ($this->admissionRules as $key => $rule) {
            $dolly = clone $rule;
            $cloned_rules[$dolly->id] = $dolly;
        }
        $this->admissionRules = $cloned_rules;
    }

} /* end of class CourseSet */

