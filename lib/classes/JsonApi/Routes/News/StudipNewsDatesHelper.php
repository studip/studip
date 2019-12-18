<?php

namespace JsonApi\Routes\News;

use JsonApi\Routes\TimestampTrait;

trait StudipNewsDatesHelper
{
    use TimestampTrait;

    protected function checkNewsDates($json)
    {
        if (!self::arrayHas($json, 'data.attributes.publication-start')) {
            return 'The attribute `publication-start` is required.';
        }
        if (!self::arrayHas($json, 'data.attributes.publication-end')) {
            return 'The attribute `publication-end` is required.';
        }

        $pubStart = self::arrayGet($json, 'data.attributes.publication-start');
        if (!self::isValidTimestamp($pubStart)) {
            return '`publication-start` is not an ISO 8601 timestamp.';
        }
        $pubEnd = self::arrayGet($json, 'data.attributes.publication-end');
        if (!self::isValidTimestamp($pubEnd)) {
            return '`publication-end` is not an ISO 8601 timestamp.';
        }

        $start = self::convertPublicationStartToDateTime($pubStart);
        $end = self::convertPublicationEndToDateTime($pubEnd);

        if (!($start < $end)) {
            return '`publication-start` must be before the `publication-end`.';
        }
    }

    public static function convertPublicationStartToDateTime($pubStartString)
    {
        return self::fromISO8601($pubStartString)->modify('midnight');
    }

    public static function convertPublicationEndToDateTime($pubEndString)
    {
        return self::fromISO8601($pubEndString)->modify('tomorrow');
    }

    public static function convertTimestampsToDateExpire($pubStartString, $pubEndString)
    {
        $start = self::convertPublicationStartToDateTime($pubStartString);
        $end = self::convertPublicationEndToDateTime($pubEndString);

        return [$start->getTimestamp(), $end->getTimestamp() - $start->getTimestamp()];
    }

    public static function getStartEndFromNews(\StudipNews $news)
    {
        $start = (new DateTime())->setTimestamp((int) $news->date);
        $end = (new DateTime())->setTimestamp((int) $news->date + (int) $news->expire);

        return [$start, $end];
    }
}
