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

class VoteController extends AuthenticatedController {

    public function display_action($range_id) {

        // Bind some params
        URLHelper::bindLinkParam('show_expired', $null1);
        URLHelper::bindLinkParam('preview', $null2);
        URLHelper::bindLinkParam('revealNames', $null3);
        URLHelper::bindLinkParam('sort', $null4);

        // Bind range_id
        $this->range_id = $range_id;

        $this->nobody = !$GLOBALS['user']->id || $GLOBALS['user']->id == 'nobody';

        /*
         * Insert vote
         */
        if ($vote = Request::get('vote')) {
            $vote = new Vote($vote);
            if (!$this->nobody && $vote && $vote->isRunning() && (!$vote->userVoted() || $vote->changeable)) {
                try {
                    $vote->insertVote(Request::getArray('vote_answers'), $GLOBALS['user']->id);
                } catch (Exception $exc) {
                    $GLOBALS['vote_message'][$vote->id] = MessageBox::error($exc->getMessage());
                }
            }
        }

        // Check if we need administration icons
        $this->admin = $range_id == $GLOBALS['user']->id || $GLOBALS['perm']->have_studip_perm('tutor', $range_id);


        // Load evaluations
        if (!$this->nobody) {
            $eval_db = new EvaluationDB();
            $this->evaluations = StudipEvaluation::findMany($eval_db->getEvaluationIDs($range_id, EVAL_STATE_ACTIVE));
        } else {
            $this->evaluations = [];
        }
        $show_votes[] = 'active';
        // Check if we got expired
        if (Request::get('show_expired')) {
            $show_votes[] = 'stopvis';
            if ($this->admin) {
                $this->evaluations = array_merge($this->evaluations, StudipEvaluation::findMany($eval_db->getEvaluationIDs($range_id, EVAL_STATE_STOPPED)));
                $show_votes[] = 'stopinvis';
            }
        }

        $this->votes = Vote::findBySQL('range_id = ? AND state IN (?) ORDER BY mkdate desc', [$range_id,$show_votes]);
        $this->visit();

        }

    function visit()
    {
        if ($GLOBALS['user']->id && $GLOBALS['user']->id != 'nobody' && Request::option('contentbox_open') && in_array(Request::option('contentbox_type'), words('vote eval'))) {
            object_set_visit(Request::option('contentbox_open'), Request::option('contentbox_type'));
        }
    }

    function visit_action()
    {
        $this->visit();
        $this->render_nothing();
    }

    /**
     * Determines if a vote should show its result
     *
     * @param Vote $vote the vote to check
     * @return boolean true if result should be shown
     */
    public function showResult($vote) {
        if (Request::submitted('change') && $vote->changeable) {
            return false;
        }
        return $vote->userVoted() || in_array($vote->id, Request::getArray('preview'));
    }

}
