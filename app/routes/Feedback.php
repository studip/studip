<?php

namespace RESTAPI\Routes;

/**
 * @author     Nils Gehrke <nils.gehrke@uni-goettingen.de>
 * @deprecated Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 *
 * @condition feedback_id ^\d*$
 * @condition course_id ^[a-f0-9]{32}$
 *
 */
class Feedback extends \RESTAPI\RouteMap
{
    /**
     * Create feedback element for a range
     *
     * @post /feedback/range/:range_id/:range_type
     *
     */
    public function createFeedbackElement($range_id, $range_type)
    {
        $course_id = $range_type::find($range_id)->getRangeCourseId();
        if (!\Feedback::hasRangeAccess($range_id, $range_type) || !\Feedback::hasCreatePerm($course_id)) {
            $this->error(403);
        }
        $feedback = \FeedbackElement::build([
            'range_id'          => $range_id,
            'range_type'        => $range_type,
            'user_id'           => $GLOBALS['user']->id,
            'course_id'         => $course_id,
            'question'          => $this->data['question'],
            'description'       => $this->data['description'],
            'results_visible'   => intval($this->data['results_visible']),
            'commentable'       => intval($this->data['commentable']),
            'mode'              => $this->data['mode']
        ]);
        $feedback->store();
        return $feedback->toArray();
    }

    /**
     * Get a feedback element
     *
     * @get /feedback/:feedback_id
     *
     */
    public function getFeedbackElement($feedback_id)
    {
        if (!$feedback = \FeedbackElement::find($feedback_id)) {
            $this->error(404);
        }
        if (!\Feedback::hasRangeAccess($feedback->range_id, $feedback->range_type)) {
            $this->error(403);
        }
        return $feedback->toArray();
    }


    /**
     * Get all entries of a feedback element
     *
     * @get /feedback/:feedback_id/entries
     *
     */
    public function getFeedbackEntries($feedback_id)
    {
        if (!$feedback = \FeedbackElement::find($feedback_id)) {
            $this->error(404);
        }
        if (!\Feedback::hasRangeAccess($feedback->range_id, $feedback->range_type)) {
            $this->error(403);
        }
        if ($feedback->results_visible == 1 && !$feedback->isFeedbackable()) {
            foreach($feedback->entries as $entry) {
                $result['entries'][] = $entry->toArray();
            }
        } elseif (!$feedback->isFeedbackable()) {
            $result['entries'][] = $feedback->getOwnEntry()->toArray();
        } else {
            $result = [];
        }

        return $result;
    }

    /**
     * Edit a feedback element
     *
     * @put /feedback/:feedback_id
     *
     */
    public function editFeedbackElement($feedback_id)
    {
        if (!$feedback = \FeedbackElement::find($feedback_id)) {
            $this->error(404);
        }
        $course_id = $feedback->course_id;
        if (!\Feedback::hasRangeAccess($feedback->range_id, $feedback->range_type) || !\Feedback::hasAdminPerm($course_id)) {
            $this->error(403);
        }
        $feedback->question = $this->data['question'] !== null ? $this->data['question'] : $feedback->question;
        $feedback->description = $this->data['description'] !== null ? $this->data['description'] : $feedback->description;
        $feedback->results_visible = $this->data['results_visible'] !== null ?
            intval($this->data['results_visible']) : $feedback->results_visible;
        $feedback->store();
        return $feedback->toArray();
    }

    /**
     * Delete a feedback element
     *
     * @delete /feedback/:feedback_id
     *
     */
    public function deleteFeedbackElement($feedback_id)
    {
        if (!$feedback = \FeedbackElement::find($feedback_id)) {
            $this->error(404);
        }
        $course_id = $feedback->course_id;
        if (!\Feedback::hasRangeAccess($feedback->range_id, $feedback->range_type) || !\Feedback::hasAdminPerm($course_id)) {
            $this->error(403);
        }
        $feedback->delete();
        $this->halt(200);
    }

    /**
     * List all feedback elements for a range
     *
     * @get /feedback/range/:range_id/:range_type
     *
     * @param string $range_id
     * @param string $range_type
     */
    public function getFeedbackElementsForRange($range_id, $range_type)
    {
        if (!\Feedback::hasRangeAccess($range_id, $range_type)) {
            $this->error(403, 'You may not access the given range object.');
        }
        $feedback_elements = \FeedbackElement::findBySQL('range_id = ? AND range_type = ?  ORDER BY mkdate DESC', [$range_id, $range_type]);
        foreach($feedback_elements as $feedback) {
            $result['feedback_elements'][] = $feedback->toArray();
        }
        return $result;
    }

    /**
     * List all feedback elements of a course
     *
     * @get /course/:course_id/feedback
     *
     */
    public function getFeedbackElementsForCourse($course_id)
    {
        if (!\Feedback::hasAdminPerm($course_id)) {
            $this->error(403, 'You may not list all feedback elements of the course. Only feedback admins can.');
        }
        $feedback_elements  = \FeedbackElement::findBySQL('course_id = ? ORDER BY mkdate DESC', [$course_id]);
        foreach($feedback_elements as $feedback) {
            $result['feedback_elements'][] = $feedback->toArray();
        }
        return $result;
    }

    /**
     * add an entry for a feedback element
     *
     * @post /feedback/:feedback_id/entry
     *
     */
    public function addFeedbackEntry($feedback_id)
    {
        if (!$feedback = \FeedbackElement::find($feedback_id)) {
            $this->error(404);
        }
        if (!$feedback->isFeedbackable()) {
            $this->error(403, 'You may not add an entry here. Maybe you have already given feedback or you are the author of the feedback element.');
        }
        $entry = \FeedbackEntry::build([
            'feedback_id'   => $feedback->id,
            'user_id'       => $GLOBALS['user']->id
        ]);

        if($feedback->commentable === 1) {
            $entry->comment = $this->data['comment'];
        }

        if($feedback->mode !== 0) {
            $rating = intval($this->data['rating']);
            if ($rating === 0) {
                $rating = 1;
            }
            if ($feedback->mode === 1) {
                $rating = ($rating > 5 ? 5 : $rating);
            }
            if ($feedback->mode === 2) {
                $rating = ($rating > 10 ? 10 : $rating);
            }
            $entry->rating = $rating;
        }

        $entry->store();
        return $entry->toArray();
    }

    /**
     * edit an entry of a feedback element
     *
     * @put /feedback/entry/:entry_id
     *
     */
    public function editFeedbackEntry($entry_id)
    {
        if (!$entry = \FeedbackEntry::find($entry_id)) {
            $this->error(404);
        }
        if (!$entry->isEditable()) {
            $this->error(403);
        }
        if($feedback->mode !== 0) {
            $rating = intval($this->data['rating']);
            if ($rating === 0) {
                $rating = 1;
            }
            if ($feedback->mode === 1) {
                $rating = ($rating > 5 ? 5 : $rating);
            }
            if ($feedback->mode === 2) {
                $rating = ($rating > 10 ? 10 : $rating);
            }
            $entry->rating = $rating;
        }
        if($feedback->commentable === 1) {
            $entry->comment = $this->data['comment'] !== null ? $this->data['comment'] : $entry->comment;
        }
        $entry->store();
        return $entry->toArray();
    }

    /**
     * delete an entry of a feedback element
     *
     * @delete /feedback/entry/:entry_id
     *
     */
    public function deleteFeedbackEntry($entry_id)
    {
        if (!$entry = \FeedbackEntry::find($entry_id)) {
            $this->error(404);
        }
        if ($entry->delete()){
            $this->halt(200);
        }
    }
}
