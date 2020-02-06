<?php

/**
 *
 * @author Nils Gehrke <nils.gehrke@uni-goettingen.de>
 *
 */

class FeedbackElements extends SimpleORMap
{
    public static function configure($config = [])
    {
        $config['db_table'] = 'feedback';
        $config['has_many']['entries'] = [
            'class_name'        => 'FeedbackEntries',
            'assoc_foreign_key' => 'feedback_id',
            'order_by'          => 'ORDER BY mkdate DESC',
            'on_delete'         => 'delete'
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'course_id',
        ];
        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id'
        ];

        parent::configure($config);
    }

    /**
     *
     * @param string $user_id    optional; use this ID instead of $GLOBALS['user']->id
     *
     * @return bool
     */
    public function isFeedbackable(string $user_id = null): bool
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        $feedbackable = false;
        if (Feedback::hasRangeAccess($this->range_id, $this->range_type, $user_id) && !$this->isOwner($user_id)) {
            $already_feedbacked = $this->getOwnEntry($user_id);
            if ($already_feedbacked === null) {
                $feedbackable = true;
            }
        }

        return $feedbackable;
    }

    /**
     *
     * @param string $user_id    optional; use this ID instead of $GLOBALS['user']->id
     *
     * @return bool
     */
    public function isOwner(string $user_id = null): bool
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;
        $ownership = false;
        if ($this->user_id == $user_id) {
            $ownership = true;
        }
        return $ownership;
    }

    /**
     *
     * @param string $user_id    optional; use this ID instead of $GLOBALS['user']->id
     *
     * @return FeedbackEntries|null
     */
    public function getOwnEntry(string $user_id = null)
    {
        $user_id = $user_id ?? $GLOBALS['user']->id;

        return FeedbackEntries::findOneBySQL("feedback_id = ? AND user_id = ?", [$this->id, $user_id]);
    }

    public function getRatings()
    {
        $ratings = $this->entries->pluck('rating');
        return $ratings;
    }

    public function getCountOfRating($rating)
    {
        $ratings = $this->entries->filter(function ($entry) use ($rating) {
            return $entry->rating == $rating;
        })->toArray();

        return count($ratings);
    }

    public function getPercentageOfRating($rating)
    {
        $ratings    = $this->getCountOfRating($rating);
        $total      = count($this->entries);
        $percentage = ($ratings * 100) / $total;

        return round($percentage);
    }

    public function getPercentageOfMeanRating($total)
    {
        $rating    = round($this->getMeanOfRating(), 2);
        $percentage = ($rating * 100) / $total;

        return $percentage;
    }

    public function getMeanOfRating()
    {
        $ratings = $this->getRatings();
        $mean    = array_sum($ratings) / count($ratings);

        return number_format($mean, 2, _(','), ' ');
    }

    public function getMaxRating()
    {
        switch ($this->mode) {
            case 1:
                // 5 Stars Rating
                return 5;
                break;
            case 2:
                // 10 Stars Rating
                return 10;
                break;
            default:
                return 0;
        }
    }

    public function getRange()
    {
        return $this->range_type::find($this->range_id);
    }
}
