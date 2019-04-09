<?php

class Questionnaire extends SimpleORMap implements PrivacyObject
{

    public $answerable;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'questionnaires';
        $config['has_many']['questions'] = [
            'class_name' => 'QuestionnaireQuestion',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        $config['has_many']['assignments'] = [
            'class_name' => 'QuestionnaireAssignment',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        $config['has_many']['anonymousanswers'] = [
            'class_name' => 'QuestionnaireAnonymousAnswer',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        parent::configure($config);
    }

    public function countAnswers()
    {
        if ($this['anonymous']) {
            $statement = DBManager::get()->prepare("
                SELECT COUNT(*)
                FROM questionnaire_anonymous_answers
                WHERE questionnaire_id = :questionnaire_id
            ");
            $statement->execute([
                'questionnaire_id' => $this->getId()
            ]);
           return $statement->fetch(PDO::FETCH_COLUMN, 0);
        } else {
            $statement = DBManager::get()->prepare("
                SELECT COUNT(*)
                FROM questionnaire_answers
                    INNER JOIN questionnaire_questions ON (questionnaire_answers.question_id = questionnaire_questions.question_id)
                WHERE questionnaire_id = :questionnaire_id
            ");
            $statement->execute([
                'questionnaire_id' => $this->getId()
            ]);
            $answers_total = $statement->fetch(PDO::FETCH_COLUMN, 0);

            return count($this->questions) ? $answers_total / count($this->questions) : 1;
        }
    }

    public function isAnswered($user_id = null)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        if (!$user_id || ($user_id === "nobody")) {
            return false;
        }
        $statement = DBManager::get()->prepare("
            SELECT 1
            FROM questionnaire_answers
                INNER JOIN questionnaire_questions ON (questionnaire_answers.question_id = questionnaire_questions.question_id)
            WHERE user_id = :user_id
                AND questionnaire_id = :questionnaire_id
            UNION SELECT 1
            FROM questionnaire_anonymous_answers
            WHERE user_id = :user_id
                AND questionnaire_id = :questionnaire_id
        ");
        $statement->execute([
            'user_id' => $user_id,
            'questionnaire_id' => $this->getId()
        ]);
        return (bool) $statement->fetch(PDO::FETCH_COLUMN, 0);
    }

    public function latestAnswerTimestamp()
    {
        $statement = DBManager::get()->prepare("
            SELECT questionnaire_answers.chdate
            FROM questionnaire_answers
                INNER JOIN questionnaire_questions ON (questionnaire_answers.question_id = questionnaire_questions.question_id)
            WHERE questionnaire_questions.questionnaire_id = ?
            ORDER BY questionnaire_answers.chdate DESC
            LIMIT 1
        ");
        $statement->execute([$this->getId()]);
        return $statement->fetch(PDO::FETCH_COLUMN, 0);
    }

    public function isViewable()
    {
        if ($this->isEditable()) {
            return true;
        }
        foreach ($this->assignments as $assignment) {
            if ($assignment['range_id'] === "public") {
                return true;
            } elseif (in_array($assignment['range_type'], ["static", "user", "institute"]) && $GLOBALS['perm']->have_perm("user")) {
                return true;
            } elseif($GLOBALS['perm']->have_studip_perm("user", $assignment['range_id'])) {
                return true;
            }
        }
        return false;
    }

    public function isAnswerable()
    {
        if (!$this->isViewable() || !$this->isRunning()) {
            return false;
        }
        if ($this['anonymous'] && $this->isAnswered()) {
            return false;
        }
        if ($this->isEditable()) {
            return true;
        }
        $this->answerable = true;
        NotificationCenter::postNotification("QuestionnaireWillAllowToAnswer", $this);
        return $this->answerable;
    }

    public function isEditable()
    {
        if ($this->isNew() || ($this['user_id'] === $GLOBALS['user']->id) || $GLOBALS['perm']->have_perm("root")) {
            return true;
        } else {
            foreach ($this->assignments as $assignment) {
                if ($assignment['range_type'] === "institute" && $GLOBALS['perm']->have_studip_perm("tutor", $assignment['range_id'])) {
                    return true;
                } elseif($assignment['range_type'] === "course" && $GLOBALS['perm']->have_studip_perm("tutor", $assignment['range_id'])) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isCopyable()
    {
        return ($this->copyable && $GLOBALS['perm']->have_perm('autor') && $this->isViewable()) || $this->isEditable();
    }

    public function start()
    {
        if (!$this->isRunning()) {
            $this['startdate'] = time();
            $this['visible'] = 1;
            if ($this->isStopped()) {
                $this['stopdate'] = null;
            }
            $this->store();
            foreach ($this->questions as $question) {
                $question->onBeginning();
            }
        }
    }

    public function stop()
    {
        if (!$this->isStopped()) {
            $this['visible'] = $this['resultvisibility'] === 'never' ? 0 : 1;
            $this['stopdate'] = time();
            $this->store();
            foreach ($this->questions as $question) {
                $question->onEnding();
            }
        }
    }

    public function isStarted()
    {
        return $this['startdate'] && ($this['startdate'] <= time());
    }

    public function isStopped()
    {
        return $this['stopdate'] && ($this['stopdate'] <= time());
    }

    public function isRunning()
    {
        return $this->isStarted() && !$this->isStopped();
    }

    public function resultsVisible()
    {
        if (!$this->isViewable()) {
            return false;
        }
        return $this['resultvisibility'] === "always"
            || $this->isEditable()
            || ($this['resultvisibility'] === "afterending" && $this->isStopped());
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findBySQL("user_id = ?", [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Fragebögen'), 'questionnaires', $field_data);
            }
        }
    }
}
