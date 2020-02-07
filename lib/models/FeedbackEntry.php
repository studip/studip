<?php

/**
 *
 * @author Nils Gehrke <nils.gehrke@uni-goettingen.de>
 *
 * @property integer id database column
 * @property integer feedback_id database column
 * @property string user_id database column
 * @property string comment database column
 * @property integer rating database column
 *
 */

class FeedbackEntry extends SimpleORMap
{
    public static function configure($config = [])
    {
        $config['db_table'] = 'feedback_entries';

        $config['belongs_to']['feedback'] = [
            'class_name'    => 'FeedbackElement',
            'foreign_key'   => 'feedback_id',
        ];
        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id'
        ];

        parent::configure($config);
    }
    public function isEditable()
    {
        $editable = false;
        if ($this->user_id == $GLOBALS['user']->id) {
            $editable = true;
        }
        return $editable;
    }
    public function isDeletable()
    {
        $deletable = false;

        $user_id = $GLOBALS['user']->id;

        if ($this->user_id == $user_id) {
            $deletable = true;
        } else {
            $course_id = $this->feedback->course_id;
            $perm_level = \CourseConfig::get($course_id)->FEEDBACK_ADMIN_PERM;
            if ($GLOBALS['perm']->have_studip_perm($perm_level, $course_id)) {
                $deletable = true;
            }
        }
        return $deletable;
    }
    public function delete()
    {
        if ($this->isDeletable()) {
            parent::delete();
        }
        return $this->is_deleted;
    }
}
