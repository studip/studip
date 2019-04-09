<?php

# Lifter010: TODO
/**
 * vote.php - Votecontroller controller
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class EvaluationController extends AuthenticatedController
{
    public function display_action($range_id)
    {
        // Bind some params
        URLHelper::bindLinkParam('show_expired', $null1);

        // Bind range_id
        $this->range_id = $range_id;

        $this->nobody = !$GLOBALS['user']->id || $GLOBALS['user']->id == 'nobody';

        // Check if we ned administration icons
        $this->admin = $range_id == $GLOBALS['user']->id || $GLOBALS['perm']->have_studip_perm('tutor', $range_id);

        // Load evaluations
        if (!$this->nobody) {
            $eval_db = new EvaluationDB();
            $this->evaluations = StudipEvaluation::findMany($eval_db->getEvaluationIDs($range_id, EVAL_STATE_ACTIVE));
        } else {
            $this->evaluations = [];
        }
        // Check if we got expired
        if (Request::get('show_expired')) {
            if ($this->admin) {
                $this->evaluations = array_merge($this->evaluations, StudipEvaluation::findMany($eval_db->getEvaluationIDs($range_id, EVAL_STATE_STOPPED)));
            }
        }

        // Special case: from widget and no data -> no output
        if ($this->suppress_empty_output && count($this->evaluations) === 0) {
            $this->render_nothing();
        } else {
            $this->visit();
        }
    }

    public function visit()
    {
        if ($GLOBALS['user']->id && $GLOBALS['user']->id != 'nobody' && Request::option('contentbox_open') && in_array(Request::option('contentbox_type'), words('vote eval'))) {
            object_set_visit(Request::option('contentbox_open'), Request::option('contentbox_type'));
        }
    }

    public function visit_action()
    {
        $this->visit();
        $this->render_nothing();
    }

}
