<?php

/**
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @author      André Klaßen <klassen@elan-ev.de>
 * @license     GPL 2 or later
 */

namespace Studip\Activity;

class NewsProvider implements ActivityProvider
{
    private function getUrlForContext($news, $activity)
    {
        switch ($activity->context) {
            case 'course':
                return [
                    \URLHelper::getUrl('dispatch.php/course/overview/?cid=' . $activity->context_id . '&contentbox_type=news&contentbox_open=' . $activity->object_id) => _('Ankündigungen in der Veranstaltung')
                ];
            break;

            case 'institute':
                return [
                    \URLHelper::getUrl('dispatch.php/institute/overview?auswahl=' . $activity->context_id) => _('Ankündigungen in der Einrichtung')
                ];
            break;

            case 'system':
                return [
                    \URLHelper::getUrl('dispatch.php/start?contentbox_type=news&contentbox_open='. $news->getId() .'#'. $news->getId()) => _('Ankündigungen auf der Startseite')
                ];
            break;

            case 'user':
                return [
                    \URLHelper::getUrl('dispatch.php/profile/?username='. get_username($activity->context_id)
                        . '&contentbox_type=news&contentbox_open='. $news->getId() .'#'. $news->getId()) => _('Ankündigungen auf der Profilseite')
                ];
            break;
        }
    }

    /**
     * posts an activity for a given notification event
     *
     * @param String $event a notification for an activity
     * @param String  $news
     */
    public function postActivity($event, $news)
    {
        // delete any old activities for this id
        $activities = Activity::findBySql('object_id = ?', [$news->id]);

        foreach ($activities as $activity) {
            $activity->delete();
        }

        $mkdate = time();

        // iterate over every news-range and create approbriate activity
        foreach ($news->news_ranges as $range) {
            $context_id = $range->range_id;

            switch ($range->type) {
                case 'user':
                    $context = 'user';
                    break;
                case 'inst':
                case 'fak':
                    $context = 'institute';
                    break;
                case 'sem':
                    $context = 'course';
                    break;
                case 'global':
                    $context = 'system';
                    $context_id = 'system';
                    break;
            }
            if (isset($context)) {
                $activity = Activity::create(
                    [
                        'provider'    => __CLASS__,
                        'context'     => $context,
                        'context_id'  => $context_id,
                        'content'     => null,
                        'actor_type'  => 'user',         // who initiated the activity?
                        'actor_id'    => $news->user_id, // id of initiator
                        'verb'        => 'created',      // the activity type
                        'object_id'   => $news->id,      // the id of the referenced object
                        'object_type' => 'news',         // type of activity object
                        'mkdate'      => $mkdate
                    ]
                );
            }

        }
    }


    /**
     * get the details for the passed activity
     *
     * @param object $activity the activity to fill with details, passed by reference
     */
    public function getActivityDetails($activity)
    {
        $news = new \StudipNews($activity->object_id);

        // do not show unpublished news
        if ($news->date > time()) {
            return false;
        }

        $activity->content = '<b>' . htmlReady($news->topic)
            .'</b><br>'. formatReady($news->body);

        $url = self::getUrlForContext($news, $activity);
        $route = \URLHelper::getURL('api.php/news/' . $news->id, NULL, true);

        $activity->object_url = $url;
        $activity->object_route = $route;

        return true;
    }
    /**
     * {@inheritdoc}
     */
    public static function getLexicalField()
    {
        return _('eine Ankündigung');
    }

}
