<?php

namespace JsonApi\Routes\News;

use JsonApi\Errors\InternalServerError;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;

abstract class AbstractNewsCreate extends JsonApiController
{
    use StudipNewsDatesHelper, ValidationTrait;

    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data.attributes.title')) {
            return 'News must have a `title`.';
        }

        if (!self::arrayHas($json, 'data.attributes.content')) {
            return 'News must have `content`.';
        }

        if (!self::arrayHas($json, 'data.attributes.comments-allowed')) {
            return 'You should allow or not allow comments in your news.';
        }

        if ($error = $this->checkNewsDates($json)) {
            return $error;
        }
    }

    protected function createNewsFromJSON(\User $user, $range, array $json)
    {
        if (!(
                $range instanceof \Course ||
                $range instanceof \User ||
                'studip' === $range
            )) {
            throw new InternalServerError('`$range` has wrong type.');
        }

        $getField = function ($key, $default = null) use ($json) {
            return self::arrayGet($json, 'data.attributes.'.$key, $default);
        };

        $title = $getField('title');
        $content = $getField('content');
        if (method_exists(\Studip\Markup::class, 'purifyHtml')) {
            $content = \Studip\Markup::purifyHtml($content);
        }
        $commentsAllowed = (bool) $getField('comments-allowed');
        list($date, $expire) = self::convertTimestampsToDateExpire($getField('publication-start'), $getField('publication-end'));

        $news = \StudipNews::create(
            [
                'user_id' => $user->id,
                'topic' => $title,
                'body' => $content,
                'allow_comments' => $commentsAllowed,
                'date' => $date,
                'expire' => $expire,
            ]
        );

        if ($news) {
            $news->addRange(
                is_callable([$range, 'getId'])
                ? $range->getId()
                : $range
            );
            $news->store();
        }

        return $news;
    }
}
